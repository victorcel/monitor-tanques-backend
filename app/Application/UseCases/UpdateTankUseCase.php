<?php

namespace App\Application\UseCases;

use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Exceptions\TankNotFoundException;

class UpdateTankUseCase
{
    private TankRepositoryInterface $tankRepository;

    public function __construct(TankRepositoryInterface $tankRepository)
    {
        $this->tankRepository = $tankRepository;
    }

    /**
     * Actualiza un tanque existente
     * 
     * @throws TankNotFoundException Si el tanque no existe
     */
    public function execute(int $id, array $data): Tank
    {
        $tank = $this->tankRepository->findById($id);
        
        if (!$tank) {
            throw new TankNotFoundException("Tanque con ID {$id} no encontrado");
        }
        
        // Actualizar propiedades según los datos proporcionados
        if (isset($data['name'])) {
            $tank->setName($data['name']);
        }
        
        if (isset($data['location'])) {
            $tank->setLocation($data['location']);
        }
        
        if (isset($data['capacity'])) {
            $tank->setCapacity((float) $data['capacity']);
        }
        
        if (isset($data['height'])) {
            $tank->setHeight((float) $data['height']);
        }
        
        if (isset($data['diameter'])) {
            $tank->setDiameter($data['diameter'] === null ? null : (float) $data['diameter']);
        }
        
        if (isset($data['is_active'])) {
            $data['is_active'] ? $tank->activate() : $tank->deactivate();
        }
        
        // Guardar y retornar tanque actualizado
        return $this->tankRepository->save($tank);
    }
}