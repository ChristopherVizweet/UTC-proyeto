<?php

namespace Database\Factories;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityAttemptFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'student_id' => User::factory(),
            'attempt_number' => 1,
            'answers' => null,
            'score' => null,
            'max_score' => 10,
            'started_at' => now(),
            'completed_at' => null,
            'time_seconds' => null,
            'status' => 'iniciado',
        ];
    }
}
