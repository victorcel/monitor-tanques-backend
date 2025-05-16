<?php

namespace App\Application\DTOs;

class UpdateTankDTO
{
    private string $name;
    private ?string $location;
    private float $capacity;
    private string $serialNumber;
    private float $height;
    private ?float $diameter;
    private bool $active;

    public function __construct(
        string $name,
        string $serialNumber,
        float $capacity,
        float $height,
        ?string $location = null,
        ?float $diameter = null,
        bool $active = true
    ) {
        $this->name = $name;
        $this->serialNumber = $serialNumber;
        $this->capacity = $capacity;
        $this->height = $height;
        $this->location = $location;
        $this->diameter = $diameter;
        $this->active = $active;
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
        return $this->active;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'],
            $data['serial_number'],
            (float) $data['capacity'],
            (float) $data['height'],
            $data['location'] ?? null,
            isset($data['diameter']) ? (float) $data['diameter'] : null,
            $data['active'] ?? true
        );
    }
}