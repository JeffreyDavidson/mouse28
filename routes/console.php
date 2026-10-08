<?php

use App\Enums\DeploymentEnvironment;
use App\Jobs\RecordQueueHeartbeat;
use App\Models\Subscriber;
use App\Support\Monitoring\Health\RuntimeHealthMonitor;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schedule;
use Laravel\Nightwatch\Console\Sample;

/*
 * Each daily task's overlap lock expires after an hour instead of the default 24, so a run
 * killed mid-way (out of memory, a hung server) cannot leave a lock that skips the next run.
 */
Schedule::command('telescope:prune', ['--hours' => Config::integer('telescope.retention_hours')])
    ->daily()
    ->withoutOverlapping(60)
    ->when(fn (): bool => Config::boolean('telescope.enabled') && DeploymentEnvironment::current() === DeploymentEnvironment::Staging);

Schedule::command('model:prune', ['--model' => [Subscriber::class]])
    ->daily()
    ->withoutOverlapping(60)
    ->onOneServer();

// Records the scheduler heartbeat and probes the queue. Enable only when a scheduler cron and a default-queue worker run.
Schedule::call(function (RuntimeHealthMonitor $runtimeHealthMonitor): void {
    $runtimeHealthMonitor->recordSchedulerHeartbeat();
    RecordQueueHeartbeat::dispatch();
})
    ->name('runtime-health:heartbeat:'.app()->environment())
    ->everyMinute()
    ->tap(Sample::rate(0.1))
    ->withoutOverlapping(5)
    ->onOneServer()
    ->when(fn (): bool => Config::boolean('health.runtime.enabled'));
