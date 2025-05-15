<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Configurar el entorno de pruebas
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Ejecutar las migraciones específicamente para la base de datos de prueba (SQLite en memoria)
        Artisan::call('migrate:fresh');
    }
}
