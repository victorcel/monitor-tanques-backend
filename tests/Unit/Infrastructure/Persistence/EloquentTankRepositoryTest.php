<?php

namespace Tests\Unit\Infrastructure\Persistence;

use App\Domain\Models\Tank as DomainTank;
use App\Infrastructure\Persistence\EloquentTankRepository;
use App\Infrastructure\Persistence\EloquentModels\Tank as EloquentTank;
use DateTime;
use Tests\TestCase;


class EloquentTankRepositoryTest extends TestCase
{

    private EloquentTankRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentTankRepository();
    }

    public function test_find_by_id_returns_null_when_tank_not_found(): void
    {
        // Act
        $result = $this->repository->findById(999);

        // Assert
        $this->assertNull($result);
    }

    public function test_find_by_id_returns_domain_model_when_tank_found(): void
    {
        // Arrange
        $tank = EloquentTank::create([
            'name' => 'Test Tank',
            'serial_number' => 'SN12345',
            'capacity' => 1000.0,
            'height' => 200.0,
            'location' => 'Test Location',
            'diameter' => 50.0,
            'is_active' => true
        ]);

        // Act
        $result = $this->repository->findById($tank->id);

        // Assert
        $this->assertInstanceOf(DomainTank::class, $result);
        $this->assertEquals($tank->id, $result->getId());
        $this->assertEquals($tank->name, $result->getName());
        $this->assertEquals($tank->serial_number, $result->getSerialNumber());
        $this->assertEquals($tank->capacity, $result->getCapacity());
        $this->assertEquals($tank->height, $result->getHeight());
        $this->assertEquals($tank->location, $result->getLocation());
        $this->assertEquals($tank->diameter, $result->getDiameter());
        $this->assertEquals($tank->is_active, $result->isActive());
    }

    public function test_find_by_serial_number_returns_null_when_tank_not_found(): void
    {
        // Act
        $result = $this->repository->findBySerialNumber('NONEXISTENT');

        // Assert
        $this->assertNull($result);
    }

    public function test_find_by_serial_number_returns_domain_model_when_tank_found(): void
    {
        // Arrange
        $serialNumber = 'SN98765';
        $tank = EloquentTank::create([
            'name' => 'Test Tank 2',
            'serial_number' => $serialNumber,
            'capacity' => 2000.0,
            'height' => 300.0,
            'location' => 'Test Location 2',
            'diameter' => 100.0,
            'is_active' => true
        ]);

        // Act
        $result = $this->repository->findBySerialNumber($serialNumber);

        // Assert
        $this->assertInstanceOf(DomainTank::class, $result);
        $this->assertEquals($tank->id, $result->getId());
        $this->assertEquals($serialNumber, $result->getSerialNumber());
    }

    public function test_find_all_returns_empty_array_when_no_tanks(): void
    {
        // Act
        $result = $this->repository->findAll();

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_find_all_returns_array_of_domain_models(): void
    {
        // Arrange
        EloquentTank::create([
            'name' => 'Tank 1',
            'serial_number' => 'SN-1',
            'capacity' => 1000.0,
            'height' => 200.0,
            'location' => 'Location 1',
            'diameter' => 50.0,
            'is_active' => true
        ]);

        EloquentTank::create([
            'name' => 'Tank 2',
            'serial_number' => 'SN-2',
            'capacity' => 2000.0,
            'height' => 300.0,
            'location' => 'Location 2',
            'diameter' => 75.0,
            'is_active' => false
        ]);

        // Act
        $result = $this->repository->findAll();

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(DomainTank::class, $result);
        $this->assertEquals('Tank 1', $result[0]->getName());
        $this->assertEquals('Tank 2', $result[1]->getName());
    }

    public function test_save_creates_new_tank(): void
    {
        // Arrange
        $domainTank = new DomainTank(
            0, // ID 0 indica tanque nuevo
            'New Tank',
            'SN-NEW',
            3000.0,
            400.0,
            'New Location',
            125.0,
            true,
            new DateTime(),
            new DateTime()
        );

        // Act
        $savedTank = $this->repository->save($domainTank);

        // Assert
        $this->assertInstanceOf(DomainTank::class, $savedTank);
        $this->assertGreaterThan(0, $savedTank->getId()); // Debe tener un ID asignado
        $this->assertEquals('New Tank', $savedTank->getName());
        $this->assertEquals('SN-NEW', $savedTank->getSerialNumber());

        // Verificar en base de datos
        $this->assertDatabaseHas('tanks', [
            'name' => 'New Tank',
            'serial_number' => 'SN-NEW',
            'capacity' => 3000.0
        ]);
    }

    public function test_save_updates_existing_tank(): void
    {
        // Arrange - Crear primero un tanque
        $tank = EloquentTank::create([
            'name' => 'Old Name',
            'serial_number' => 'SN-OLD',
            'capacity' => 1000.0,
            'height' => 200.0,
            'location' => 'Old Location',
            'diameter' => 50.0,
            'is_active' => true
        ]);

        // Crear un modelo de dominio con el ID existente pero datos actualizados
        $domainTank = new DomainTank(
            $tank->id,
            'Updated Name',
            'SN-OLD',
            1500.0,
            250.0,
            'Updated Location',
            75.0,
            false,
            new DateTime(),
            new DateTime()
        );

        // Act
        $updatedTank = $this->repository->save($domainTank);

        // Assert
        $this->assertEquals($tank->id, $updatedTank->getId());
        $this->assertEquals('Updated Name', $updatedTank->getName());
        $this->assertEquals(1500.0, $updatedTank->getCapacity());
        $this->assertEquals('Updated Location', $updatedTank->getLocation());
        $this->assertFalse($updatedTank->isActive());

        // Verificar en base de datos
        $this->assertDatabaseHas('tanks', [
            'id' => $tank->id,
            'name' => 'Updated Name',
            'location' => 'Updated Location',
            'is_active' => false
        ]);
    }

    public function test_save_handles_nonexistent_tank_id(): void
    {
        // Arrange - Crear un modelo de dominio con un ID que no existe en la base de datos
        $domainTank = new DomainTank(
            999, // ID que no existe
            'Nonexistent Tank',
            'SN-NONEXIST',
            2000.0,
            300.0,
            'Some Location',
            100.0,
            true,
            new DateTime(),
            new DateTime()
        );

        // Act
        $result = $this->repository->save($domainTank);

        // Assert
        $this->assertInstanceOf(DomainTank::class, $result);
        $this->assertNotEquals(999, $result->getId()); // Debería tener un nuevo ID

        // Verificar en base de datos
        $this->assertDatabaseHas('tanks', [
            'name' => 'Nonexistent Tank',
            'serial_number' => 'SN-NONEXIST'
        ]);
    }

    public function test_delete_returns_false_when_tank_not_found(): void
    {
        // Act
        $result = $this->repository->delete(999);

        // Assert
        $this->assertFalse($result);
    }

    public function test_delete_returns_true_and_removes_tank_when_found(): void
    {
        // Arrange
        $tank = EloquentTank::create([
            'name' => 'Tank To Delete',
            'serial_number' => 'SN-DELETE',
            'capacity' => 1000.0,
            'height' => 200.0,
            'location' => 'Delete Location',
            'diameter' => 50.0,
            'is_active' => true
        ]);

        // Act
        $result = $this->repository->delete($tank->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseMissing('tanks', ['id' => $tank->id]);
    }
}
