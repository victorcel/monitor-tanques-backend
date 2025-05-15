<?php

namespace App\Domain\Repositories;

use App\Domain\Models\Tank;

interface TankRepositoryInterface
{
    /**
     * Encuentra un tanque por su ID
     */
    public function findById(int $id): ?Tank;
    
    /**
     * Encuentra un tanque por su número de serie
     */
    public function findBySerialNumber(string $serialNumber): ?Tank;
    
    /**
     * Obtiene todos los tanques
     * 
     * @return Tank[]
     */
    public function findAll(): array;
    
    /**
     * Guarda un nuevo tanque o actualiza uno existente
     */
    public function save(Tank $tank): Tank;
    
    /**
     * Elimina un tanque por su ID
     */
    public function delete(int $id): bool;
}
