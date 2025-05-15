<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Models\Tank as DomainTank;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Infrastructure\Persistence\EloquentModels\Tank as EloquentTank;
use DateTime;

class EloquentTankRepository implements TankRepositoryInterface
{
    /**
     * Convierte un modelo Eloquent a un modelo de dominio
     */
    private function toDomainModel(EloquentTank $eloquentModel): DomainTank
    {
        return new DomainTank(
            $eloquentModel->id,
            $eloquentModel->name,
            $eloquentModel->serial_number,
            $eloquentModel->capacity,
            $eloquentModel->height,
            $eloquentModel->location,
            $eloquentModel->diameter,
            $eloquentModel->is_active,
            new DateTime($eloquentModel->created_at),
            new DateTime($eloquentModel->updated_at)
        );
    }

    /**
     * Encuentra un tanque por su ID
     */
    public function findById(int $id): ?DomainTank
    {
        $tank = EloquentTank::find($id);
        
        if (!$tank) {
            return null;
        }
        
        return $this->toDomainModel($tank);
    }
    
    /**
     * Encuentra un tanque por su número de serie
     */
    public function findBySerialNumber(string $serialNumber): ?DomainTank
    {
        $tank = EloquentTank::where('serial_number', $serialNumber)->first();
        
        if (!$tank) {
            return null;
        }
        
        return $this->toDomainModel($tank);
    }
    
    /**
     * Obtiene todos los tanques
     * 
     * @return DomainTank[]
     */
    public function findAll(): array
    {
        $tanks = EloquentTank::all();
        $domainTanks = [];
        
        foreach ($tanks as $tank) {
            $domainTanks[] = $this->toDomainModel($tank);
        }
        
        return $domainTanks;
    }
    
    /**
     * Guarda un nuevo tanque o actualiza uno existente
     */
    public function save(DomainTank $tank): DomainTank
    {
        if ($tank->getId() === 0) {
            // Es un nuevo tanque
            $eloquentTank = new EloquentTank();
        } else {
            // Es un tanque existente
            $eloquentTank = EloquentTank::find($tank->getId());
            
            if (!$eloquentTank) {
                // Si no existe, creamos uno nuevo
                $eloquentTank = new EloquentTank();
            }
        }
        
        $eloquentTank->name = $tank->getName();
        $eloquentTank->location = $tank->getLocation();
        $eloquentTank->capacity = $tank->getCapacity();
        $eloquentTank->serial_number = $tank->getSerialNumber();
        $eloquentTank->height = $tank->getHeight();
        $eloquentTank->diameter = $tank->getDiameter();
        $eloquentTank->is_active = $tank->isActive();
        
        $eloquentTank->save();
        
        return $this->toDomainModel($eloquentTank);
    }
    
    /**
     * Elimina un tanque por su ID
     */
    public function delete(int $id): bool
    {
        $tank = EloquentTank::find($id);
        
        if (!$tank) {
            return false;
        }
        
        return $tank->delete();
    }
}