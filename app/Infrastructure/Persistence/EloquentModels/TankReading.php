<?php

namespace App\Infrastructure\Persistence\EloquentModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TankReading extends Model
{
    protected $fillable = [
        'tank_id',
        'liquid_level',
        'volume',
        'percentage',
        'temperature',
        'reading_timestamp',
        'raw_data',
    ];

    protected $casts = [
        'liquid_level' => 'float',
        'volume' => 'float',
        'percentage' => 'float',
        'temperature' => 'float',
        'reading_timestamp' => 'datetime',
        'raw_data' => 'array',
    ];

    public function tank(): BelongsTo
    {
        return $this->belongsTo(Tank::class);
    }
}