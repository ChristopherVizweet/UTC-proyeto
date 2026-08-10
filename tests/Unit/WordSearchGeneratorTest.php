<?php

use App\Services\WordSearchGenerator;
use Illuminate\Validation\ValidationException;

it('normaliza acentos espacios signos y conserva la ñ', function () {
    $generator = app(WordSearchGenerator::class);

    expect($generator->normalize('  Oxígeno-mañana  '))->toBe('OXIGENOMAÑANA');
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

it('rechaza palabras duplicadas después de normalizarlas', function () {
    app(WordSearchGenerator::class)->generate(
        ['Oxígeno', 'oxigeno', 'Carbono'],
        12,
        12,
        ['horizontal'],
        false
    );
})->throws(ValidationException::class);
