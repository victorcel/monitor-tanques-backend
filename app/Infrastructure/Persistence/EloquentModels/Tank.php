<?php

namespace App\Infrastructure\Persistence\EloquentModels;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tank extends Model
{
    protected $fillable = [
        'name',
        'location',
        'capacity',
        'serial_number',
        'height',
        'diameter',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'float',
        'height' => 'float',
        'diameter' => 'float',
        'is_active' => 'boolean',
    ];

    public function readings(): HasMany
    {
        return $this->hasMany(TankReading::class);
    }
}