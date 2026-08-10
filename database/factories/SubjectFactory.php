<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_materia' => fake()->unique()->words(2, true),
            'code' => fake()->unique()->bothify('MAT-###'),
            'descripcion_materia' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
