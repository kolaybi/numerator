<?php

namespace KolayBi\Numerator;

use Illuminate\Support\ServiceProvider;

class NumeratorServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->bootConfig();
        $this->bootMigrations();
        $this->bootTranslations();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/numerator.php', 'numerator');
    }

    private function bootConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/numerator.php' => $this->app->configPath('kolaybi/numerator.php'),
        ], 'numerator-config');
    }

    private function bootMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../database/migrations' => $this->app->databasePath('migrations'),
        ], 'numerator-migrations');
    }

    private function bootTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'numerator');

        $this->publishes([
            __DIR__ . '/../lang' => $this->app->langPath('vendor/kolaybi/numerator'),
        ], 'numerator-lang');
    }
}
