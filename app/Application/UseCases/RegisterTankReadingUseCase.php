<?php

namespace App\Application\UseCases;

use App\Application\DTOs\TankReadingDTO;
use App\Domain\Models\TankReading;
use App\Domain\Repositories\TankReadingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\ValueObjects\LiquidLevel;
use DateTimeImmutable;
use Illuminate\Support\Str;

class RegisterTankReadingUseCase
{
    private TankReadingRepositoryInterface $tankReadingRepository;
    private TankRepositoryInterface $tankRepository;

    public function __construct(
        TankReadingRepositoryInterface $tankReadingRepository,
        TankRepositoryInterface $tankRepository
    ) {
        $this->tankReadingRepository = $tankReadingRepository;
        $this->tankRepository = $tankRepository;
    }

    public function execute(TankReadingDTO $dto): array
    {
        // Verificar que el tanque existe
        $tank = $this->tankRepository->findById($dto->tankId);
        if (!$tank) {
            throw new \InvalidArgumentException("Tank with ID {$dto->tankId} not found");
        }

        // Crear un Value Object para el nivel de líquido
        $liquidLevel = new LiquidLevel($dto->liquidLevel);
        
        // Crear una lectura de tanque
        $reading = new TankReading(
            Str::uuid()->toString(),
            $dto->tankId,
            $liquidLevel,
            new DateTimeImmutable($dto->timestamp)
        );
        
        // Guardar la lectura
        $this->tankReadingRepository->save($reading);
        
        // Actualizar el nivel actual del tanque
        $tank->updateLevel($liquidLevel);
        $this->tankRepository->update($tank);
        
        return [
            'id' => $reading->id(),
            'tank_id' => $reading->tankId(),
            'liquid_level' => $reading->level()->value(),
            'timestamp' => $reading->timestamp()->format('Y-m-d H:i:s'),
            'fill_percentage' => $tank->fillPercentage()
        ];
    }
}
