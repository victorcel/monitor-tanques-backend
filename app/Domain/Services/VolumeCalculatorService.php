<?php

namespace App\Domain\Services;

use App\Domain\Models\Tank;

class VolumeCalculatorService
{
    /**
     * Calcula el volumen de líquido en un tanque basado en el nivel del líquido.
     * Este método maneja diferentes tipos de tanques (cilíndricos, rectangulares, etc.)
     * 
     * @param Tank $tank El tanque para el cual calcular el volumen
     * @param float $liquidLevel El nivel de líquido en centímetros
     * @return float El volumen calculado en litros
     */
    public function calculateVolume(Tank $tank, float $liquidLevel): float
    {
        // Asegurarnos de que el nivel no sea superior a la altura del tanque
        $liquidLevel = min($liquidLevel, $tank->getHeight());
        
        // Asegurarnos de que el nivel no sea negativo
        $liquidLevel = max(0, $liquidLevel);
        
        // Si tiene diámetro, asumir tanque cilíndrico
        if ($tank->getDiameter()) {
            return $this->calculateCylindricalVolume($liquidLevel, $tank->getDiameter());
        }
        
        // Si no tiene diámetro, usar método simple basado en capacidad y altura
        return $this->calculateSimpleVolume($liquidLevel, $tank->getHeight(), $tank->getCapacity());
    }
    
    /**
     * Calcula el volumen para un tanque cilíndrico
     */
    private function calculateCylindricalVolume(float $liquidLevel, float $diameter): float
    {
        // Volumen de un cilindro = π * radio² * altura
        $radius = $diameter / 2;
        $volumeInCubicCm = pi() * pow($radius, 2) * $liquidLevel;
        
        // Convertir cm³ a litros (1L = 1000cm³)
        return $volumeInCubicCm / 1000;
    }
    
    /**
     * Calcula el volumen usando proporción simple basada en altura y capacidad total
     */
    private function calculateSimpleVolume(float $liquidLevel, float $totalHeight, float $totalCapacity): float
    {
        if ($totalHeight <= 0) {
            return 0;
        }
        
        // Regla de tres simple: si totalHeight = totalCapacity, entonces liquidLevel = X
        return ($liquidLevel * $totalCapacity) / $totalHeight;
    }
}