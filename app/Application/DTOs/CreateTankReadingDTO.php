<?php

namespace App\Application\DTOs;

use DateTime;

class CreateTankReadingDTO
{
    private int $tankId;
    private float $liquidLevel;
    private ?float $temperature;
    private ?array $rawData;
    private DateTime $readingTimestamp;

    public function __construct(
        int $tankId,
        float $liquidLevel,
        ?DateTime $readingTimestamp = null,
        ?float $temperature = null,
        ?array $rawData = null
    ) {
        $this->tankId = $tankId;
        $this->liquidLevel = $liquidLevel;
        $this->readingTimestamp = $readingTimestamp ?? new DateTime();
        $this->temperature = $temperature;
        $this->rawData = $rawData;
    }

    public function getTankId(): int
    {
        return $this->tankId;
    }

    public function getLiquidLevel(): float
    {
        return $this->liquidLevel;
    }

    public function getReadingTimestamp(): DateTime
    {
        return $this->readingTimestamp;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public static function fromArray(array $data): self
    {
        $readingTimestamp = null;
        if (isset($data['reading_timestamp'])) {
            $readingTimestamp = new DateTime($data['reading_timestamp']);
        }

        return new self(
            (int) $data['tank_id'],
            (float) $data['liquid_level'],
            $readingTimestamp,
            isset($data['temperature']) ? (float) $data['temperature'] : null,
            $data['raw_data'] ?? null
        );
    }
}