<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class WordSearchGenerator
{
    private const ALPHABET = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'Ñ', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

    private const VECTORS = [
        'horizontal' => [0, 1],
        'vertical' => [1, 0],
        'diagonal_down' => [1, 1],
        'diagonal_up' => [-1, 1],
    ];

    public function normalize(string $word): string
    {
        $normalized = Str::upper(Str::squish($word));
        $normalized = strtr($normalized, [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U',
        ]);

        return preg_replace('/[^A-ZÑ]/u', '', $normalized) ?? '';
    }

    public function generate(array $words, int $rows, int $columns, array $directions, bool $allowReverse): array
    {
        $maximumLength = collect($directions)->map(fn (string $direction) => match ($direction) {
            'horizontal' => $columns,
            'vertical' => $rows,
            default => min($rows, $columns),
        })->max();

        $normalizedWords = collect($words)
            ->map(function (string $word) use ($maximumLength): array {
                $original = Str::squish($word);
                $normalized = $this->normalize($original);

                if ($normalized === '') {
                    throw ValidationException::withMessages(['words' => 'Las palabras deben contener letras válidas.']);
                }

                if (Str::length($normalized) > 20) {
                    throw ValidationException::withMessages(['words' => "La palabra «{$original}» supera 20 caracteres normalizados."]);
                }

                if (Str::length($normalized) > $maximumLength) {
                    throw ValidationException::withMessages(['words' => "La palabra «{$original}» no cabe en la cuadrícula seleccionada."]);
                }

                return [
                    'id' => (string) Str::uuid(),
                    'original' => $original,
                    'normalized' => $normalized,
                ];
            })
            ->values();

        if ($normalizedWords->pluck('normalized')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['words' => 'No se permiten palabras repetidas después de normalizarlas.']);
        }

        for ($generation = 0; $generation < 12; $generation++) {
            $result = $this->tryGenerate($normalizedWords->all(), $rows, $columns, $directions, $allowReverse);

            if ($result !== null) {
                return [
                    'words' => $normalizedWords->all(),
                    'rows' => $rows,
                    'columns' => $columns,
                    'directions' => array_values($directions),
                    'allow_reverse' => $allowReverse,
                    ...$result,
                ];
            }
        }

        throw ValidationException::withMessages([
            'words' => 'No fue posible colocar todas las palabras. Aumenta la cuadrícula o reduce las palabras.',
        ]);
    }

    private function tryGenerate(array $words, int $rows, int $columns, array $directions, bool $allowReverse): ?array
    {
        usort($words, fn (array $first, array $second) => Str::length($second['normalized']) <=> Str::length($first['normalized']));
        $grid = array_fill(0, $rows, array_fill(0, $columns, null));
        $placements = [];

        foreach ($words as $word) {
            $placement = $this->placeWord($grid, $word, $rows, $columns, $directions, $allowReverse);

            if ($placement === null) {
                return null;
            }

            $grid = $placement['grid'];
            $placements[] = $placement['placement'];
        }

        for ($row = 0; $row < $rows; $row++) {
            for ($column = 0; $column < $columns; $column++) {
                $grid[$row][$column] ??= self::ALPHABET[random_int(0, count(self::ALPHABET) - 1)];
            }
        }

        return ['grid' => $grid, 'placements' => $placements];
    }

    private function placeWord(array $grid, array $word, int $rows, int $columns, array $directions, bool $allowReverse): ?array
    {
        $letters = mb_str_split($word['normalized']);

        for ($attempt = 0; $attempt < 250; $attempt++) {
            $direction = $directions[random_int(0, count($directions) - 1)];
            [$rowStep, $columnStep] = self::VECTORS[$direction];
            $reversed = $allowReverse && random_int(0, 1) === 1;

            if ($reversed) {
                $rowStep *= -1;
                $columnStep *= -1;
            }

            $startRow = random_int(0, $rows - 1);
            $startColumn = random_int(0, $columns - 1);
            $coordinates = [];
            $valid = true;

            foreach ($letters as $index => $letter) {
                $row = $startRow + ($rowStep * $index);
                $column = $startColumn + ($columnStep * $index);

                if ($row < 0 || $row >= $rows || $column < 0 || $column >= $columns || ($grid[$row][$column] !== null && $grid[$row][$column] !== $letter)) {
                    $valid = false;
                    break;
                }

                $coordinates[] = ['row' => $row, 'column' => $column];
            }

            if (! $valid) {
                continue;
            }

            foreach ($coordinates as $index => $coordinate) {
                $grid[$coordinate['row']][$coordinate['column']] = $letters[$index];
            }

            $end = $coordinates[array_key_last($coordinates)];

            return [
                'grid' => $grid,
                'placement' => [
                    'word_id' => $word['id'],
                    'start_row' => $startRow,
                    'start_column' => $startColumn,
                    'end_row' => $end['row'],
                    'end_column' => $end['column'],
                    'direction' => $direction,
                    'reversed' => $reversed,
                    'coordinates' => $coordinates,
                ],
            ];
        }

        return null;
    }
}
