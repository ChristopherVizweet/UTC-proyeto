<?php

namespace App\Models;

use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Activity extends Model
{
    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    public const TYPES = ['tarea', 'archivo', 'cuestionario', 'matematicas', 'secuencia', 'sopa_letras', 'crucigrama', 'hay_ahi_ay', 'memorama', 'relacion_columnas'];

    public const STATUSES = ['borrador', 'publicada', 'cerrada'];

    protected $fillable = [
        'teaching_assignment_id',
        'created_by',
        'titulo',
        'descripcion',
        'instrucciones',
        'tipo',
        'fecha_publicacion',
        'fecha_limite',
        'puntaje_maximo',
        'permite_entrega_tardia',
        'archivo_path',
        'estado',
    ];

    protected $attributes = [
        'tipo' => 'tarea',
        'puntaje_maximo' => 10,
        'permite_entrega_tardia' => false,
        'estado' => 'borrador',
    ];

    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'datetime',
            'fecha_limite' => 'datetime',
            'puntaje_maximo' => 'decimal:2',
            'permite_entrega_tardia' => 'boolean',
        ];
    }

    public function teachingAssignment(): BelongsTo
    {
        return $this->belongsTo(TeachingAssignment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function content(): HasOne
    {
        return $this->hasOne(ActivityContent::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ActivityAttempt::class);
    }
}
