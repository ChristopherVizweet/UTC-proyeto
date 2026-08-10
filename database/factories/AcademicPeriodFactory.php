<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicPeriodFactory extends Factory
{
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 year', 'now');

        return [
            'nombre_periodo' => fake()->unique()->numerify('Periodo ####'),
            'fecha_inicio' => $start,
            'fecha_fin' => (clone $start)->modify('+10 months'),
            'estado_periodo' => true,
        ];
    }
}
