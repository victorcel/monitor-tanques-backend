<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TankReadingModel extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tank_readings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'tank_id',
        'liquid_level',
        'volume',
        'percentage',
        'reading_timestamp',
        'temperature',
        'raw_data'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'liquid_level' => 'float',
        'volume' => 'float',
        'percentage' => 'float',
        'reading_timestamp' => 'datetime',
        'temperature' => 'float',
        'raw_data' => 'json'
    ];

    /**
     * Get the tank that owns the reading.
     */
    public function tank()
    {
        return $this->belongsTo(TankModel::class, 'tank_id', 'id');
    }
}
