<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        // Refuse cached or external database configuration before RefreshDatabase runs.
        if ($app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Tests require SQLite :memory:. Clear cached configuration and use phpunit.xml or audit/run.ps1.');
        }
        return $app;
    }
}
