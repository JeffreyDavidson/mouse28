<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('application and database health are reported as available', function (): void {
    $this->getJson('/up')
        ->assertOk()
        ->assertExactJson(['status' => 'up']);
});

test('an unavailable database is reported as unhealthy', function (): void {
    $defaultConnection = config('database.default');

    config()->set([
        'app.debug' => false,
        'database.default' => 'unavailable',
        'database.connections.unavailable' => [
            'driver' => 'sqlite',
            'database' => '/dev/null/database.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
    ]);
    DB::purge('unavailable');

    $response = $this->getJson('/up');

    DB::purge('unavailable');
    config()->set('database.default', $defaultConnection);

    $response
        ->assertServerError()
        ->assertExactJson(['status' => 'down']);
});
