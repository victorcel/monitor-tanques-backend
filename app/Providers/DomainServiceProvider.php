<?php

namespace App\Providers;

use App\Domain\Repositories\TankRepositoryInterface;
use App\Domain\Repositories\TankReadingRepositoryInterface;
use App\Domain\Services\VolumeCalculatorService;
use App\Infrastructure\Persistence\EloquentTankRepository;
use App\Infrastructure\Persistence\EloquentTankReadingRepository;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar repositorios
        $this->app->bind(TankRepositoryInterface::class, EloquentTankRepository::class);
        $this->app->bind(TankReadingRepositoryInterface::class, EloquentTankReadingRepository::class);
        
        // Registrar servicios
        $this->app->singleton(VolumeCalculatorService::class, function ($app) {
            return new VolumeCalculatorService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}