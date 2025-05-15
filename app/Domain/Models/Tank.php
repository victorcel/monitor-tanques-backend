<?php

namespace App\Domain\Models;

use DateTime;

class Tank
{
    private int $id;
    private string $name;
    private ?string $location;
    private float $capacity;
    private string $serialNumber;
    private float $height;
    private ?float $diameter;
    private bool $isActive;
    private DateTime $createdAt;
    private DateTime $updatedAt;

    public function __construct(
        int $id,
        string $name,
        string $serialNumber,
        float $capacity,
        float $height,
        ?string $location = null,
        ?float $diameter = null,
        bool $isActive = true,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->location = $location;
        $this->capacity = $capacity;
        $this->serialNumber = $serialNumber;
        $this->height = $height;
        $this->diameter = $diameter;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt ?? new DateTime();
        $this->updatedAt = $updatedAt ?? new DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getCapacity(): float
    {
        return $this->capacity;
    }

    public function getSerialNumber(): string
    {
        return $this->serialNumber;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getDiameter(): ?float
    {
        return $this->diameter;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->markAsUpdated();
    }

    public function setLocation(?string $location): void
    {
        $this->location = $location;
        $this->markAsUpdated();
    }

    public function setCapacity(float $capacity): void
    {
        $this->capacity = $capacity;
        $this->markAsUpdated();
    }

    public function setHeight(float $height): void
    {
        $this->height = $height;
        $this->markAsUpdated();
    }

    public function setDiameter(?float $diameter): void
    {
        $this->diameter = $diameter;
        $this->markAsUpdated();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->markAsUpdated();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->markAsUpdated();
    }

    private function markAsUpdated(): void
    {
        $this->updatedAt = new DateTime();
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'serial_number' => $this->serialNumber,
            'height' => $this->height,
            'diameter' => $this->diameter,
            'is_active' => $this->isActive,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
