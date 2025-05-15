<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TankModel extends Model
{
    use HasUuids;
    
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
        'id',
        'name',
        'capacity',
        'current_level',
        'location',
    ];

    /**
     * Get the tank's readings.
     */
    public function readings()
    {
        return $this->hasMany(TankReadingModel::class, 'tank_id', 'id');
    }
}
