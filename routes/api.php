<?php

use App\Http\Controllers\Api\TankController;
use App\Http\Controllers\Api\TankReadingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas para tanques
Route::apiResource('tanks', TankController::class);

// Rutas para lecturas de tanques
Route::post('tank-readings', [TankReadingController::class, 'store'])
    ->name('readings.store');
Route::post('tank-readings/batch', [TankReadingController::class, 'storeBatch'])
    ->name('readings.store.batch');
Route::get('tanks/{tankId}/readings', [TankReadingController::class, 'index'])
    ->name('tanks.readings.index');
Route::get('tanks/{tankId}/readings/latest', [TankReadingController::class, 'latest'])
    ->name('tanks.readings.latest');
Route::get('tanks/{tankId}/readings/date-range', [TankReadingController::class, 'getByDateRange'])
    ->name('tanks.readings.date-range');