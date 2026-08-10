<?php

namespace Database\Factories;

use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teaching_assignment_id' => TeachingAssignment::factory(),
            'created_by' => User::factory(),
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'instrucciones' => fake()->paragraph(),
            'tipo' => 'tarea',
            'fecha_publicacion' => now(),
            'fecha_limite' => now()->addWeek(),
            'puntaje_maximo' => 10,
            'permite_entrega_tardia' => false,
            'estado' => 'publicada',
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => [
            'estado' => 'borrador',
            'fecha_publicacion' => null,
        ]);
    }
}
