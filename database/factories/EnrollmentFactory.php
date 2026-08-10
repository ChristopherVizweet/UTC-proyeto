<?php

namespace Database\Factories;

use App\Models\SchoolGroup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory(),
            'school_group_id' => SchoolGroup::factory(),
            'fecha_inscripcion' => now()->toDateString(),
            'estado_inscripcion' => 'activo',
        ];
    }
}
