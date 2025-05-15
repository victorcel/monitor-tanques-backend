<?php

namespace App\Application\UseCases;

use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;

class ListTanksUseCase
{
    private TankRepositoryInterface $tankRepository;

    public function __construct(TankRepositoryInterface $tankRepository)
    {
        $this->tankRepository = $tankRepository;
    }

    /**
     * Obtiene todos los tanques
     * 
     * @return Tank[]
     */
    public function execute(): array
    {
        return $this->tankRepository->findAll();
    }
}