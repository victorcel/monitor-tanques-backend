<?php

namespace Tests\Unit\Application\UseCases;

use App\Application\DTOs\CreateTankReadingDTO;
use App\Application\UseCases\RegisterTankReadingUseCase;
use App\Domain\Exceptions\TankNotFoundException;
use App\Domain\Models\Tank;
use App\Domain\Models\TankReading;
use App\Domain\Repositories\TankReadingRepositoryInterface;
use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Services\VolumeCalculatorService;
use DateTime;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class RegisterTankReadingUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_registers_reading_successfully(): void
    {
        // Arrange
        $tankId = 1;
        $liquidLevel = 150.0;
        $temperature = 23.5;
        $readingTimestamp = new DateTime('2025-05-15 10:00:00');
        $rawData = ['sensor' => 'ultrasonic', 'battery' => '90%'];
        
        $calculatedVolume = 750.0;
        $capacity = 1000.0;
        $calculatedPercentage = 75.0;
        
        $tank = new Tank(
            $tankId, 
            'Tanque 1', 
            'SN-001', 
            $capacity, 
            200.0, 
            'Ubicación 1', 
            50.0, 
            true, 
            new DateTime(), 
            new DateTime()
        );
        
        $savedReading = new TankReading(
            1, 
            $tankId, 
            $liquidLevel, 
            $calculatedVolume, 
            $calculatedPercentage, 
            $readingTimestamp, 
            $temperature, 
            $rawData, 
            new DateTime(), 
            new DateTime()
        );
        
        $dto = new CreateTankReadingDTO(
            $tankId,
            $liquidLevel,
            $readingTimestamp,
            $temperature,
            $rawData
        );
        
        $tankRepository = $this->mock(TankRepositoryInterface::class, function (MockInterface $mock) use ($tankId, $tank) {
            $mock->shouldReceive('findById')
                ->once()
                ->with($tankId)
                ->andReturn($tank);
        });
        
        $tankReadingRepository = $this->mock(TankReadingRepositoryInterface::class, function (MockInterface $mock) use ($savedReading) {
            $mock->shouldReceive('save')
                ->once()
                ->with(Mockery::type(TankReading::class))
                ->andReturn($savedReading);
        });
        
        $volumeCalculator = $this->mock(VolumeCalculatorService::class, function (MockInterface $mock) use ($tank, $liquidLevel, $calculatedVolume) {
            $mock->shouldReceive('calculateVolume')
                ->once()
                ->with($tank, $liquidLevel)
                ->andReturn($calculatedVolume);
        });
        
        $useCase = new RegisterTankReadingUseCase(
            $tankRepository,
            $tankReadingRepository,
            $volumeCalculator
        );

        // Act
        $result = $useCase->execute($dto);

        // Assert
        $this->assertInstanceOf(TankReading::class, $result);
        $this->assertEquals($tankId, $result->getTankId());
        $this->assertEquals($liquidLevel, $result->getLiquidLevel());
        $this->assertEquals($calculatedVolume, $result->getVolume());
        $this->assertEquals($calculatedPercentage, $result->getPercentage());
        $this->assertEquals($temperature, $result->getTemperature());
        $this->assertEquals($rawData, $result->getRawData());
        $this->assertEquals($readingTimestamp, $result->getReadingTimestamp());
    }

    public function test_execute_throws_exception_when_tank_not_found(): void
    {
        // Arrange
        $tankId = 999;
        $liquidLevel = 150.0;
        $readingTimestamp = new DateTime('2025-05-15 10:00:00');
        $temperature = 23.5;
        $rawData = ['sensor' => 'ultrasonic'];
        
        $dto = new CreateTankReadingDTO(
            $tankId,
            $liquidLevel,
            $readingTimestamp,
            $temperature,
            $rawData
        );
        
        $tankRepository = $this->mock(TankRepositoryInterface::class, function (MockInterface $mock) use ($tankId) {
            $mock->shouldReceive('findById')
                ->once()
                ->with($tankId)
                ->andReturnNull();
        });
        
        $tankReadingRepository = $this->mock(TankReadingRepositoryInterface::class);
        $volumeCalculator = $this->mock(VolumeCalculatorService::class);
        
        $useCase = new RegisterTankReadingUseCase(
            $tankRepository,
            $tankReadingRepository,
            $volumeCalculator
        );

        // Act & Assert
        $this->expectException(TankNotFoundException::class);
        $this->expectExceptionMessage("Tanque con ID {$tankId} no encontrado");
        
        $useCase->execute($dto);
    }

    public function test_calculate_percentage_handles_zero_capacity(): void
    {
        // Arrange
        $tankId = 1;
        $liquidLevel = 150.0;
        $readingTimestamp = new DateTime('2025-05-15 10:00:00');
        $temperature = 23.5;
        $rawData = null;
        
        $calculatedVolume = 750.0;
        $capacity = 0.0;  // Capacidad cero para probar división por cero
        
        $tank = new Tank(
            $tankId, 
            'Tanque Zero', 
            'SN-ZERO', 
            $capacity, 
            200.0, 
            'Ubicación Zero', 
            50.0, 
            true, 
            new DateTime(), 
            new DateTime()
        );
        
        $savedReading = new TankReading(
            1, 
            $tankId, 
            $liquidLevel, 
            $calculatedVolume, 
            0.0, // Esperamos 0% porque la capacidad es 0
            $readingTimestamp, 
            $temperature, 
            $rawData, 
            new DateTime(), 
            new DateTime()
        );
        
        $dto = new CreateTankReadingDTO(
            $tankId,
            $liquidLevel,
            $readingTimestamp,
            $temperature,
            $rawData
        );
        
        $tankRepository = $this->mock(TankRepositoryInterface::class, function (MockInterface $mock) use ($tankId, $tank) {
            $mock->shouldReceive('findById')
                ->once()
                ->with($tankId)
                ->andReturn($tank);
        });
        
        $tankReadingRepository = $this->mock(TankReadingRepositoryInterface::class, function (MockInterface $mock) use ($savedReading) {
            $mock->shouldReceive('save')
                ->once()
                ->with(Mockery::on(function($reading) {
                    return $reading->getPercentage() === 0.0;
                }))
                ->andReturn($savedReading);
        });
        
        $volumeCalculator = $this->mock(VolumeCalculatorService::class, function (MockInterface $mock) use ($tank, $liquidLevel, $calculatedVolume) {
            $mock->shouldReceive('calculateVolume')
                ->once()
                ->with($tank, $liquidLevel)
                ->andReturn($calculatedVolume);
        });
        
        $useCase = new RegisterTankReadingUseCase(
            $tankRepository,
            $tankReadingRepository,
            $volumeCalculator
        );

        // Act
        $result = $useCase->execute($dto);

        // Assert
        $this->assertInstanceOf(TankReading::class, $result);
        $this->assertEquals(0.0, $result->getPercentage());
    }

    public function test_calculate_percentage_limits_to_100_percent_max(): void
    {
        // Arrange
        $tankId = 1;
        $liquidLevel = 300.0;  // Nivel más alto de lo normal
        $readingTimestamp = new DateTime('2025-05-15 10:00:00');
        $temperature = 23.5;
        $rawData = null;
        
        $calculatedVolume = 1500.0;  // Volumen que excede la capacidad
        $capacity = 1000.0;
        
        $tank = new Tank(
            $tankId, 
            'Tanque Overflow', 
            'SN-OVERFLOW', 
            $capacity, 
            200.0, 
            'Ubicación Overflow', 
            50.0, 
            true, 
            new DateTime(), 
            new DateTime()
        );
        
        $savedReading = new TankReading(
            1, 
            $tankId, 
            $liquidLevel, 
            $calculatedVolume, 
            100.0, // Esperamos 100% máximo
            $readingTimestamp, 
            $temperature, 
            $rawData, 
            new DateTime(), 
            new DateTime()
        );
        
        $dto = new CreateTankReadingDTO(
            $tankId,
            $liquidLevel,
            $readingTimestamp,
            $temperature,
            $rawData
        );
        
        $tankRepository = $this->mock(TankRepositoryInterface::class, function (MockInterface $mock) use ($tankId, $tank) {
            $mock->shouldReceive('findById')
                ->once()
                ->with($tankId)
                ->andReturn($tank);
        });
        
        $tankReadingRepository = $this->mock(TankReadingRepositoryInterface::class, function (MockInterface $mock) use ($savedReading) {
            $mock->shouldReceive('save')
                ->once()
                ->with(Mockery::on(function($reading) {
                    return $reading->getPercentage() === 100.0;
                }))
                ->andReturn($savedReading);
        });
        
        $volumeCalculator = $this->mock(VolumeCalculatorService::class, function (MockInterface $mock) use ($tank, $liquidLevel, $calculatedVolume) {
            $mock->shouldReceive('calculateVolume')
                ->once()
                ->with($tank, $liquidLevel)
                ->andReturn($calculatedVolume);
        });
        
        $useCase = new RegisterTankReadingUseCase(
            $tankRepository,
            $tankReadingRepository,
            $volumeCalculator
        );

        // Act
        $result = $useCase->execute($dto);

        // Assert
        $this->assertInstanceOf(TankReading::class, $result);
        $this->assertEquals(100.0, $result->getPercentage());
    }
}