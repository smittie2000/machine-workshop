<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_reports_database_connectivity(): void
    {
        DB::shouldReceive('select')->once()->with('select 1')->andReturn([(object) ['value' => 1]]);
        $this->getJson('/api/health')->assertOk()->assertExactJson([
            'status' => 'ok', 'database' => 'connected',
        ]);
    }

    public function test_health_does_not_expose_database_errors(): void
    {
        DB::shouldReceive('select')->once()->andThrow(new \RuntimeException('private connection details'));
        $this->getJson('/api/health')->assertStatus(503)->assertExactJson([
            'status' => 'unavailable', 'database' => 'unavailable',
        ]);
    }
}
