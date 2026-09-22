<?php

use Illuminate\Console\Scheduling\Schedule;

test('Telescope pruning runs daily only on enabled staging', function (string $environment, bool $enabled, bool $runs): void {
    config()->set(['mouse28.deployment_environment' => $environment, 'telescope.enabled' => $enabled]);
    $event = collect(app(Schedule::class)->events())->sole();

    expect($event->expression)->toBe('0 0 * * *')
        ->and($event->command)->toContain('telescope:prune', '--hours=48')
        ->and($event->withoutOverlapping)->toBeTrue()
        ->and($event->filtersPass($this->app))->toBe($runs);
})->with([
    'staging enabled' => ['staging', true, true],
    'staging disabled' => ['staging', false, false],
    'production' => ['production', true, false],
]);
