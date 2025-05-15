<?php

namespace App\Domain\Models;

use App\Domain\ValueObjects\LiquidLevel;
use DateTimeImmutable;

class TankReading
{
    private string $id;
    private string $tankId;
    private LiquidLevel $level;
    private DateTimeImmutable $timestamp;

    public function __construct(
        string $id,
        string $tankId,
        LiquidLevel $level,
        DateTimeImmutable $timestamp
    ) {
        $this->id = $id;
        $this->tankId = $tankId;
        $this->level = $level;
        $this->timestamp = $timestamp;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function tankId(): string
    {
        return $this->tankId;
    }

    public function level(): LiquidLevel
    {
        return $this->level;
    }

    public function timestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
}
