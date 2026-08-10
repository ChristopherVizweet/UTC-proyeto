<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'student_id' => User::factory(),
            'respuesta' => fake()->paragraph(),
            'fecha_entrega' => now(),
            'estado' => 'entregada',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'fecha_entrega' => null,
            'estado' => 'borrador',
        ]);
    }
}
