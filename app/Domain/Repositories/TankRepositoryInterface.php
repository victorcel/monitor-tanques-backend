<?php

namespace App\Domain\Repositories;

use App\Domain\Models\Tank;

interface TankRepositoryInterface
{
    public function findById(string $id): ?Tank;
    public function findAll(): array;
    public function save(Tank $tank): void;
    public function update(Tank $tank): void;
    public function delete(string $id): void;
}
