<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
      use HasFactory;
      protected $fillable = [
        'nombre_materia',
        'code',
        'descripcion_materia',
        'is_active',
    ];
     protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
    public function teachingAssignments(): HasMany
{
    return $this->hasMany(TeachingAssignment::class);
}

public function schoolGroups(): BelongsToMany
{
    return $this->belongsToMany(
        SchoolGroup::class,
        'teaching_assignments',
        'subject_id',
        'school_group_id'
    )
        ->withPivot([
            'teacher_id',
            'is_active',
        ])
        ->withTimestamps();
}

public function teachers(): BelongsToMany
{
    return $this->belongsToMany(
        User::class,
        'teaching_assignments',
        'subject_id',
        'teacher_id'
    )
        ->withPivot([
            'school_group_id',
            'is_active',
        ])
        ->withTimestamps();
}
}
