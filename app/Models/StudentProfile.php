<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
     use HasFactory;

    protected $fillable = [
        'user_id',
        'nombre_tutor',
        'parentesco_tutor',
        'telefono_tutor',
        'telefonoSecundario_tutor',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
