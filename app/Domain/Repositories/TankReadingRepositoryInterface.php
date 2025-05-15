<?php

namespace App\Domain\Repositories;

use App\Domain\Models\TankReading;
use DateTime;

interface TankReadingRepositoryInterface
{
    /**
     * Encuentra una lectura por su ID
     */
    public function findById(int $id): ?TankReading;
    
    /**
     * Guarda una nueva lectura
     */
    public function save(TankReading $reading): TankReading;
    
    /**
     * Obtiene todas las lecturas de un tanque
     * 
     * @return TankReading[]
     */
    public function findByTankId(int $tankId): array;
    
    /**
     * Obtiene las lecturas de un tanque en un rango de fechas
     * 
     * @return TankReading[]
     */
    public function findByTankIdAndDateRange(int $tankId, DateTime $startDate, DateTime $endDate): array;
    
    /**
     * Obtiene la última lectura de un tanque
     */
    public function findLatestByTankId(int $tankId): ?TankReading;
    
    /**
     * Elimina lecturas antiguas de un tanque
     * 
     * @return int Número de registros eliminados
     */
    public function deleteOldReadings(int $tankId, DateTime $olderThan): int;
}
