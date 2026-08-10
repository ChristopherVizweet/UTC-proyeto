<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolGradeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_grado' => fake()->unique()->numerify('Grado ##'),
            'nivel_grado' => fake()->numberBetween(1, 12),
            'descripcion_grado' => fake()->sentence(),
        ];
    }
}
