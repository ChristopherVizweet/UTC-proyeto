<?php

namespace App\Models;

use Database\Factories\ActivityContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityContent extends Model
{
    /** @use HasFactory<ActivityContentFactory> */
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'configuracion',
        'contenido_version',
    ];

    protected $attributes = [
        'contenido_version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'configuracion' => 'array',
            'contenido_version' => 'integer',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
