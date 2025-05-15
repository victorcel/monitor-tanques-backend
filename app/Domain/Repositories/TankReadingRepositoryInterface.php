<?php

namespace App\Domain\Repositories;

use App\Domain\Models\TankReading;
use DateTimeImmutable;

interface TankReadingRepositoryInterface
{
    public function findById(string $id): ?TankReading;
    public function findByTankId(string $tankId): array;
    public function findByTankIdAndDateRange(string $tankId, DateTimeImmutable $start, DateTimeImmutable $end): array;
    public function save(TankReading $reading): void;
    public function getLatestReadingByTankId(string $tankId): ?TankReading;
}
