<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Application\DTOs\CreateTankDTO;
use App\Application\UseCases\CreateTankUseCase;
use App\Application\UseCases\DeleteTankUseCase;
use App\Application\UseCases\GetTankUseCase;
use App\Application\UseCases\ListTanksUseCase;
use App\Application\UseCases\UpdateTankUseCase;
use App\Domain\Exceptions\TankNotFoundException;
use App\Domain\Models\Tank;
use App\Http\Controllers\Api\TankController;
use App\Http\Requests\TankStoreRequest;
use App\Http\Requests\TankUpdateRequest;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TankControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_list_of_tanks(): void
    {
        // Arrange
        $tank1 = new Tank(1, 'Tanque 1', 'SN-001', 1000.0, 200.0, 'Ubicación 1', 50.0, true, new DateTime(), new DateTime());
        $tank2 = new Tank(2, 'Tanque 2', 'SN-002', 2000.0, 300.0, 'Ubicación 2', 75.0, true, new DateTime(), new DateTime());
        $tanks = [$tank1, $tank2];

        $listTanksUseCase = $this->mock(ListTanksUseCase::class, function (MockInterface $mock) use ($tanks) {
            $mock->shouldReceive('execute')
                ->once()
                ->andReturn($tanks);
        });

        $controller = new TankController();

        // Act
        $response = $controller->index($listTanksUseCase);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content['data']);
        $this->assertEquals('Tanque 1', $content['data'][0]['name']);
        $this->assertEquals('Tanque 2', $content['data'][1]['name']);
    }

    public function test_store_creates_new_tank(): void
    {
        // Arrange
        $tankData = [
            'name' => 'New Tank',
            'serial_number' => 'SN-NEW',
            'capacity' => 1500.0,
            'height' => 250.0,
            'location' => 'New Location',
            'diameter' => 60.0
        ];

        $createdTank = new Tank(
            1, 
            $tankData['name'], 
            $tankData['serial_number'], 
            $tankData['capacity'], 
            $tankData['height'], 
            $tankData['location'], 
            $tankData['diameter'], 
            true, 
            new DateTime(), 
            new DateTime()
        );

        $request = $this->mock(TankStoreRequest::class, function (MockInterface $mock) use ($tankData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($tankData);
        });

        $createTankUseCase = $this->mock(CreateTankUseCase::class, function (MockInterface $mock) use ($createdTank) {
            $mock->shouldReceive('execute')
                ->once()
                ->with(Mockery::type(CreateTankDTO::class))
                ->andReturn($createdTank);
        });

        $controller = new TankController();

        // Act
        $response = $controller->store($request, $createTankUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Tanque creado con éxito', $content['message']);
        $this->assertEquals('New Tank', $content['data']['name']);
    }

    public function test_show_returns_tank_when_found(): void
    {
        // Arrange
        $tankId = 1;
        $tank = new Tank($tankId, 'Tanque Test', 'SN-TEST', 1000.0, 200.0, 'Test Location', 50.0, true, new DateTime(), new DateTime());

        $getTankUseCase = $this->mock(GetTankUseCase::class, function (MockInterface $mock) use ($tankId, $tank) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andReturn($tank);
        });

        $controller = new TankController();

        // Act
        $response = $controller->show($tankId, $getTankUseCase);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Tanque Test', $content['data']['name']);
        $this->assertEquals('SN-TEST', $content['data']['serial_number']);
    }

    public function test_show_returns_not_found_when_tank_not_found(): void
    {
        // Arrange
        $tankId = 999;
        $errorMessage = "Tanque no encontrado con ID: {$tankId}";

        $getTankUseCase = $this->mock(GetTankUseCase::class, function (MockInterface $mock) use ($tankId, $errorMessage) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andThrow(new TankNotFoundException($errorMessage));
        });

        $controller = new TankController();

        // Act
        $response = $controller->show($tankId, $getTankUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($errorMessage, $content['message']);
    }

    public function test_update_modifies_tank_when_found(): void
    {
        // Arrange
        $tankId = 1;
        $updateData = [
            'name' => 'Updated Tank',
            'location' => 'Updated Location',
            'capacity' => 2000.0
        ];

        $updatedTank = new Tank(
            $tankId, 
            $updateData['name'], 
            'SN-TEST', 
            $updateData['capacity'], 
            200.0, 
            $updateData['location'], 
            50.0, 
            true, 
            new DateTime(), 
            new DateTime()
        );

        $request = $this->mock(TankUpdateRequest::class, function (MockInterface $mock) use ($updateData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($updateData);
        });

        $updateTankUseCase = $this->mock(UpdateTankUseCase::class, function (MockInterface $mock) use ($tankId, $updateData, $updatedTank) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId, $updateData)
                ->andReturn($updatedTank);
        });

        $controller = new TankController();

        // Act
        $response = $controller->update($tankId, $request, $updateTankUseCase);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Tanque actualizado con éxito', $content['message']);
        $this->assertEquals('Updated Tank', $content['data']['name']);
        $this->assertEquals('Updated Location', $content['data']['location']);
    }

    public function test_update_returns_not_found_when_tank_not_found(): void
    {
        // Arrange
        $tankId = 999;
        $updateData = ['name' => 'Updated Tank'];
        $errorMessage = "Tanque no encontrado con ID: {$tankId}";

        $request = $this->mock(TankUpdateRequest::class, function (MockInterface $mock) use ($updateData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($updateData);
        });

        $updateTankUseCase = $this->mock(UpdateTankUseCase::class, function (MockInterface $mock) use ($tankId, $updateData, $errorMessage) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId, $updateData)
                ->andThrow(new TankNotFoundException($errorMessage));
        });

        $controller = new TankController();

        // Act
        $response = $controller->update($tankId, $request, $updateTankUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($errorMessage, $content['message']);
    }

    public function test_destroy_deletes_tank_when_found(): void
    {
        // Arrange
        $tankId = 1;

        $deleteTankUseCase = $this->mock(DeleteTankUseCase::class, function (MockInterface $mock) use ($tankId) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andReturn(true);
        });

        $controller = new TankController();

        // Act
        $response = $controller->destroy($tankId, $deleteTankUseCase);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Tanque eliminado con éxito', $content['message']);
    }

    public function test_destroy_returns_not_found_when_tank_not_found(): void
    {
        // Arrange
        $tankId = 999;
        $errorMessage = "Tanque no encontrado con ID: {$tankId}";

        $deleteTankUseCase = $this->mock(DeleteTankUseCase::class, function (MockInterface $mock) use ($tankId, $errorMessage) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andThrow(new TankNotFoundException($errorMessage));
        });

        $controller = new TankController();

        // Act
        $response = $controller->destroy($tankId, $deleteTankUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($errorMessage, $content['message']);
    }
}