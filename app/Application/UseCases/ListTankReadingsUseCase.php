<?php

namespace App\Application\UseCases;

use App\Domain\Models\TankReading;
use App\Domain\Repositories\TankReadingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Exceptions\TankNotFoundException;
use DateTime;

class ListTankReadingsUseCase
{
    private TankRepositoryInterface $tankRepository;
    private TankReadingRepositoryInterface $tankReadingRepository;

    public function __construct(
        TankRepositoryInterface $tankRepository,
        TankReadingRepositoryInterface $tankReadingRepository
    ) {
        $this->tankRepository = $tankRepository;
        $this->tankReadingRepository = $tankReadingRepository;
    }

    /**
     * Obtiene todas las lecturas de un tanque
     * 
     * @return TankReading[]
     * @throws TankNotFoundException Si el tanque no existe
     */
    public function execute(int $tankId): array
    {
        // Verificar que el tanque existe
        $tank = $this->tankRepository->findById($tankId);
        
        if (!$tank) {
            throw new TankNotFoundException("Tanque con ID {$tankId} no encontrado");
        }
        
        // Obtener todas las lecturas del tanque
        return $this->tankReadingRepository->findByTankId($tankId);
    }
    
    /**
     * Obtiene las lecturas de un tanque en un rango de fechas
     * 
     * @return TankReading[]
     * @throws TankNotFoundException Si el tanque no existe
     */
    public function executeWithDateRange(int $tankId, DateTime $startDate, DateTime $endDate): array
    {
        // Verificar que el tanque existe
        $tank = $this->tankRepository->findById($tankId);
        
        if (!$tank) {
            throw new TankNotFoundException("Tanque con ID {$tankId} no encontrado");
        }
        
        // Obtener las lecturas del tanque en el rango de fechas
        return $this->tankReadingRepository->findByTankIdAndDateRange($tankId, $startDate, $endDate);
    }
}