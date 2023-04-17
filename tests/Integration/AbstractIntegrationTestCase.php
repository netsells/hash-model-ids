<?php

namespace Netsells\HashModelIds\Tests\Integration;

use Netsells\HashModelIds\ServiceProvider;
use Orchestra\Testbench\TestCase;

abstract class AbstractIntegrationTestCase extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/migrations');
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('hash-model-ids.salt', 'test-salt');
    }
}
