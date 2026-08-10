<?php

namespace App\Models;

use Database\Factories\ActivityAttemptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityAttempt extends Model
{
    /** @use HasFactory<ActivityAttemptFactory> */
    use HasFactory;

    public const STATUSES = ['iniciado', 'completado', 'abandonado'];

    protected $fillable = [
        'activity_id',
        'student_id',
        'attempt_number',
        'answers',
        'score',
        'max_score',
        'started_at',
        'completed_at',
        'time_seconds',
        'status',
    ];

    protected $hidden = ['answers'];

    protected $attributes = [
        'status' => 'iniciado',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'score' => 'decimal:2',
            'max_score' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'time_seconds' => 'integer',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
