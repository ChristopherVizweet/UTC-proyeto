<?php

namespace Database\Factories;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ActivityContentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'configuracion' => [
                'pairs' => [
                    ['id' => (string) Str::uuid(), 'left' => 'México', 'right' => 'Ciudad de México'],
                    ['id' => (string) Str::uuid(), 'left' => 'Francia', 'right' => 'París'],
                ],
                'shuffle_right_column' => true,
                'max_attempts' => 2,
                'show_result_immediately' => true,
            ],
            'contenido_version' => 1,
        ];
    }
}
