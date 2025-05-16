<?php

namespace Tests\Unit\Http\Controllers\Api;

use App\Application\DTOs\CreateTankReadingDTO;
use App\Application\UseCases\GetLatestTankReadingUseCase;
use App\Application\UseCases\ListTankReadingsUseCase;
use App\Application\UseCases\RegisterTankReadingUseCase;
use App\Domain\Exceptions\TankNotFoundException;
use App\Domain\Models\TankReading;
use App\Http\Controllers\Api\TankReadingController;
use App\Http\Requests\TankReadingBatchRequest;
use App\Http\Requests\TankReadingDateRangeRequest;
use App\Http\Requests\TankReadingStoreRequest;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TankReadingControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_index_returns_list_of_readings(): void
    {
        // Arrange
        $tankId = 1;
        $reading1 = new TankReading(1, $tankId, 150.0, 750.0, 75.0, new DateTime('2025-05-15 10:00:00'), 22.5, ['sensor' => 'ultrasonic'], new DateTime(), new DateTime());
        $reading2 = new TankReading(2, $tankId, 140.0, 700.0, 70.0, new DateTime('2025-05-15 11:00:00'), 22.0, ['sensor' => 'ultrasonic'], new DateTime(), new DateTime());
        $readings = [$reading1, $reading2];

        $listTankReadingsUseCase = $this->mock(ListTankReadingsUseCase::class, function (MockInterface $mock) use ($tankId, $readings) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andReturn($readings);
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->index($tankId, $listTankReadingsUseCase);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content['data']);
        $this->assertEquals(150.0, $content['data'][0]['liquid_level']);
        $this->assertEquals(140.0, $content['data'][1]['liquid_level']);
    }

    public function test_index_returns_not_found_when_tank_not_found(): void
    {
        // Arrange
        $tankId = 999;
        $errorMessage = "Tanque no encontrado con ID: {$tankId}";

        $listTankReadingsUseCase = $this->mock(ListTankReadingsUseCase::class, function (MockInterface $mock) use ($tankId, $errorMessage) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andThrow(new TankNotFoundException($errorMessage));
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->index($tankId, $listTankReadingsUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($errorMessage, $content['message']);
    }

    public function test_store_registers_new_reading(): void
    {
        // Arrange
        $readingData = [
            'tank_id' => 1,
            'liquid_level' => 160.0,
            'volume' => 800.0,
            'percentage' => 80.0,
            'temperature' => 23.0,
            'reading_timestamp' => '2025-05-15 12:00:00',
            'raw_data' => ['sensor' => 'ultrasonic', 'battery' => '90%']
        ];

        $createdReading = new TankReading(
            1, 
            $readingData['tank_id'], 
            $readingData['liquid_level'], 
            $readingData['volume'], 
            $readingData['percentage'], 
            new DateTime($readingData['reading_timestamp']), 
            $readingData['temperature'], 
            $readingData['raw_data'], 
            new DateTime(), 
            new DateTime()
        );

        $request = $this->mock(TankReadingStoreRequest::class, function (MockInterface $mock) use ($readingData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($readingData);
        });

        $registerTankReadingUseCase = $this->mock(RegisterTankReadingUseCase::class, function (MockInterface $mock) use ($createdReading) {
            $mock->shouldReceive('execute')
                ->once()
                ->with(Mockery::type(CreateTankReadingDTO::class))
                ->andReturn($createdReading);
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->store($request, $registerTankReadingUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Lectura registrada con éxito', $content['message']);
        $this->assertEquals(160.0, $content['data']['liquid_level']);
        $this->assertEquals(80.0, $content['data']['percentage']);
    }

    public function test_store_returns_not_found_when_tank_not_found(): void
    {
        // Arrange
        $readingData = [
            'tank_id' => 999,
            'liquid_level' => 160.0,
            'volume' => 800.0,
            'percentage' => 80.0,
            'temperature' => 23.0,
            'reading_timestamp' => '2025-05-15 12:00:00'
        ];
        
        $errorMessage = "Tanque no encontrado con ID: 999";

        $request = $this->mock(TankReadingStoreRequest::class, function (MockInterface $mock) use ($readingData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($readingData);
        });

        $registerTankReadingUseCase = $this->mock(RegisterTankReadingUseCase::class, function (MockInterface $mock) use ($errorMessage) {
            $mock->shouldReceive('execute')
                ->once()
                ->with(Mockery::type(CreateTankReadingDTO::class))
                ->andThrow(new TankNotFoundException($errorMessage));
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->store($request, $registerTankReadingUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($errorMessage, $content['message']);
    }

    public function test_latest_returns_latest_reading_when_found(): void
    {
        // Arrange
        $tankId = 1;
        $latestReading = new TankReading(
            3, 
            $tankId, 
            170.0, 
            850.0, 
            85.0, 
            new DateTime('2025-05-15 13:00:00'), 
            23.5, 
            ['sensor' => 'ultrasonic'], 
            new DateTime(), 
            new DateTime()
        );

        $getLatestTankReadingUseCase = $this->mock(GetLatestTankReadingUseCase::class, function (MockInterface $mock) use ($tankId, $latestReading) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andReturn($latestReading);
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->latest($tankId, $getLatestTankReadingUseCase);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals(170.0, $content['data']['liquid_level']);
        $this->assertEquals(85.0, $content['data']['percentage']);
    }

    public function test_latest_returns_not_found_when_no_readings_available(): void
    {
        // Arrange
        $tankId = 1;

        $getLatestTankReadingUseCase = $this->mock(GetLatestTankReadingUseCase::class, function (MockInterface $mock) use ($tankId) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andReturn(null);
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->latest($tankId, $getLatestTankReadingUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('No hay lecturas disponibles para este tanque', $content['message']);
    }

    public function test_latest_returns_not_found_when_tank_not_found(): void
    {
        // Arrange
        $tankId = 999;
        $errorMessage = "Tanque no encontrado con ID: {$tankId}";

        $getLatestTankReadingUseCase = $this->mock(GetLatestTankReadingUseCase::class, function (MockInterface $mock) use ($tankId, $errorMessage) {
            $mock->shouldReceive('execute')
                ->once()
                ->with($tankId)
                ->andThrow(new TankNotFoundException($errorMessage));
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->latest($tankId, $getLatestTankReadingUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($errorMessage, $content['message']);
    }

    public function test_get_by_date_range_returns_readings_in_range(): void
    {
        // Arrange
        $tankId = 1;
        $dateRangeData = [
            'start_date' => '2025-05-14',
            'end_date' => '2025-05-16'
        ];
        
        $reading1 = new TankReading(1, $tankId, 150.0, 750.0, 75.0, new DateTime('2025-05-14 10:00:00'), 22.5, ['sensor' => 'ultrasonic'], new DateTime(), new DateTime());
        $reading2 = new TankReading(2, $tankId, 160.0, 800.0, 80.0, new DateTime('2025-05-15 10:00:00'), 23.0, ['sensor' => 'ultrasonic'], new DateTime(), new DateTime());
        $readings = [$reading1, $reading2];

        $request = $this->mock(TankReadingDateRangeRequest::class, function (MockInterface $mock) use ($dateRangeData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($dateRangeData);
        });

        $listTankReadingsUseCase = $this->mock(ListTankReadingsUseCase::class, function (MockInterface $mock) use ($tankId, $readings) {
            $mock->shouldReceive('executeWithDateRange')
                ->once()
                ->with(
                    $tankId,
                    Mockery::type(DateTime::class),
                    Mockery::type(DateTime::class)
                )
                ->andReturn($readings);
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->getByDateRange($tankId, $request, $listTankReadingsUseCase);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertCount(2, $content['data']);
        $this->assertEquals(150.0, $content['data'][0]['liquid_level']);
        $this->assertEquals(160.0, $content['data'][1]['liquid_level']);
    }

    public function test_get_by_date_range_returns_not_found_when_tank_not_found(): void
    {
        // Arrange
        $tankId = 999;
        $dateRangeData = [
            'start_date' => '2025-05-14',
            'end_date' => '2025-05-16'
        ];
        $errorMessage = "Tanque no encontrado con ID: {$tankId}";

        $request = $this->mock(TankReadingDateRangeRequest::class, function (MockInterface $mock) use ($dateRangeData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($dateRangeData);
        });

        $listTankReadingsUseCase = $this->mock(ListTankReadingsUseCase::class, function (MockInterface $mock) use ($tankId, $errorMessage) {
            $mock->shouldReceive('executeWithDateRange')
                ->once()
                ->with(
                    $tankId,
                    Mockery::type(DateTime::class),
                    Mockery::type(DateTime::class)
                )
                ->andThrow(new TankNotFoundException($errorMessage));
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->getByDateRange($tankId, $request, $listTankReadingsUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals($errorMessage, $content['message']);
    }

    public function test_store_batch_registers_multiple_readings(): void
    {
        // Arrange
        $batchData = [
            'readings' => [
                [
                    'tank_id' => 1,
                    'liquid_level' => 160.0,
                    'volume' => 800.0,
                    'percentage' => 80.0,
                    'temperature' => 23.0,
                    'reading_timestamp' => '2025-05-15 12:00:00'
                ],
                [
                    'tank_id' => 2,
                    'liquid_level' => 90.0,
                    'volume' => 450.0,
                    'percentage' => 45.0,
                    'temperature' => 21.0,
                    'reading_timestamp' => '2025-05-15 12:00:00'
                ]
            ]
        ];

        $reading1 = new TankReading(
            1, 
            $batchData['readings'][0]['tank_id'], 
            $batchData['readings'][0]['liquid_level'], 
            $batchData['readings'][0]['volume'], 
            $batchData['readings'][0]['percentage'], 
            new DateTime($batchData['readings'][0]['reading_timestamp']), 
            $batchData['readings'][0]['temperature'], 
            null, 
            new DateTime(), 
            new DateTime()
        );
        
        $reading2 = new TankReading(
            2, 
            $batchData['readings'][1]['tank_id'], 
            $batchData['readings'][1]['liquid_level'], 
            $batchData['readings'][1]['volume'], 
            $batchData['readings'][1]['percentage'], 
            new DateTime($batchData['readings'][1]['reading_timestamp']), 
            $batchData['readings'][1]['temperature'], 
            null, 
            new DateTime(), 
            new DateTime()
        );

        $request = $this->mock(TankReadingBatchRequest::class, function (MockInterface $mock) use ($batchData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($batchData);
        });

        $registerTankReadingUseCase = $this->mock(RegisterTankReadingUseCase::class, function (MockInterface $mock) use ($reading1, $reading2) {
            $mock->shouldReceive('execute')
                ->twice()
                ->with(Mockery::type(CreateTankReadingDTO::class))
                ->andReturnValues([$reading1, $reading2]);
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->storeBatch($request, $registerTankReadingUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('2 lecturas registradas con éxito', $content['message']);
        $this->assertCount(2, $content['data']);
        $this->assertEquals(160.0, $content['data'][0]['liquid_level']);
        $this->assertEquals(90.0, $content['data'][1]['liquid_level']);
        $this->assertEmpty($content['errors']);
    }

    public function test_store_batch_handles_errors_for_some_readings(): void
    {
        // Arrange
        $batchData = [
            'readings' => [
                [
                    'tank_id' => 1,
                    'liquid_level' => 160.0,
                    'volume' => 800.0,
                    'percentage' => 80.0,
                    'temperature' => 23.0,
                    'reading_timestamp' => '2025-05-15 12:00:00'
                ],
                [
                    'tank_id' => 999, // Tanque inválido
                    'liquid_level' => 90.0,
                    'volume' => 450.0,
                    'percentage' => 45.0,
                    'temperature' => 21.0,
                    'reading_timestamp' => '2025-05-15 12:00:00'
                ]
            ]
        ];

        $reading1 = new TankReading(
            1, 
            $batchData['readings'][0]['tank_id'], 
            $batchData['readings'][0]['liquid_level'], 
            $batchData['readings'][0]['volume'], 
            $batchData['readings'][0]['percentage'], 
            new DateTime($batchData['readings'][0]['reading_timestamp']), 
            $batchData['readings'][0]['temperature'], 
            null, 
            new DateTime(), 
            new DateTime()
        );
        
        $errorMessage = "Tanque no encontrado con ID: 999";

        $request = $this->mock(TankReadingBatchRequest::class, function (MockInterface $mock) use ($batchData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($batchData);
        });

        $registerTankReadingUseCase = $this->mock(RegisterTankReadingUseCase::class, function (MockInterface $mock) use ($reading1, $errorMessage) {
            $mock->shouldReceive('execute')
                ->once()
                ->with(Mockery::on(function ($dto) {
                    return $dto->getTankId() == 1;
                }))
                ->andReturn($reading1);
                
            $mock->shouldReceive('execute')
                ->once()
                ->with(Mockery::on(function ($dto) {
                    return $dto->getTankId() == 999;
                }))
                ->andThrow(new TankNotFoundException($errorMessage));
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->storeBatch($request, $registerTankReadingUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('1 lecturas registradas con éxito', $content['message']);
        $this->assertCount(1, $content['data']);
        $this->assertEquals(160.0, $content['data'][0]['liquid_level']);
        $this->assertCount(1, $content['errors']);
        $this->assertEquals(1, $content['errors'][0]['index']);
        $this->assertEquals($errorMessage, $content['errors'][0]['message']);
    }
    
    public function test_store_batch_returns_bad_request_when_all_readings_fail(): void
    {
        // Arrange
        $batchData = [
            'readings' => [
                [
                    'tank_id' => 998,
                    'liquid_level' => 160.0,
                    'volume' => 800.0,
                    'percentage' => 80.0,
                    'temperature' => 23.0,
                    'reading_timestamp' => '2025-05-15 12:00:00'
                ],
                [
                    'tank_id' => 999,
                    'liquid_level' => 90.0,
                    'volume' => 450.0,
                    'percentage' => 45.0,
                    'temperature' => 21.0,
                    'reading_timestamp' => '2025-05-15 12:00:00'
                ]
            ]
        ];
        
        $errorMessage1 = "Tanque no encontrado con ID: 998";
        $errorMessage2 = "Tanque no encontrado con ID: 999";

        $request = $this->mock(TankReadingBatchRequest::class, function (MockInterface $mock) use ($batchData) {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn($batchData);
        });

        $registerTankReadingUseCase = $this->mock(RegisterTankReadingUseCase::class, function (MockInterface $mock) use ($errorMessage1, $errorMessage2) {
            $mock->shouldReceive('execute')
                ->once()
                ->with(Mockery::on(function ($dto) {
                    return $dto->getTankId() == 998;
                }))
                ->andThrow(new TankNotFoundException($errorMessage1));
                
            $mock->shouldReceive('execute')
                ->once()
                ->with(Mockery::on(function ($dto) {
                    return $dto->getTankId() == 999;
                }))
                ->andThrow(new TankNotFoundException($errorMessage2));
        });

        $controller = new TankReadingController();

        // Act
        $response = $controller->storeBatch($request, $registerTankReadingUseCase);

        // Assert
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('0 lecturas registradas con éxito', $content['message']);
        $this->assertEmpty($content['data']);
        $this->assertCount(2, $content['errors']);
        $this->assertEquals($errorMessage1, $content['errors'][0]['message']);
        $this->assertEquals($errorMessage2, $content['errors'][1]['message']);
    }
}