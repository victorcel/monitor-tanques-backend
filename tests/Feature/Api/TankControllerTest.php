<?php

namespace Tests\Feature\Api;

use App\Infrastructure\Persistence\Eloquent\TankModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TankControllerTest extends TestCase
{
    // Quitamos RefreshDatabase ya que ahora manejamos las tablas en TestCase.php


    public function test_index_returns_all_tanks(): void
    {
        // Crear algunos tanques de prueba
        TankModel::create([
            'name' => 'Tanque 1',
            'serial_number' => 'SN001',
            'capacity' => 1000.0,
            'height' => 100.0,
            'location' => 'Ubicación A',
            'diameter' => 50.0,
            'is_active' => true
        ]);

        TankModel::create([
            'name' => 'Tanque 2',
            'serial_number' => 'SN002',
            'capacity' => 2000.0,
            'height' => 150.0,
            'location' => 'Ubicación B',
            'diameter' => null,
            'is_active' => true
        ]);

        // Realizar petición GET a la API
        $response = $this->getJson('/api/tanks');

        // Verificar respuesta correcta
        $response->assertStatus(200)
                ->assertJsonCount(2, 'data')
                ->assertJsonPath('data.0.name', 'Tanque 1')
                ->assertJsonPath('data.1.name', 'Tanque 2');
    }

    public function test_store_creates_new_tank(): void
    {
        $tankData = [
            'name' => 'Nuevo Tanque',
            'serial_number' => 'SN003',
            'capacity' => 3000.0,
            'height' => 200.0,
            'location' => 'Ubicación C',
            'diameter' => 75.0
        ];

        // Realizar petición POST a la API
        $response = $this->postJson('/api/tanks', $tankData);

        // Verificar respuesta correcta
        $response->assertStatus(201)
                ->assertJsonPath('data.name', 'Nuevo Tanque')
                ->assertJsonPath('data.serial_number', 'SN003')
                ->assertJson([
                    'data' => [
                        'capacity' => 3000
                    ]
                ])
                ->assertJsonPath('data.is_active', true);

        // Verificar que se ha guardado en la base de datos
        $this->assertDatabaseHas('tanks', [
            'name' => 'Nuevo Tanque',
            'serial_number' => 'SN003',
            'capacity' => 3000.0
        ]);
    }

    public function test_store_validates_input_data(): void
    {
        // Intentar crear tanque con datos incorrectos/faltantes
        $response = $this->postJson('/api/tanks', [
            'name' => 'Tanque Inválido',
            // Falta serial_number que es obligatorio
            'capacity' => -500.0, // Valor negativo
            'height' => -50.0 // Valor negativo
        ]);

        // Verificar error de validación
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['serial_number', 'capacity', 'height']);
    }

    public function test_show_returns_specific_tank(): void
    {
        // Crear un tanque de prueba
        $tank = TankModel::create([
            'name' => 'Tanque Individual',
            'serial_number' => 'SN004',
            'capacity' => 500.0,
            'height' => 80.0,
            'location' => 'Ubicación D',
            'diameter' => 40.0,
            'is_active' => true
        ]);

        // Realizar petición GET a la API
        $response = $this->getJson("/api/tanks/{$tank->id}");

        // Verificar respuesta correcta
        $response->assertStatus(200)
                ->assertJsonPath('data.name', 'Tanque Individual')
                ->assertJsonPath('data.serial_number', 'SN004');
    }

    public function test_show_returns_404_for_nonexistent_tank(): void
    {
        // ID que no existe
        $nonExistentId = 999;

        // Realizar petición GET a la API
        $response = $this->getJson("/api/tanks/{$nonExistentId}");

        // Verificar respuesta 404
        $response->assertStatus(404);
    }

    public function test_update_modifies_tank(): void
    {
        // Crear un tanque de prueba
        $tank = TankModel::create([
            'name' => 'Tanque para Actualizar',
            'serial_number' => 'SN005',
            'capacity' => 800.0,
            'height' => 90.0,
            'location' => 'Ubicación E',
            'diameter' => 45.0,
            'is_active' => true
        ]);

        // Datos para actualizar
        $updateData = [
            'name' => 'Tanque Actualizado',
            'capacity' => 1200.0,
            'location' => 'Nueva Ubicación',
            'is_active' => false
        ];

        // Realizar petición PUT a la API
        $response = $this->putJson("/api/tanks/{$tank->id}", $updateData);

        // Verificar respuesta correcta
        $response->assertStatus(200)
                ->assertJsonPath('data.name', 'Tanque Actualizado')
                ->assertJson([
                    'data' => [
                        'capacity' => 1200
                    ]
                ])
                ->assertJsonPath('data.location', 'Nueva Ubicación')
                ->assertJsonPath('data.is_active', false);

        // El número de serie no debería cambiar
        $response->assertJsonPath('data.serial_number', 'SN005');

        // Verificar actualización en base de datos
        $this->assertDatabaseHas('tanks', [
            'id' => $tank->id,
            'name' => 'Tanque Actualizado',
            'capacity' => 1200.0,
            'is_active' => false
        ]);
    }

    public function test_update_validates_input_data(): void
    {
        // Crear un tanque de prueba
        $tank = TankModel::create([
            'name' => 'Tanque para Validar',
            'serial_number' => 'SN006',
            'capacity' => 1000.0,
            'height' => 100.0,
            'location' => 'Ubicación F',
            'diameter' => 50.0,
            'is_active' => true
        ]);

        // Datos inválidos para actualizar
        $invalidData = [
            'capacity' => -200.0, // Valor negativo
            'height' => -30.0 // Valor negativo
        ];

        // Realizar petición PUT a la API
        $response = $this->putJson("/api/tanks/{$tank->id}", $invalidData);

        // Verificar error de validación
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['capacity', 'height']);
    }

    public function test_destroy_removes_tank(): void
    {
        // Crear un tanque de prueba
        $tank = TankModel::create([
            'name' => 'Tanque para Eliminar',
            'serial_number' => 'SN007',
            'capacity' => 600.0,
            'height' => 85.0,
            'location' => 'Ubicación G',
            'diameter' => 42.0,
            'is_active' => true
        ]);

        // Realizar petición DELETE a la API
        $response = $this->deleteJson("/api/tanks/{$tank->id}");

        // Verificar respuesta correcta
        $response->assertStatus(200)
                ->assertJson(['message' => 'Tanque eliminado con éxito']);

        // Verificar eliminación en base de datos
        $this->assertDatabaseMissing('tanks', ['id' => $tank->id]);
    }

    public function test_destroy_returns_404_for_nonexistent_tank(): void
    {
        // ID que no existe
        $nonExistentId = 999;

        // Realizar petición DELETE a la API
        $response = $this->deleteJson("/api/tanks/{$nonExistentId}");

        // Verificar respuesta 404
        $response->assertStatus(404);
    }
}
