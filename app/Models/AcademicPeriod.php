<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicPeriod extends Model
{
    use HasFactory;
    protected $fillable = [
        'nombre_periodo',
        'fecha_inicio',
        'fecha_fin',
        'estado_periodo',
    ];
     protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'estado_periodo' => 'boolean',
        ];
    }

    public function schoolGroups(): HasMany
    {
        return $this->hasMany(SchoolGroup::class);
    }
}
