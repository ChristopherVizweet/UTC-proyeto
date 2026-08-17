<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CrosswordGenerator
{
    private const SIZE = 31;

    public function generate(array $items): array
    {
        $entries = collect($items)->map(function (array $item): array {
            $answer = Str::upper(Str::squish($item['answer']));
            $answer = strtr($answer, ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U']);
            $answer = preg_replace('/[^A-ZÑ0-9]/u', '', $answer) ?? '';

            if (Str::length($answer) < 2 || Str::length($answer) > 20) {
                throw ValidationException::withMessages(['crossword_items' => 'Cada respuesta debe contener entre 2 y 20 letras o números.']);
            }

            return [
                'id' => (string) Str::uuid(),
                'answer' => $answer,
                'original' => Str::squish($item['answer']),
                'clue' => Str::squish($item['clue']),
            ];
        })->sortByDesc(fn (array $entry): int => Str::length($entry['answer']))->values();

        if ($entries->pluck('answer')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['crossword_items' => 'No se permiten respuestas repetidas.']);
        }

        $grid = [];
        $placed = [];
        $first = $entries->shift();
        $startColumn = intdiv(self::SIZE - Str::length($first['answer']), 2);
        $this->write($grid, $first['answer'], intdiv(self::SIZE, 2), $startColumn, 'horizontal');
        $placed[] = [...$first, 'row' => intdiv(self::SIZE, 2), 'column' => $startColumn, 'direction' => 'horizontal'];

        foreach ($entries as $entry) {
            $placement = $this->findPlacement($grid, $entry['answer']);

            if ($placement === null) {
                throw ValidationException::withMessages([
                    'crossword_items' => "No fue posible conectar «{$entry['original']}» con las demás respuestas. Usa palabras que compartan letras.",
                ]);
            }

            $this->write($grid, $entry['answer'], $placement['row'], $placement['column'], $placement['direction']);
            $placed[] = [...$entry, ...$placement];
        }

        return $this->trim($grid, $placed);
    }

    private function findPlacement(array $grid, string $answer): ?array
    {
        $candidates = [];

        foreach (mb_str_split($answer) as $answerIndex => $letter) {
            foreach ($grid as $key => $cell) {
                if ($cell !== $letter) {
                    continue;
                }

                [$row, $column] = array_map('intval', explode(':', $key));

                foreach (['horizontal', 'vertical'] as $direction) {
                    $startRow = $direction === 'vertical' ? $row - $answerIndex : $row;
                    $startColumn = $direction === 'horizontal' ? $column - $answerIndex : $column;

                    if ($this->canPlace($grid, $answer, $startRow, $startColumn, $direction)) {
                        $candidates[] = ['row' => $startRow, 'column' => $startColumn, 'direction' => $direction];
                    }
                }
            }
        }

        if ($candidates === []) {
            return null;
        }

        return $candidates[array_rand($candidates)];
    }

    private function canPlace(array $grid, string $answer, int $row, int $column, string $direction): bool
    {
        $letters = mb_str_split($answer);
        $rowStep = $direction === 'vertical' ? 1 : 0;
        $columnStep = $direction === 'horizontal' ? 1 : 0;
        $crossings = 0;

        if ($row < 1 || $column < 1 || $row + ($rowStep * count($letters)) >= self::SIZE - 1 || $column + ($columnStep * count($letters)) >= self::SIZE - 1) {
            return false;
        }

        $before = ($row - $rowStep).':'.($column - $columnStep);
        $after = ($row + ($rowStep * count($letters))).':'.($column + ($columnStep * count($letters)));

        if (isset($grid[$before]) || isset($grid[$after])) {
            return false;
        }

        foreach ($letters as $index => $letter) {
            $cellRow = $row + ($rowStep * $index);
            $cellColumn = $column + ($columnStep * $index);
            $key = $cellRow.':'.$cellColumn;

            if (isset($grid[$key])) {
                if ($grid[$key] !== $letter) {
                    return false;
                }

                $crossings++;

                continue;
            }

            $neighbors = $direction === 'horizontal'
                ? [($cellRow - 1).':'.$cellColumn, ($cellRow + 1).':'.$cellColumn]
                : [$cellRow.':'.($cellColumn - 1), $cellRow.':'.($cellColumn + 1)];

            if (collect($neighbors)->contains(fn (string $neighbor): bool => isset($grid[$neighbor]))) {
                return false;
            }
        }

        return $crossings > 0;
    }

    private function write(array &$grid, string $answer, int $row, int $column, string $direction): void
    {
        foreach (mb_str_split($answer) as $index => $letter) {
            $cellRow = $row + ($direction === 'vertical' ? $index : 0);
            $cellColumn = $column + ($direction === 'horizontal' ? $index : 0);
            $grid[$cellRow.':'.$cellColumn] = $letter;
        }
    }

    private function trim(array $grid, array $entries): array
    {
        $coordinates = collect(array_keys($grid))->map(fn (string $key): array => array_map('intval', explode(':', $key)));
        $minimumRow = $coordinates->min(fn (array $coordinate): int => $coordinate[0]);
        $minimumColumn = $coordinates->min(fn (array $coordinate): int => $coordinate[1]);
        $rows = $coordinates->max(fn (array $coordinate): int => $coordinate[0]) - $minimumRow + 1;
        $columns = $coordinates->max(fn (array $coordinate): int => $coordinate[1]) - $minimumColumn + 1;
        $trimmedGrid = [];

        foreach ($grid as $key => $letter) {
            [$row, $column] = array_map('intval', explode(':', $key));
            $trimmedGrid[($row - $minimumRow).':'.($column - $minimumColumn)] = $letter;
        }

        $orderedEntries = collect($entries)
            ->map(fn (array $entry): array => [
                ...$entry,
                'row' => $entry['row'] - $minimumRow,
                'column' => $entry['column'] - $minimumColumn,
            ])
            ->sortBy(fn (array $entry): string => sprintf('%03d:%03d', $entry['row'], $entry['column']))
            ->values();
        $numberByStart = [];
        $nextNumber = 1;
        $entries = $orderedEntries->map(function (array $entry) use (&$numberByStart, &$nextNumber): array {
            $start = $entry['row'].':'.$entry['column'];
            $numberByStart[$start] ??= $nextNumber++;

            return [...$entry, 'number' => $numberByStart[$start]];
        })->all();

        return ['grid' => $trimmedGrid, 'entries' => $entries, 'rows' => $rows, 'columns' => $columns];
    }
}
