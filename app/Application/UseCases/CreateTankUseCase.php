<?php

namespace App\Application\UseCases;

use App\Application\DTOs\CreateTankDTO;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;

class CreateTankUseCase
{
    private TankRepositoryInterface $tankRepository;

    public function __construct(TankRepositoryInterface $tankRepository)
    {
        $this->tankRepository = $tankRepository;
    }

    public function execute(CreateTankDTO $dto): Tank
    {
        // Verificar si ya existe un tanque con el mismo número de serie
        $existing = $this->tankRepository->findBySerialNumber($dto->getSerialNumber());
        if ($existing) {
            throw new \InvalidArgumentException("Ya existe un tanque con el número de serie {$dto->getSerialNumber()}");
        }

        // Crear un nuevo objeto Tank con ID temporal 0
        $tank = new Tank(
            0, // ID temporal, se asignará en el repositorio
            $dto->getName(),
            $dto->getSerialNumber(),
            $dto->getCapacity(),
            $dto->getHeight(),
            $dto->getLocation(),
            $dto->getDiameter(),
            true // Tanque activo por defecto
        );

        // Guardar y devolver el tanque
        return $this->tankRepository->save($tank);
    }
}