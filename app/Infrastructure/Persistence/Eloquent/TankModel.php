<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TankModel extends Model
{


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tanks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'capacity',
        'location',
        'serial_number',
        'height',
        'diameter',
        'is_active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'capacity' => 'float',
        'height' => 'float',
        'diameter' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tank's readings.
     */
    public function readings()
    {
        return $this->hasMany(TankReadingModel::class, 'tank_id', 'id');
    }
}
