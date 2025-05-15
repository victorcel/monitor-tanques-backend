<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\TankModel;
use App\Infrastructure\Persistence\Eloquent\TankReadingModel;
use DateTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TankReadingControllerTest extends TestCase
{
    // Quitamos RefreshDatabase ya que ahora manejamos las tablas en TestCase.php

    private $tank;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un tanque para usarlo en las pruebas
        $this->tank = TankModel::create([
            'name' => 'Tanque de Prueba',
            'serial_number' => 'SN-TEST-001',
            'capacity' => 1000.0,
            'height' => 100.0,
            'location' => 'Laboratorio',
            'diameter' => 50.0,
            'is_active' => true
        ]);
    }

    public function test_index_returns_all_readings_for_tank(): void
    {
        // Crear algunas lecturas para el tanque
        TankReadingModel::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 50.5,
            'volume' => 500.5,
            'percentage' => 50.05,
            'reading_timestamp' => now()->subHours(2),
            'temperature' => 22.5,
            'raw_data' => json_encode(['sensor' => 'A1', 'value' => '50.5'])
        ]);

        TankReadingModel::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 60.0,
            'volume' => 600.0,
            'percentage' => 60.0,
            'reading_timestamp' => now()->subHour(),
            'temperature' => 23.0,
            'raw_data' => json_encode(['sensor' => 'A1', 'value' => '60.0'])
        ]);

        // Realizar petición GET a la API
        $response = $this->getJson("/api/tanks/{$this->tank->id}/readings");

        // Verificar respuesta correcta
        $response->assertStatus(200)
                ->assertJsonCount(2, 'data')
                ->assertJsonPath('data.0.liquid_level', 60)
                ->assertJsonPath('data.1.liquid_level', 50.5);
    }

    public function test_index_returns_404_for_nonexistent_tank(): void
    {
        // ID que no existe
        $nonExistentId = 999;

        // Realizar petición GET a la API
        $response = $this->getJson("/api/tanks/{$nonExistentId}/readings");

        // Verificar respuesta 404
        $response->assertStatus(404);
    }

    public function test_store_creates_new_reading(): void
    {
        $readingData = [
            'tank_id' => $this->tank->id,
            'liquid_level' => 75.5,
            'temperature' => 24.0,
            'reading_timestamp' => now()->toISOString(),
            'raw_data' => ['sensor' => 'A1', 'value' => '75.5']
        ];

        // Realizar petición POST a la API
        $response = $this->postJson('/api/tank-readings', $readingData);

        // Verificar respuesta correcta
        $response->assertStatus(201)
                ->assertJsonPath('data.liquid_level', 75.5)
                ->assertJson([
                    'data' => [
                        'temperature' => 24
                    ]
                ]);

        // Verificar que se ha guardado en la base de datos
        $this->assertDatabaseHas('tank_readings', [
            'tank_id' => $this->tank->id,
            'liquid_level' => 75.5,
            'temperature' => 24.0
        ]);
    }

    public function test_store_validates_input_data(): void
    {
        // Intentar crear lectura con datos incorrectos/faltantes
        $response = $this->postJson('/api/tank-readings', [
            'tank_id' => $this->tank->id,
            // Falta liquid_level que es obligatorio
            'temperature' => 'no-es-un-numero' // No es numérico
        ]);

        // Verificar error de validación
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['liquid_level', 'temperature']);
    }

    public function test_store_returns_404_for_nonexistent_tank(): void
    {
        $readingData = [
            'tank_id' => 999, // ID que no existe
            'liquid_level' => 75.5,
            'temperature' => 24.0
        ];

        // Realizar petición POST a la API
        $response = $this->postJson('/api/tank-readings', $readingData);

        // Verificar respuesta de error
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['tank_id']);
    }

    public function test_latest_returns_most_recent_reading(): void
    {
        // Crear dos lecturas para el tanque con timestamps diferentes
        TankReadingModel::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 50.5,
            'volume' => 500.5,
            'percentage' => 50.05,
            'reading_timestamp' => now()->subHours(2),
            'temperature' => 22.5,
            'raw_data' => json_encode(['sensor' => 'A1', 'value' => '50.5'])
        ]);

        $latestReading = TankReadingModel::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 60.0,
            'volume' => 600.0,
            'percentage' => 60.0,
            'reading_timestamp' => now()->subHour(),
            'temperature' => 23.0,
            'raw_data' => json_encode(['sensor' => 'A1', 'value' => '60.0'])
        ]);

        // Realizar petición GET a la API
        $response = $this->getJson("/api/tanks/{$this->tank->id}/readings/latest");

        // Verificar que devuelve la lectura más reciente
        $response->assertStatus(200)
                ->assertJsonPath('data.id', $latestReading->id)
                ->assertJsonPath('data.liquid_level', 60);
    }

    public function test_latest_returns_404_when_no_readings(): void
    {
        // No creamos lecturas para el tanque

        // Realizar petición GET a la API
        $response = $this->getJson("/api/tanks/{$this->tank->id}/readings/latest");

        // Verificar respuesta 404
        $response->assertStatus(404)
                ->assertJsonPath('message', 'No hay lecturas disponibles para este tanque');
    }

    public function test_latest_returns_404_for_nonexistent_tank(): void
    {
        // ID que no existe
        $nonExistentId = 999;

        // Realizar petición GET a la API
        $response = $this->getJson("/api/tanks/{$nonExistentId}/readings/latest");

        // Verificar respuesta 404
        $response->assertStatus(404);
    }

    public function test_get_by_date_range_returns_readings_in_range(): void
    {
        // Crear lecturas con diferentes fechas
        TankReadingModel::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 50.5,
            'volume' => 500.5,
            'percentage' => 50.05,
            'reading_timestamp' => now()->subDays(5),
            'temperature' => 22.5,
            'raw_data' => json_encode(['sensor' => 'A1', 'value' => '50.5'])
        ]);

        TankReadingModel::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 60.0,
            'volume' => 600.0,
            'percentage' => 60.0,
            'reading_timestamp' => now()->subDays(3),
            'temperature' => 23.0,
            'raw_data' => json_encode(['sensor' => 'A1', 'value' => '60.0'])
        ]);

        TankReadingModel::create([
            'tank_id' => $this->tank->id,
            'liquid_level' => 70.0,
            'volume' => 700.0,
            'percentage' => 70.0,
            'reading_timestamp' => now()->subDay(),
            'temperature' => 23.5,
            'raw_data' => json_encode(['sensor' => 'A1', 'value' => '70.0'])
        ]);

        // Rango de fechas que debería incluir solo las dos últimas lecturas
        $dateRange = [
            'start_date' => now()->subDays(4)->toISOString(),
            'end_date' => now()->toISOString()
        ];

        // Realizar petición GET a la API con rango de fechas
        $response = $this->getJson("/api/tanks/{$this->tank->id}/readings/date-range?start_date={$dateRange['start_date']}&end_date={$dateRange['end_date']}");

        // Verificar respuesta correcta
        $response->assertStatus(200)
                ->assertJsonCount(2, 'data');

        // Las lecturas deben estar en el rango de fechas especificado
        $data = $response->json('data');
        $this->assertEquals(70.0, $data[0]['liquid_level']);
        $this->assertEquals(60.0, $data[1]['liquid_level']);
    }

    public function test_get_by_date_range_validates_date_format(): void
    {
        // Fechas en formato incorrecto
        $dateRange = [
            'start_date' => 'no-es-una-fecha',
            'end_date' => 'tampoco-es-una-fecha'
        ];

        // Realizar petición GET a la API con fechas incorrectas
        $response = $this->getJson("/api/tanks/{$this->tank->id}/readings/date-range?start_date={$dateRange['start_date']}&end_date={$dateRange['end_date']}");

        // Verificar error de validación
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['start_date', 'end_date']);
    }

    public function test_get_by_date_range_validates_start_before_end(): void
    {
        // Fecha de inicio posterior a la fecha final
        $dateRange = [
            'start_date' => now()->toISOString(),
            'end_date' => now()->subDays(5)->toISOString()
        ];

        // Realizar petición GET a la API con rango de fechas incorrecto
        $response = $this->getJson("/api/tanks/{$this->tank->id}/readings/date-range?start_date={$dateRange['start_date']}&end_date={$dateRange['end_date']}");

        // Verificar error de validación
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['end_date']);
    }

    public function test_store_batch_creates_multiple_readings(): void
    {
        $batchData = [
            'readings' => [
                [
                    'tank_id' => $this->tank->id,
                    'liquid_level' => 80.5,
                    'temperature' => 25.0,
                    'reading_timestamp' => now()->subHours(2)->toISOString()
                ],
                [
                    'tank_id' => $this->tank->id,
                    'liquid_level' => 85.0,
                    'temperature' => 25.5,
                    'reading_timestamp' => now()->subHour()->toISOString()
                ]
            ]
        ];

        // Realizar petición POST a la API
        $response = $this->postJson('/api/tank-readings/batch', $batchData);

        // Verificar respuesta correcta
        $response->assertStatus(201)
                ->assertJsonCount(2, 'data')
                ->assertJsonPath('data.0.liquid_level', 80.5)
                ->assertJson([
                    'data' => [
                        '1' => [
                            'liquid_level' => 85
                        ]
                    ]
                ]);

        // Verificar que se han guardado en la base de datos
        $this->assertDatabaseHas('tank_readings', [
            'tank_id' => $this->tank->id,
            'liquid_level' => 80.5,
            'temperature' => 25.0
        ]);

        $this->assertDatabaseHas('tank_readings', [
            'tank_id' => $this->tank->id,
            'liquid_level' => 85.0,
            'temperature' => 25.5
        ]);
    }

    public function test_store_batch_validates_input_data(): void
    {
        $batchData = [
            'readings' => [
                [
                    'tank_id' => $this->tank->id,
                    'liquid_level' => 80.5,
                    'temperature' => 25.0
                ],
                [
                    'tank_id' => $this->tank->id,
                    // Falta liquid_level que es obligatorio
                    'temperature' => 'no-es-un-numero' // No es numérico
                ]
            ]
        ];

        // Realizar petición POST a la API
        $response = $this->postJson('/api/tank-readings/batch', $batchData);

        // Verificar error de validación
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['readings.1.liquid_level', 'readings.1.temperature']);
    }
}
