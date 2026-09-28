<?php

use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\getJson;

pest()->use(RefreshDatabase::class);

test('the health endpoint reports a healthy application and database', function (): void {
    getJson('/up')
        ->assertOk()
        ->assertExactJson(['status' => 'up']);
});

test('the health endpoint reports an unavailable database as down', function (): void {
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

    $response = getJson('/up');

    DB::purge('unavailable');
    config()->set('database.default', $defaultConnection);
    $response->assertStatus(500)->assertExactJson(['status' => 'down']);
});

test('the health endpoint accepts fresh runtime heartbeats when runtime monitoring is enabled', function (): void {
    config()->set('health.runtime.enabled', true);
    Cache::put(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, Date::now()->getTimestamp());
    Cache::put(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, Date::now()->getTimestamp());

    getJson('/up')
        ->assertOk()
        ->assertExactJson(['status' => 'up']);
});

test('the health endpoint reports stale or misconfigured runtime heartbeats as down', function (int $schedulerAgeMinutes, int $queueAgeMinutes, int $maxAgeSeconds): void {
    config()->set(['app.debug' => false, 'health.runtime.enabled' => true, 'health.runtime.max_age_seconds' => $maxAgeSeconds]);
    Cache::put(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY, Date::now()->subMinutes($schedulerAgeMinutes)->getTimestamp());
    Cache::put(RuntimeHealthMonitor::QUEUE_HEARTBEAT_KEY, Date::now()->subMinutes($queueAgeMinutes)->getTimestamp());

    getJson('/up')
        ->assertStatus(500)
        ->assertExactJson(['status' => 'down']);
})->with([
    'stale scheduler' => [6, 0, 300],
    'stale queue worker' => [0, 6, 300],
    'invalid maximum age' => [0, 0, 30],
]);

test('the health endpoint ignores runtime heartbeats while runtime monitoring is disabled', function (): void {
    config()->set('health.runtime.enabled', false);
    Cache::flush();

    getJson('/up')->assertOk();
});
