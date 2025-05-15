<?php

namespace App\Application\UseCases;

use App\Application\DTOs\CreateTankReadingDTO;
use App\Domain\Models\Tank;
use App\Domain\Models\TankReading;
use App\Domain\Repositories\TankReadingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Exceptions\TankNotFoundException;
use App\Domain\Services\VolumeCalculatorService;

class RegisterTankReadingUseCase
{
    private TankRepositoryInterface $tankRepository;
    private TankReadingRepositoryInterface $tankReadingRepository;
    private VolumeCalculatorService $volumeCalculator;

    public function __construct(
        TankRepositoryInterface $tankRepository,
        TankReadingRepositoryInterface $tankReadingRepository,
        VolumeCalculatorService $volumeCalculator
    ) {
        $this->tankRepository = $tankRepository;
        $this->tankReadingRepository = $tankReadingRepository;
        $this->volumeCalculator = $volumeCalculator;
    }

    /**
     * Registra una nueva lectura para un tanque.
     * 
     * @throws TankNotFoundException cuando el tanque no existe
     */
    public function execute(CreateTankReadingDTO $dto): TankReading
    {
        // Verificar que el tanque existe
        $tank = $this->tankRepository->findById($dto->getTankId());
        
        if (!$tank) {
            throw new TankNotFoundException("Tanque con ID {$dto->getTankId()} no encontrado");
        }
        
        // Calcular volumen y porcentaje
        $volume = $this->calculateVolume($tank, $dto->getLiquidLevel());
        $percentage = $this->calculatePercentage($volume, $tank->getCapacity());
        
        // Crear nueva lectura
        $reading = new TankReading(
            0, // ID temporal, será asignado por el repositorio
            $dto->getTankId(),
            $dto->getLiquidLevel(),
            $volume,
            $percentage,
            $dto->getReadingTimestamp(),
            $dto->getTemperature(),
            $dto->getRawData()
        );
        
        // Guardar y retornar lectura
        return $this->tankReadingRepository->save($reading);
    }
    
    private function calculateVolume(Tank $tank, float $liquidLevel): float
    {
        return $this->volumeCalculator->calculateVolume($tank, $liquidLevel);
    }
    
    private function calculatePercentage(float $volume, float $capacity): float
    {
        if ($capacity <= 0) {
            return 0;
        }
        
        $percentage = ($volume / $capacity) * 100;
        
        // Limitar a rango 0-100
        return max(0, min(100, $percentage));
    }
}
