<?php

namespace Tests\Unit\Domain\Services;

use App\Domain\Models\Tank;
use App\Domain\Services\VolumeCalculatorService;
use DateTime;
use PHPUnit\Framework\TestCase;

class VolumeCalculatorServiceTest extends TestCase
{
    private VolumeCalculatorService $volumeCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->volumeCalculator = new VolumeCalculatorService();
    }

    public function test_calculate_cylindrical_volume(): void
    {
        // Crear un tanque cilíndrico de prueba
        $tank = new Tank(
            1,
            'Tanque Cilíndrico',
            'SN123456',
            1000, // Capacidad en litros
            100,  // Altura en cm
            'Ubicación A',
            50,   // Diámetro en cm
            true
        );

        // Volumen para nivel de líquido a la mitad
        $liquidLevel = 50; // cm
        $volume = $this->volumeCalculator->calculateVolume($tank, $liquidLevel);
        
        // Para un cilindro: π * r² * h = π * 25² * 50 = π * 625 * 50 ≈ 98174.77 cm³ ≈ 98.17 litros
        $expectedVolume = pi() * pow(25, 2) * 50 / 1000;
        
        $this->assertEqualsWithDelta($expectedVolume, $volume, 0.1, 'El cálculo de volumen cilíndrico debería ser correcto');
    }

    public function test_calculate_simple_volume(): void
    {
        // Crear un tanque no cilíndrico (sin diámetro) de prueba
        $tank = new Tank(
            2,
            'Tanque Rectangular',
            'SN987654',
            1000, // Capacidad en litros
            100,  // Altura en cm
            'Ubicación B',
            null, // Sin diámetro
            true
        );

        // Volumen para nivel de líquido a la mitad
        $liquidLevel = 50; // cm
        $volume = $this->volumeCalculator->calculateVolume($tank, $liquidLevel);
        
        // Para cálculo simple: (liquidLevel / totalHeight) * totalCapacity = (50 / 100) * 1000 = 500 litros
        $expectedVolume = 500;
        
        $this->assertEquals($expectedVolume, $volume, 'El cálculo de volumen simple debería ser correcto');
    }

    public function test_ensure_liquid_level_cannot_exceed_tank_height(): void
    {
        // Crear un tanque de prueba
        $tank = new Tank(
            3,
            'Tanque Alto',
            'SN456789',
            1000, // Capacidad en litros
            100,  // Altura en cm
            'Ubicación C',
            null, // Sin diámetro
            true
        );

        // Intentar con un nivel de líquido superior a la altura del tanque
        $liquidLevel = 150; // cm (mayor que la altura de 100 cm)
        $volume = $this->volumeCalculator->calculateVolume($tank, $liquidLevel);
        
        // Debe limitarse a la capacidad total
        $expectedVolume = 1000; // litros (capacidad total)
        
        $this->assertEquals($expectedVolume, $volume, 'El volumen no debería exceder la capacidad del tanque');
    }

    public function test_ensure_liquid_level_cannot_be_negative(): void
    {
        // Crear un tanque de prueba
        $tank = new Tank(
            4,
            'Tanque X',
            'SN111111',
            1000, // Capacidad en litros
            100,  // Altura en cm
            'Ubicación D',
            null, // Sin diámetro
            true
        );

        // Intentar con un nivel de líquido negativo
        $liquidLevel = -10; // cm (no tiene sentido en el mundo real)
        $volume = $this->volumeCalculator->calculateVolume($tank, $liquidLevel);
        
        // Debe limitarse a cero
        $expectedVolume = 0; // litros
        
        $this->assertEquals($expectedVolume, $volume, 'El volumen no puede ser negativo');
    }
}