<?php

namespace KolayBi\Numerator\Tests;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use KolayBi\Numerator\NumeratorServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__ . '/../database/factories');
        Carbon::setTestNow('1999-07-23 12:45:00');
    }

    protected function getPackageProviders($app): array
    {
        return [
            NumeratorServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testdb');
        $app['config']->set('database.connections.testdb', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);
    }
}
