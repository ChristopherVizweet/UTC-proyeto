<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_grade_id',
        'academic_period_id',
        'nombre_grupo',
        'capacidad_grupo',
        'descripcion_grupo',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacidad_grupo' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function schoolGrade(): BelongsTo
    {
        return $this->belongsTo(SchoolGrade::class);
    }

    public function academicPeriod(): BelongsTo
    {
        return $this->belongsTo(AcademicPeriod::class);
    }
}
