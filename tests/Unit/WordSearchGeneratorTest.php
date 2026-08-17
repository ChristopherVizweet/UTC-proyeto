<?php

use App\Services\WordSearchGenerator;
use Illuminate\Validation\ValidationException;

it('normaliza acentos espacios signos conserva la ñ y acepta números', function () {
    $generator = app(WordSearchGenerator::class);

    expect($generator->normalize('  Oxígeno-mañana  123-45 '))->toBe('OXIGENOMAÑANA12345');
});

it('genera una cuadrícula completa con ubicaciones válidas', function () {
    $result = app(WordSearchGenerator::class)->generate(
        ['Oxígeno', 'Carbono', 'Célula'],
        12,
        12,
        ['horizontal', 'vertical', 'diagonal_down', 'diagonal_up'],
        true
    );

    expect($result['grid'])->toHaveCount(12)
        ->and($result['grid'][0])->toHaveCount(12)
        ->and($result['placements'])->toHaveCount(3)
        ->and(collect($result['grid'])->flatten()->every(fn ($letter) => preg_match('/^[A-ZÑ]$/u', $letter) === 1))->toBeTrue();
});

it('genera una sopa de números cuando todos los elementos son numéricos', function () {
    $result = app(WordSearchGenerator::class)->generate(
        ['123', '4567', '890'],
        8,
        8,
        ['horizontal', 'vertical', 'diagonal_down', 'diagonal_up'],
        true
    );

    expect($result['words'])->sequence(
        fn ($word) => $word->normalized->toBe('123'),
        fn ($word) => $word->normalized->toBe('4567'),
        fn ($word) => $word->normalized->toBe('890'),
    )
        ->and(collect($result['grid'])->flatten()->every(fn ($letter) => preg_match('/^[0-9]$/', $letter) === 1))->toBeTrue();
});

it('rechaza palabras duplicadas después de normalizarlas', function () {
    app(WordSearchGenerator::class)->generate(
        ['Oxígeno', 'oxigeno', 'Carbono'],
        12,
        12,
        ['horizontal'],
        false
    );
})->throws(ValidationException::class);
