<?php

use App\Jobs\RecordQueueHeartbeat;
use App\Models\Subscriber;
use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Queue;

test('Telescope pruning runs daily only on enabled staging', function (string $environment, bool $enabled, bool $runs): void {
    config()->set(['app.deployment_environment' => $environment, 'telescope.enabled' => $enabled]);
    $event = collect(app(Schedule::class)->events())->sole(fn (Event $event): bool => str_contains((string) $event->command, 'telescope:prune'));

    expect($event->expression)->toBe('0 0 * * *')
        ->and($event->command)->toContain('telescope:prune', '--hours=48')
        ->and($event->withoutOverlapping)->toBeTrue()
        ->and($event->filtersPass($this->app))->toBe($runs);
})->with([
    'staging enabled' => ['staging', true, true],
    'staging disabled' => ['staging', false, false],
    'production' => ['production', true, false],
]);

test('the runtime heartbeat is scheduled every minute only when runtime health is enabled', function (bool $enabled): void {
    config()->set('health.runtime.enabled', $enabled);
    $event = collect(app(Schedule::class)->events())->sole(fn (Event $event): bool => str_starts_with((string) $event->description, 'runtime-health:heartbeat:'));

    expect($event->expression)->toBe('* * * * *')
        ->and($event->filtersPass($this->app))->toBe($enabled);
})->with(['enabled' => [true], 'disabled' => [false]]);

test('the runtime heartbeat records the scheduler and probes the queue', function (): void {
    config()->set('health.runtime.enabled', true);
    Cache::flush();
    Queue::fake();
    Date::setTestNow('2026-09-27 12:34:00');
    $event = collect(app(Schedule::class)->events())->sole(fn (Event $event): bool => str_starts_with((string) $event->description, 'runtime-health:heartbeat:'));

    $event->run($this->app);

    expect(Cache::get(RuntimeHealthMonitor::SCHEDULER_HEARTBEAT_KEY))->toBe(Date::now()->getTimestamp());
    Queue::assertPushed(RecordQueueHeartbeat::class);
});

test('stale newsletter subscribers are pruned daily on one server', function (): void {
    $event = collect(app(Schedule::class)->events())->sole(fn (Event $event): bool => str_contains((string) $event->command, 'model:prune'));

    expect($event->expression)->toBe('0 0 * * *')
        ->and($event->command)->toContain('model:prune', "--model='".Subscriber::class."'")
        ->and($event->withoutOverlapping)->toBeTrue()
        ->and($event->onOneServer)->toBeTrue();
});
