<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    public function enrollments(): HasMany
{
    return $this->hasMany(Enrollment::class);
}

public function students(): BelongsToMany
{
    return $this->belongsToMany(
        User::class,
        'enrollments',
        'school_group_id',
        'student_id'
    )
        ->withPivot([
            'fecha_inscripcion',
            'estado_inscripcion',
        ])
        ->withTimestamps();
}

public function teachingAssignments(): HasMany
{
    return $this->hasMany(TeachingAssignment::class);
}

public function teachers(): BelongsToMany
{
    return $this->belongsToMany(
        User::class,
        'teaching_assignments',
        'school_group_id',
        'teacher_id'
    )
        ->withPivot([
            'subject_id',
            'is_active',
        ])
        ->withTimestamps();
}

public function subjects(): BelongsToMany
{
    return $this->belongsToMany(
        Subject::class,
        'teaching_assignments',
        'school_group_id',
        'subject_id'
    )
        ->withPivot([
            'teacher_id',
            'is_active',
        ])
        ->withTimestamps();
}
}
