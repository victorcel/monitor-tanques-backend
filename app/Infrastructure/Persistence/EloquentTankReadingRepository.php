<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Models\TankReading as DomainTankReading;
use App\Domain\Repositories\TankReadingRepositoryInterface;
use App\Infrastructure\Persistence\EloquentModels\TankReading as EloquentTankReading;
use DateTime;

class EloquentTankReadingRepository implements TankReadingRepositoryInterface
{
    /**
     * Convierte un modelo Eloquent a un modelo de dominio
     */
    private function toDomainModel(EloquentTankReading $eloquentModel): DomainTankReading
    {
        // Convertir raw_data de string JSON a array si no es nulo
        $rawData = $eloquentModel->raw_data ? json_decode($eloquentModel->raw_data, true) : null;
        
        return new DomainTankReading(
            $eloquentModel->id,
            $eloquentModel->tank_id,
            $eloquentModel->liquid_level,
            $eloquentModel->volume,
            $eloquentModel->percentage,
            new DateTime($eloquentModel->reading_timestamp),
            $eloquentModel->temperature,
            $rawData,
            new DateTime($eloquentModel->created_at),
            new DateTime($eloquentModel->updated_at)
        );
    }

    /**
     * Encuentra una lectura por su ID
     */
    public function findById(int $id): ?DomainTankReading
    {
        $reading = EloquentTankReading::find($id);
        
        if (!$reading) {
            return null;
        }
        
        return $this->toDomainModel($reading);
    }
    
    /**
     * Guarda una nueva lectura
     */
    public function save(DomainTankReading $reading): DomainTankReading
    {
        if ($reading->getId() === 0) {
            // Es una nueva lectura
            $eloquentReading = new EloquentTankReading();
        } else {
            // Es una lectura existente
            $eloquentReading = EloquentTankReading::find($reading->getId());
            
            if (!$eloquentReading) {
                // Si no existe, creamos una nueva
                $eloquentReading = new EloquentTankReading();
            }
        }
        
        $eloquentReading->tank_id = $reading->getTankId();
        $eloquentReading->liquid_level = $reading->getLiquidLevel();
        $eloquentReading->volume = $reading->getVolume();
        $eloquentReading->percentage = $reading->getPercentage();
        $eloquentReading->temperature = $reading->getTemperature();
        $eloquentReading->reading_timestamp = $reading->getReadingTimestamp()->format('Y-m-d H:i:s');
        
        // Convertir raw_data de array a JSON string si no es nulo
        $eloquentReading->raw_data = $reading->getRawData() ? json_encode($reading->getRawData()) : null;
        
        $eloquentReading->save();
        
        return $this->toDomainModel($eloquentReading);
    }
    
    /**
     * Obtiene todas las lecturas de un tanque
     * 
     * @return DomainTankReading[]
     */
    public function findByTankId(int $tankId): array
    {
        $readings = EloquentTankReading::where('tank_id', $tankId)
            ->orderBy('reading_timestamp', 'desc')
            ->get();
        
        $domainReadings = [];
        foreach ($readings as $reading) {
            $domainReadings[] = $this->toDomainModel($reading);
        }
        
        return $domainReadings;
    }
    
    /**
     * Obtiene las lecturas de un tanque en un rango de fechas
     * 
     * @return DomainTankReading[]
     */
    public function findByTankIdAndDateRange(int $tankId, DateTime $startDate, DateTime $endDate): array
    {
        $readings = EloquentTankReading::where('tank_id', $tankId)
            ->whereBetween('reading_timestamp', [
                $startDate->format('Y-m-d H:i:s'),
                $endDate->format('Y-m-d H:i:s')
            ])
            ->orderBy('reading_timestamp', 'desc')
            ->get();
        
        $domainReadings = [];
        foreach ($readings as $reading) {
            $domainReadings[] = $this->toDomainModel($reading);
        }
        
        return $domainReadings;
    }
    
    /**
     * Obtiene la última lectura de un tanque
     */
    public function findLatestByTankId(int $tankId): ?DomainTankReading
    {
        $reading = EloquentTankReading::where('tank_id', $tankId)
            ->orderBy('reading_timestamp', 'desc')
            ->first();
        
        if (!$reading) {
            return null;
        }
        
        return $this->toDomainModel($reading);
    }
    
    /**
     * Elimina lecturas antiguas de un tanque
     * 
     * @return int Número de registros eliminados
     */
    public function deleteOldReadings(int $tankId, DateTime $olderThan): int
    {
        return EloquentTankReading::where('tank_id', $tankId)
            ->where('reading_timestamp', '<', $olderThan->format('Y-m-d H:i:s'))
            ->delete();
    }
}