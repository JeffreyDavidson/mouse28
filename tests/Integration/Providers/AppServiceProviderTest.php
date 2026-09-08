<?php

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Core;

test('destructive database command protection follows the application environment', function (string $environment, bool $prohibited): void {
    $this->app->detectEnvironment(fn (): string => $environment);

    try {
        $this->app->getProvider(AppServiceProvider::class)->boot();

        foreach ([FreshCommand::class, RefreshCommand::class, ResetCommand::class, RollbackCommand::class, WipeCommand::class] as $commandClass) {
            $command = app($commandClass);
            $guard = new ReflectionMethod($command, 'isProhibited');

            expect($guard->invoke($command, true))->toBe($prohibited);
        }
    } finally {
        DB::prohibitDestructiveCommands(false);
    }
})->with(['production' => ['production', true], 'local' => ['local', false], 'testing' => ['testing', false]]);

test('production database protection cannot be bypassed with force', function (string $command): void {
    $this->app->detectEnvironment(fn (): string => 'production');

    try {
        $this->app->getProvider(AppServiceProvider::class)->boot();

        // An unconfigured connection prevents database access even if the guard regresses.
        $result = Artisan::call($command, ['--force' => true, '--database' => 'production-guard-test-unconfigured']);

        expect($result)->toBe(1)
            ->and(Artisan::output())->toContain('prohibited from running');
    } finally {
        DB::prohibitDestructiveCommands(false);
    }
})->with(['db:wipe', 'migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback']);

test('Nightwatch identifies administrators without sending their profile details', function (): void {
    $admin = User::factory()->admin()->make();
    $resolver = app(Core::class)->userDetailsResolver;

    $userDetails = $resolver($admin);

    expect($userDetails)->toBe([]);
});
