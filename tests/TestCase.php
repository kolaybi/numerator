<?php

namespace KolayBi\Numerator\Tests;

use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    protected function useCaseInsensitiveFormattedNumbers(): void
    {
        Schema::drop('numerator_sequences');
        DB::statement('CREATE TABLE numerator_sequences (
            id varchar NOT NULL PRIMARY KEY,
            created_at datetime,
            updated_at datetime,
            deleted_at datetime,
            profile_id varchar NOT NULL,
            model_type varchar NOT NULL,
            model_id varchar NOT NULL,
            formatted_number varchar NOT NULL COLLATE NOCASE
        )');
    }
}
