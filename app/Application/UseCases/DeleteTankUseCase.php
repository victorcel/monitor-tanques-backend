<?php

namespace App\Application\UseCases;

use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Exceptions\TankNotFoundException;

class DeleteTankUseCase
{
    private TankRepositoryInterface $tankRepository;

    public function __construct(TankRepositoryInterface $tankRepository)
    {
        $this->tankRepository = $tankRepository;
    }

    /**
     * Elimina un tanque por su ID
     * 
     * @throws TankNotFoundException Si el tanque no existe
     */
    public function execute(int $id): void
    {
        // Verificar que el tanque existe
        $tank = $this->tankRepository->findById($id);
        
        if (!$tank) {
            throw new TankNotFoundException("Tanque con ID {$id} no encontrado");
        }
        
        // Eliminar el tanque
        $this->tankRepository->delete($id);
    }
}