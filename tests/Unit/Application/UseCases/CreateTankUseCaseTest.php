<?php

namespace Tests\Unit\Application\UseCases;

use App\Application\DTOs\CreateTankDTO;
use App\Application\UseCases\CreateTankUseCase;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use DateTime;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateTankUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_creates_new_tank_when_serial_number_is_unique(): void
    {
        // Arrange
        $dto = new CreateTankDTO(
            'Test Tank',
            'SN-TEST-001',
            1000.0,
            200.0,
            'Test Location',
            50.0
        );
        
        $expectedTank = new Tank(
            1, // ID asignado por el repositorio
            $dto->getName(),
            $dto->getSerialNumber(),
            $dto->getCapacity(),
            $dto->getHeight(),
            $dto->getLocation(),
            $dto->getDiameter(),
            true, // Activo por defecto
            new DateTime(),
            new DateTime()
        );

        // Mock del repositorio
        $tankRepository = $this->mock(TankRepositoryInterface::class, function (MockInterface $mock) use ($dto, $expectedTank) {
            // Simula que no existe ningún tanque con ese número de serie
            $mock->shouldReceive('findBySerialNumber')
                ->once()
                ->with($dto->getSerialNumber())
                ->andReturn(null);
            
            // Simula guardar el tanque y devolver uno con ID asignado
            $mock->shouldReceive('save')
                ->once()
                ->andReturn($expectedTank);
        });

        $useCase = new CreateTankUseCase($tankRepository);

        // Act
        $result = $useCase->execute($dto);

        // Assert
        $this->assertInstanceOf(Tank::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals($dto->getName(), $result->getName());
        $this->assertEquals($dto->getSerialNumber(), $result->getSerialNumber());
        $this->assertEquals($dto->getCapacity(), $result->getCapacity());
        $this->assertEquals($dto->getHeight(), $result->getHeight());
        $this->assertEquals($dto->getLocation(), $result->getLocation());
        $this->assertEquals($dto->getDiameter(), $result->getDiameter());
        $this->assertTrue($result->isActive());
    }

    public function test_execute_throws_exception_when_serial_number_already_exists(): void
    {
        // Arrange
        $dto = new CreateTankDTO(
            'Duplicate Tank',
            'SN-DUPLICATE',
            2000.0,
            300.0,
            'Another Location',
            70.0
        );
        
        $existingTank = new Tank(
            5,
            'Existing Tank',
            'SN-DUPLICATE', // Mismo número de serie
            1500.0,
            250.0,
            'Existing Location',
            60.0,
            true,
            new DateTime(),
            new DateTime()
        );

        // Mock del repositorio para simular que ya existe un tanque con ese número de serie
        $tankRepository = $this->mock(TankRepositoryInterface::class, function (MockInterface $mock) use ($dto, $existingTank) {
            $mock->shouldReceive('findBySerialNumber')
                ->once()
                ->with($dto->getSerialNumber())
                ->andReturn($existingTank);
            
            // No debería llamarse al método save
            $mock->shouldNotReceive('save');
        });

        $useCase = new CreateTankUseCase($tankRepository);

        // Assert & Act
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Ya existe un tanque con el número de serie {$dto->getSerialNumber()}");
        
        $useCase->execute($dto);
    }
}