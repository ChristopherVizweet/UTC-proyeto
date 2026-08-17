<?php

use App\Services\CrosswordGenerator;
use Illuminate\Validation\ValidationException;

it('genera un crucigrama conectado y numerado', function () {
    $result = app(CrosswordGenerator::class)->generate([
        ['answer' => 'pequeño', 'clue' => 'Antónimo de grande'],
        ['answer' => 'alegre', 'clue' => 'Antónimo de triste'],
        ['answer' => 'generoso', 'clue' => 'Antónimo de tacaño'],
        ['answer' => 'regalar', 'clue' => 'Sinónimo de obsequiar'],
    ]);

    expect($result['entries'])->toHaveCount(4)
        ->and($result['grid'])->not->toBeEmpty()
        ->and(collect($result['entries'])->pluck('number')->every(fn (int $number): bool => $number >= 1))->toBeTrue();
});

it('rechaza respuestas que no pueden conectarse', function () {
    app(CrosswordGenerator::class)->generate([
        ['answer' => 'abc', 'clue' => 'Primera'],
        ['answer' => 'xyz', 'clue' => 'Segunda'],
        ['answer' => 'qwerty', 'clue' => 'Tercera'],
    ]);
})->throws(ValidationException::class);
