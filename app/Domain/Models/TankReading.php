<?php

namespace App\Domain\Models;

use DateTime;

class TankReading
{
    private int $id;
    private int $tankId;
    private float $liquidLevel;
    private float $volume;
    private float $percentage;
    private ?float $temperature;
    private DateTime $readingTimestamp;
    private ?array $rawData;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        int $id,
        int $tankId,
        float $liquidLevel,
        float $volume,
        float $percentage,
        DateTime $readingTimestamp,
        ?float $temperature = null,
        ?array $rawData = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->tankId = $tankId;
        $this->liquidLevel = $liquidLevel;
        $this->volume = $volume;
        $this->percentage = $percentage;
        $this->temperature = $temperature;
        $this->readingTimestamp = $readingTimestamp;
        $this->rawData = $rawData;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTankId(): int
    {
        return $this->tankId;
    }

    public function getLiquidLevel(): float
    {
        return $this->liquidLevel;
    }

    public function getVolume(): float
    {
        return $this->volume;
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function getReadingTimestamp(): DateTime
    {
        return $this->readingTimestamp;
    }

    public function getRawData(): ?array
    {
        return $this->rawData;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'tank_id' => $this->tankId,
            'liquid_level' => $this->liquidLevel,
            'volume' => $this->volume,
            'percentage' => $this->percentage,
            'temperature' => $this->temperature,
            'reading_timestamp' => $this->readingTimestamp->format('Y-m-d H:i:s'),
            'raw_data' => $this->rawData,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
