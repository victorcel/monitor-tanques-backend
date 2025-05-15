<?php

namespace App\Application\UseCases;

use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Exceptions\TankNotFoundException;

class GetTankUseCase
{
    private TankRepositoryInterface $tankRepository;

    public function __construct(TankRepositoryInterface $tankRepository)
    {
        $this->tankRepository = $tankRepository;
    }

    /**
     * Obtiene un tanque por su ID
     * 
     * @throws TankNotFoundException Si el tanque no existe
     */
    public function execute(int $id): Tank
    {
        $tank = $this->tankRepository->findById($id);
        
        if (!$tank) {
            throw new TankNotFoundException("Tanque con ID {$id} no encontrado");
        }
        
        return $tank;
    }
}