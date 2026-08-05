<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolGrade extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre_grado',
        'nivel_grado',
        'descripcion_grado',
    ];

     protected function casts(): array
    {
        return [
            'nivel_grado' => 'integer',
        ];
    }

    public function schoolGroups(): HasMany
    {
        return $this->hasMany(SchoolGroup::class);
    }
}
