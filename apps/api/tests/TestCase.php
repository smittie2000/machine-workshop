<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->refreshApplication();
        if (config('database.default') !== 'pgsql'
            || config('database.connections.pgsql.host') !== 'test-db'
            || config('database.connections.pgsql.database') !== 'workshop_test') {
            throw new \RuntimeException('Database tests must use the isolated workshop_test database on test-db.');
        }
        parent::setUp();
    }
}
