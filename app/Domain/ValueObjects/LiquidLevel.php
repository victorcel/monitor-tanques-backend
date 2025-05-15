<?php

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\InvalidLiquidLevelException;

class LiquidLevel
{
    private float $value;

    public function __construct(float $value)
    {
        if ($value < 0) {
            throw new InvalidLiquidLevelException("Liquid level cannot be negative");
        }
        
        $this->value = $value;
    }

    public function value(): float
    {
        return $this->value;
    }

    public function equals(LiquidLevel $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
