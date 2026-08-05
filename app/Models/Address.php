<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
     use HasFactory;

    protected $fillable = [
        'user_id',
        'calle_usuario',
        'colonia_usuario',
        'exterior_usuario',
        'interior_usuario',
        'municipio_usuario',
        'estado_usuario',
        'cp_usuario',
    ];
      public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
