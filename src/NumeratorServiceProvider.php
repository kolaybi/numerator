<?php

namespace KolayBi\Numerator;

use Illuminate\Support\ServiceProvider;

class NumeratorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootConfig();
        $this->bootMigrations();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/numerator.php', 'numerator');
    }

    private function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'numerator-migrations');
    }

    private function bootConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/numerator.php' => config_path('numerator.php'),
        ], 'numerator-config');
    }
}
