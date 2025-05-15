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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RegisterTankReadingUseCaseTest extends TestCase
{
    private TankRepositoryInterface|MockObject $tankRepository;
    private TankReadingRepositoryInterface|MockObject $tankReadingRepository;
    private VolumeCalculatorService|MockObject $volumeCalculator;
    private RegisterTankReadingUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear mocks de las dependencias
        $this->tankRepository = $this->createMock(TankRepositoryInterface::class);
        $this->tankReadingRepository = $this->createMock(TankReadingRepositoryInterface::class);
        $this->volumeCalculator = $this->createMock(VolumeCalculatorService::class);
        
        // Instanciar el caso de uso con las dependencias mockeadas
        $this->useCase = new RegisterTankReadingUseCase(
            $this->tankRepository,
            $this->tankReadingRepository,
            $this->volumeCalculator
        );
    }

    public function test_register_tank_reading_successfully(): void
    {
        // Preparar un DTO con los datos de entrada
        $readingTimestamp = new DateTime();
        $dto = new CreateTankReadingDTO(
            1, // tank_id
            75.5, // liquid_level
            $readingTimestamp,
            22.3, // temperature
            ['raw' => 'data']
        );

        // Crear un tanque de prueba
        $tank = new Tank(
            1,
            'Tanque 1',
            'SN123',
            1000, // capacity
            100, // height
            'Ubicación',
            50, // diameter
            true
        );

        // Configurar el comportamiento esperado de los mocks
        $this->tankRepository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($tank);
        
        $this->volumeCalculator->expects($this->once())
            ->method('calculateVolume')
            ->with($tank, 75.5)
            ->willReturn(750.0);
        
        // Crear una lectura de prueba que simula la que se guardará
        $expectedReading = new TankReading(
            1, // id
            1, // tank_id
            75.5, // liquid_level
            750.0, // volume
            75.0, // percentage
            $readingTimestamp,
            22.3, // temperature
            ['raw' => 'data']
        );
        
        // El repositorio debería guardar y devolver la lectura
        $this->tankReadingRepository->expects($this->once())
            ->method('save')
            ->willReturn($expectedReading);

        // Ejecutar el caso de uso
        $result = $this->useCase->execute($dto);
        
        // Verificar el resultado
        $this->assertInstanceOf(TankReading::class, $result);
        $this->assertEquals(1, $result->getId());
        $this->assertEquals(1, $result->getTankId());
        $this->assertEquals(75.5, $result->getLiquidLevel());
        $this->assertEquals(750.0, $result->getVolume());
        $this->assertEquals(75.0, $result->getPercentage());
        $this->assertEquals($readingTimestamp, $result->getReadingTimestamp());
        $this->assertEquals(22.3, $result->getTemperature());
        $this->assertEquals(['raw' => 'data'], $result->getRawData());
    }

    public function test_throws_exception_when_tank_not_found(): void
    {
        // Preparar un DTO con los datos de entrada
        $dto = new CreateTankReadingDTO(
            999, // tank_id inexistente
            75.5, // liquid_level
            new DateTime(),
            22.3, // temperature
            ['raw' => 'data']
        );

        // El tank_id no existe
        $this->tankRepository->expects($this->once())
            ->method('findById')
            ->with(999)
            ->willReturn(null);
        
        // No deberían llamarse estos métodos
        $this->volumeCalculator->expects($this->never())->method('calculateVolume');
        $this->tankReadingRepository->expects($this->never())->method('save');

        // Verificar que se lanza la excepción esperada
        $this->expectException(TankNotFoundException::class);
        $this->expectExceptionMessage('Tanque con ID 999 no encontrado');
        
        // Ejecutar el caso de uso
        $this->useCase->execute($dto);
    }
}