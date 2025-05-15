<?php

namespace App\Application\DTOs;

class TankReadingDTO
{
    public string $tankId;
    public float $liquidLevel;
    public string $timestamp;

    public function __construct(
        string $tankId,
        float $liquidLevel,
        string $timestamp
    ) {
        $this->tankId = $tankId;
        $this->liquidLevel = $liquidLevel;
        $this->timestamp = $timestamp;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['tank_id'],
            (float) $data['liquid_level'],
            $data['timestamp'] ?? now()->toIso8601String()
        );
    }

    public function toArray(): array
    {
        return [
            'tank_id' => $this->tankId,
            'liquid_level' => $this->liquidLevel,
            'timestamp' => $this->timestamp,
        ];
    }
}
