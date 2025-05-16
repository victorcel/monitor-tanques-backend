<?php

namespace Tests\Unit\Infrastructure\Persistence;

use App\Domain\Models\TankReading as DomainTankReading;
use App\Infrastructure\Persistence\EloquentTankReadingRepository;
use App\Infrastructure\Persistence\EloquentModels\TankReading as EloquentTankReading;
use App\Infrastructure\Persistence\EloquentModels\Tank as EloquentTank;
use DateTime;
use Tests\TestCase;


class EloquentTankReadingRepositoryTest extends TestCase
{

    private EloquentTankReadingRepository $repository;
    private EloquentTank $tank;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentTankReadingRepository();

        // Crear un tanque para asociar con las lecturas
        $this->tank = EloquentTank::create([
            'name' => 'Test Tank',
            'serial_number' => 'SN-TEST-1',
            'capacity' => 1000.0,
            'height' => 200.0,
            'location' => 'Test Location',
            'diameter' => 50.0,
            'is_active' => true
        ]);
    }

    public function test_find_by_id_returns_null_when_reading_not_found(): void
    {
        // Act
        $result = $this->repository->findById(999);

        // Assert
        $this->assertNull($result);
    }

    public function test_find_by_id_returns_domain_model_when_reading_found(): void
    {
        // Arrange
        $rawData = ['sensor' => 'ultrasonic', 'battery' => '95%'];
        $timestamp = new DateTime('2025-05-15 10:00:00');

        $reading = EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 150.0,
            'volume' => 750.0,
            'percentage' => 75.0,
            'temperature' => 22.5,
            'reading_timestamp' => $timestamp->format('Y-m-d H:i:s'),
            'raw_data' => json_encode($rawData)
        ]);

        // Act
        $result = $this->repository->findById($reading->id);

        // Assert
        $this->assertInstanceOf(DomainTankReading::class, $result);
        $this->assertEquals($reading->id, $result->getId());
        $this->assertEquals($this->tank->id, $result->getTankId());
        $this->assertEquals(150.0, $result->getLiquidLevel());
        $this->assertEquals(750.0, $result->getVolume());
        $this->assertEquals(75.0, $result->getPercentage());
        $this->assertEquals(22.5, $result->getTemperature());
        $this->assertEquals($timestamp->format('Y-m-d H:i:s'), $result->getReadingTimestamp()->format('Y-m-d H:i:s'));
        $this->assertEquals($rawData, $result->getRawData());
    }

    public function test_save_creates_new_reading_with_raw_data(): void
    {
        // Arrange
        $timestamp = new DateTime('2025-05-15 11:00:00');
        $rawData = ['sensor_type' => 'radar', 'battery_level' => 85];

        $domainReading = new DomainTankReading(
            0, // ID 0 indica nueva lectura
            $this->tank->id,
            120.0,
            600.0,
            60.0,
            $timestamp,
            21.0,
            $rawData,
            new DateTime(),
            new DateTime()
        );

        // Act
        $savedReading = $this->repository->save($domainReading);

        // Assert
        $this->assertInstanceOf(DomainTankReading::class, $savedReading);
        $this->assertGreaterThan(0, $savedReading->getId()); // Debe tener un ID asignado
        $this->assertEquals(120.0, $savedReading->getLiquidLevel());
        $this->assertEquals($rawData, $savedReading->getRawData());

        // Verificar en base de datos
        $this->assertDatabaseHas('tank_readings', [
            'tank_id' => $this->tank->id,
            'liquid_level' => 120.0,
            'volume' => 600.0,
            'percentage' => 60.0,
            'temperature' => 21.0,
            'reading_timestamp' => $timestamp->format('Y-m-d H:i:s')
        ]);
    }

    public function test_save_creates_new_reading_without_raw_data(): void
    {
        // Arrange
        $timestamp = new DateTime('2025-05-15 12:00:00');

        $domainReading = new DomainTankReading(
            0, // ID 0 indica nueva lectura
            $this->tank->id,
            80.0,
            400.0,
            40.0,
            $timestamp,
            20.0,
            null, // Sin raw_data
            new DateTime(),
            new DateTime()
        );

        // Act
        $savedReading = $this->repository->save($domainReading);

        // Assert
        $this->assertInstanceOf(DomainTankReading::class, $savedReading);
        $this->assertNull($savedReading->getRawData());

        // Verificar en base de datos
        $this->assertDatabaseHas('tank_readings', [
            'tank_id' => $this->tank->id,
            'liquid_level' => 80.0,
            'raw_data' => null
        ]);
    }

    public function test_save_updates_existing_reading(): void
    {
        // Arrange - Crear primero una lectura
        $timestamp = new DateTime('2025-05-15 13:00:00');
        $initialReading = EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 100.0,
            'volume' => 500.0,
            'percentage' => 50.0,
            'temperature' => 20.0,
            'reading_timestamp' => $timestamp->format('Y-m-d H:i:s'),
            'raw_data' => json_encode(['inicial' => 'datos'])
        ]);

        // Crear un modelo de dominio con el ID existente pero datos actualizados
        $updatedTimestamp = new DateTime('2025-05-15 14:00:00');
        $domainReading = new DomainTankReading(
            $initialReading->id,
            $this->tank->id,
            110.0, // Nuevo nivel
            550.0, // Nuevo volumen
            55.0,  // Nuevo porcentaje
            $updatedTimestamp,
            22.0,
            ['actualizado' => 'nuevos_datos'],
            new DateTime(),
            new DateTime()
        );

        // Act
        $updatedReading = $this->repository->save($domainReading);

        // Assert
        $this->assertEquals($initialReading->id, $updatedReading->getId());
        $this->assertEquals(110.0, $updatedReading->getLiquidLevel());
        $this->assertEquals(550.0, $updatedReading->getVolume());
        $this->assertEquals(['actualizado' => 'nuevos_datos'], $updatedReading->getRawData());

        // Verificar en base de datos
        $this->assertDatabaseHas('tank_readings', [
            'id' => $initialReading->id,
            'liquid_level' => 110.0,
            'volume' => 550.0,
            'reading_timestamp' => $updatedTimestamp->format('Y-m-d H:i:s')
        ]);
    }

    public function test_save_handles_nonexistent_reading_id(): void
    {
        // Arrange - Crear un modelo de dominio con un ID que no existe en la base de datos
        $timestamp = new DateTime('2025-05-15 15:00:00');
        $domainReading = new DomainTankReading(
            999, // ID que no existe
            $this->tank->id,
            90.0,
            450.0,
            45.0,
            $timestamp,
            19.5,
            ['test' => 'data'],
            new DateTime(),
            new DateTime()
        );

        // Act
        $result = $this->repository->save($domainReading);

        // Assert
        $this->assertInstanceOf(DomainTankReading::class, $result);
        $this->assertNotEquals(999, $result->getId()); // Debería tener un nuevo ID

        // Verificar en base de datos
        $this->assertDatabaseHas('tank_readings', [
            'tank_id' => $this->tank->id,
            'liquid_level' => 90.0,
            'volume' => 450.0
        ]);
    }

    public function test_find_by_tank_id_returns_empty_array_when_no_readings(): void
    {
        // Arrange - Crear un nuevo tanque sin lecturas
        $emptyTank = EloquentTank::create([
            'name' => 'Empty Tank',
            'serial_number' => 'SN-EMPTY',
            'capacity' => 2000.0,
            'height' => 300.0,
            'location' => 'Empty Location',
            'diameter' => 70.0,
            'is_active' => true
        ]);

        // Act
        $result = $this->repository->findByTankId($emptyTank->id);

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_find_by_tank_id_returns_array_of_domain_models_ordered_by_timestamp_desc(): void
    {
        // Arrange
        $timestamp1 = new DateTime('2025-05-15 08:00:00');
        $timestamp2 = new DateTime('2025-05-15 09:00:00');
        $timestamp3 = new DateTime('2025-05-15 10:00:00');

        // Crear lecturas con diferentes timestamps
        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 100.0,
            'volume' => 500.0,
            'percentage' => 50.0,
            'temperature' => 20.0,
            'reading_timestamp' => $timestamp2->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 80.0,
            'volume' => 400.0,
            'percentage' => 40.0,
            'temperature' => 19.0,
            'reading_timestamp' => $timestamp1->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 120.0,
            'volume' => 600.0,
            'percentage' => 60.0,
            'temperature' => 21.0,
            'reading_timestamp' => $timestamp3->format('Y-m-d H:i:s')
        ]);

        // Act
        $result = $this->repository->findByTankId($this->tank->id);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(DomainTankReading::class, $result);

        // Comprobar que están ordenados por timestamp descendiente
        $this->assertEquals(120.0, $result[0]->getLiquidLevel()); // El último timestamp primero
        $this->assertEquals(100.0, $result[1]->getLiquidLevel());
        $this->assertEquals(80.0, $result[2]->getLiquidLevel());
    }

    public function test_find_by_tank_id_and_date_range_returns_readings_in_range(): void
    {
        // Arrange
        $timestamp1 = new DateTime('2025-05-14 10:00:00');
        $timestamp2 = new DateTime('2025-05-15 10:00:00');
        $timestamp3 = new DateTime('2025-05-16 10:00:00');
        $timestamp4 = new DateTime('2025-05-17 10:00:00');

        // Crear lecturas en diferentes fechas
        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 90.0,
            'volume' => 450.0,
            'percentage' => 45.0,
            'temperature' => 19.0,
            'reading_timestamp' => $timestamp1->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 100.0,
            'volume' => 500.0,
            'percentage' => 50.0,
            'temperature' => 20.0,
            'reading_timestamp' => $timestamp2->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 110.0,
            'volume' => 550.0,
            'percentage' => 55.0,
            'temperature' => 21.0,
            'reading_timestamp' => $timestamp3->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 120.0,
            'volume' => 600.0,
            'percentage' => 60.0,
            'temperature' => 22.0,
            'reading_timestamp' => $timestamp4->format('Y-m-d H:i:s')
        ]);

        // Act - Buscar lecturas entre el 15 y 16 de mayo
        $startDate = new DateTime('2025-05-15 00:00:00');
        $endDate = new DateTime('2025-05-16 23:59:59');
        $result = $this->repository->findByTankIdAndDateRange($this->tank->id, $startDate, $endDate);

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(DomainTankReading::class, $result);

        // Comprobar que están ordenados por timestamp descendiente y solo incluye los del rango
        $this->assertEquals(110.0, $result[0]->getLiquidLevel()); // 16 de mayo primero
        $this->assertEquals(100.0, $result[1]->getLiquidLevel()); // 15 de mayo después
    }

    public function test_find_latest_by_tank_id_returns_null_when_no_readings(): void
    {
        // Arrange - Crear un nuevo tanque sin lecturas
        $emptyTank = EloquentTank::create([
            'name' => 'Empty Tank 2',
            'serial_number' => 'SN-EMPTY-2',
            'capacity' => 3000.0,
            'height' => 400.0,
            'location' => 'Empty Location 2',
            'diameter' => 80.0,
            'is_active' => true
        ]);

        // Act
        $result = $this->repository->findLatestByTankId($emptyTank->id);

        // Assert
        $this->assertNull($result);
    }

    public function test_find_latest_by_tank_id_returns_latest_reading(): void
    {
        // Arrange
        $timestamp1 = new DateTime('2025-05-15 08:00:00');
        $timestamp2 = new DateTime('2025-05-15 09:00:00');
        $timestamp3 = new DateTime('2025-05-15 10:00:00'); // Más reciente

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 80.0,
            'volume' => 400.0,
            'percentage' => 40.0,
            'temperature' => 19.0,
            'reading_timestamp' => $timestamp1->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 100.0,
            'volume' => 500.0,
            'percentage' => 50.0,
            'temperature' => 20.0,
            'reading_timestamp' => $timestamp2->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 120.0,
            'volume' => 600.0,
            'percentage' => 60.0,
            'temperature' => 21.0,
            'reading_timestamp' => $timestamp3->format('Y-m-d H:i:s')
        ]);

        // Act
        $result = $this->repository->findLatestByTankId($this->tank->id);

        // Assert
        $this->assertInstanceOf(DomainTankReading::class, $result);
        $this->assertEquals(120.0, $result->getLiquidLevel()); // Debe ser la lectura más reciente
        $this->assertEquals(600.0, $result->getVolume());
        $this->assertEquals($timestamp3->format('Y-m-d H:i:s'), $result->getReadingTimestamp()->format('Y-m-d H:i:s'));
    }

    public function test_delete_old_readings_removes_readings_older_than_date(): void
    {
        // Arrange
        $oldTimestamp1 = new DateTime('2025-04-01 10:00:00');
        $oldTimestamp2 = new DateTime('2025-04-15 10:00:00');
        $recentTimestamp = new DateTime('2025-05-15 10:00:00');

        // Crear lecturas en diferentes fechas
        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 80.0,
            'volume' => 400.0,
            'percentage' => 40.0,
            'temperature' => 19.0,
            'reading_timestamp' => $oldTimestamp1->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 90.0,
            'volume' => 450.0,
            'percentage' => 45.0,
            'temperature' => 19.5,
            'reading_timestamp' => $oldTimestamp2->format('Y-m-d H:i:s')
        ]);

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 100.0,
            'volume' => 500.0,
            'percentage' => 50.0,
            'temperature' => 20.0,
            'reading_timestamp' => $recentTimestamp->format('Y-m-d H:i:s')
        ]);

        // Act - Eliminar lecturas anteriores al 1 de mayo
        $cutoffDate = new DateTime('2025-05-01 00:00:00');
        $deletedCount = $this->repository->deleteOldReadings($this->tank->id, $cutoffDate);

        // Assert
        $this->assertEquals(2, $deletedCount); // Se deberían haber eliminado 2 lecturas

        // Verificar que solo queda la lectura más reciente
        $remainingReadings = EloquentTankReading::where('tank_id', $this->tank->id)->get();
        $this->assertCount(1, $remainingReadings);
        $this->assertEquals(100.0, $remainingReadings->first()->liquid_level);
        $this->assertEquals($recentTimestamp->format('Y-m-d H:i:s'), $remainingReadings->first()->reading_timestamp);

        // Verificar usando el repositorio
        $domainReadings = $this->repository->findByTankId($this->tank->id);
        $this->assertCount(1, $domainReadings);
        $this->assertEquals(100.0, $domainReadings[0]->getLiquidLevel());
    }

    public function test_delete_old_readings_returns_zero_when_no_old_readings(): void
    {
        // Arrange
        $recentTimestamp = new DateTime('2025-05-15 10:00:00');

        EloquentTankReading::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 100.0,
            'volume' => 500.0,
            'percentage' => 50.0,
            'temperature' => 20.0,
            'reading_timestamp' => $recentTimestamp->format('Y-m-d H:i:s')
        ]);

        // Act - Intentar eliminar lecturas anteriores al 1 de mayo
        $cutoffDate = new DateTime('2025-05-01 00:00:00');
        $deletedCount = $this->repository->deleteOldReadings($this->tank->id, $cutoffDate);

        // Assert
        $this->assertEquals(0, $deletedCount); // No se debería haber eliminado ninguna lectura

        // Verificar que la lectura sigue ahí
        $remainingReadings = EloquentTankReading::where('tank_id', $this->tank->id)->get();
        $this->assertCount(1, $remainingReadings);
    }
}
