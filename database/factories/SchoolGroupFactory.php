<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\SchoolGrade;
use Illuminate\Database\Eloquent\Factories\Factory;

class SchoolGroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_grade_id' => SchoolGrade::factory(),
            'academic_period_id' => AcademicPeriod::factory(),
            'nombre_grupo' => fake()->randomElement(['A', 'B', 'C']),
            'capacidad_grupo' => 30,
            'descripcion_grupo' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
