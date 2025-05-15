<?php

namespace App\Domain\Models;

use App\Domain\ValueObjects\LiquidLevel;

class Tank
{
    private string $id;
    private string $name;
    private float $capacity;
    private ?LiquidLevel $currentLevel;
    private ?string $location;

    public function __construct(
        string $id,
        string $name,
        float $capacity,
        ?LiquidLevel $currentLevel = null,
        ?string $location = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->capacity = $capacity;
        $this->currentLevel = $currentLevel;
        $this->location = $location;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function capacity(): float
    {
        return $this->capacity;
    }

    public function currentLevel(): ?LiquidLevel
    {
        return $this->currentLevel;
    }

    public function location(): ?string
    {
        return $this->location;
    }

    public function updateLevel(LiquidLevel $level): void
    {
        $this->currentLevel = $level;
    }

    public function fillPercentage(): ?float
    {
        if ($this->currentLevel === null) {
            return null;
        }

        return ($this->currentLevel->value() / $this->capacity) * 100;
    }
}
