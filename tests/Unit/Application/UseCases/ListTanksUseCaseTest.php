<?php

namespace Tests\Unit\Application\UseCases;

use App\Application\UseCases\ListTanksUseCase;
use App\Domain\Models\Tank;
use App\Domain\Repositories\TankRepositoryInterface;
use DateTime;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ListTanksUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_all_tanks(): void
    {
        // Arrange
        $tank1 = new Tank(1, 'Tanque 1', 'SN-001', 1000.0, 200.0, 'Ubicación 1', 50.0, true, new DateTime(), new DateTime());
        $tank2 = new Tank(2, 'Tanque 2', 'SN-002', 2000.0, 300.0, 'Ubicación 2', 75.0, true, new DateTime(), new DateTime());
        $tanks = [$tank1, $tank2];

        $tankRepository = $this->mock(TankRepositoryInterface::class, function (MockInterface $mock) use ($tanks) {
            $mock->shouldReceive('findAll')
                ->once()
                ->andReturn($tanks);
        });

        $useCase = new ListTanksUseCase($tankRepository);

        // Act
        $result = $useCase->execute();

        // Assert
        $this->assertSame($tanks, $result);
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Tank::class, $result[0]);
        $this->assertInstanceOf(Tank::class, $result[1]);
        $this->assertEquals('Tanque 1', $result[0]->getName());
        $this->assertEquals('Tanque 2', $result[1]->getName());
    }

    public function test_execute_returns_empty_array_when_no_tanks(): void
    {
        // Arrange
        $tankRepository = $this->mock(TankRepositoryInterface::class, function (MockInterface $mock) {
            $mock->shouldReceive('findAll')
                ->once()
                ->andReturn([]);
        });

        $useCase = new ListTanksUseCase($tankRepository);

        // Act
        $result = $useCase->execute();

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}