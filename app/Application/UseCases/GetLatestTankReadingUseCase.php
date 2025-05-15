<?php

namespace App\Application\UseCases;

use App\Domain\Models\TankReading;
use App\Domain\Repositories\TankReadingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Exceptions\TankNotFoundException;

class GetLatestTankReadingUseCase
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
     * Obtiene la última lectura de un tanque
     * 
     * @return TankReading|null La última lectura o null si no hay lecturas
     * @throws TankNotFoundException Si el tanque no existe
     */
    public function execute(int $tankId): ?TankReading
    {
        // Verificar que el tanque existe
        $tank = $this->tankRepository->findById($tankId);
        
        if (!$tank) {
            throw new TankNotFoundException("Tanque con ID {$tankId} no encontrado");
        }
        
        // Obtener la última lectura del tanque
        return $this->tankReadingRepository->findLatestByTankId($tankId);
    }
}