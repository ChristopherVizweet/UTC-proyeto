<?php

namespace Database\Factories;

use App\Models\SchoolGroup;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeachingAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => User::factory(),
            'school_group_id' => SchoolGroup::factory(),
            'subject_id' => Subject::factory(),
            'is_active' => true,
        ];
    }
}
