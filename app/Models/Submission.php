<?php

namespace App\Models;

use Database\Factories\SubmissionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    /** @use HasFactory<SubmissionFactory> */
    use HasFactory;

    public const STATUSES = ['borrador', 'entregada', 'entregada_tarde', 'revisada'];

    protected $fillable = [
        'activity_id',
        'student_id',
        'respuesta',
        'archivo_path',
        'fecha_entrega',
        'estado',
        'calificacion',
        'retroalimentacion',
        'graded_by',
        'graded_at',
    ];

    protected $attributes = [
        'estado' => 'borrador',
    ];

    protected function casts(): array
    {
        return [
            'fecha_entrega' => 'datetime',
            'graded_at' => 'datetime',
            'calificacion' => 'decimal:2',
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

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }
}
