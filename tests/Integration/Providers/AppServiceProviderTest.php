<?php

use App\Models\User;
use App\Providers\AppServiceProvider;
use Filament\Support\Events\FilamentUpgraded;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Console\Migrations\FreshCommand;
use Illuminate\Database\Console\Migrations\RefreshCommand;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Database\Console\Migrations\RollbackCommand;
use Illuminate\Database\Console\WipeCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Nightwatch\Core;
use Symfony\Component\Finder\SplFileInfo;

test('the application registers its service provider', function (): void {
    // Act
    $provider = $this->app->getProvider(AppServiceProvider::class);

    // Assert
    expect($provider)->toBeInstanceOf(AppServiceProvider::class);
});

test('destructive database command protection follows the application environment', function (string $environment, bool $prohibited): void {
    // Arrange
    $this->app->detectEnvironment(fn (): string => $environment);
    $provider = new AppServiceProvider($this->app);

    try {
        // Act
        $provider->boot();

        // Assert
        foreach ([FreshCommand::class, RefreshCommand::class, ResetCommand::class, RollbackCommand::class, WipeCommand::class] as $commandClass) {
            $command = app($commandClass);
            $guard = new ReflectionMethod($command, 'isProhibited');

            expect($guard->invoke($command, true))->toBe($prohibited);
        }
    } finally {
        DB::prohibitDestructiveCommands(false);
    }
})->with(['production' => ['production', true], 'local' => ['local', false], 'testing' => ['testing', false]]);

test('lazy loading prevention follows the application environment', function (string $environment, bool $prevented): void {
    // Arrange
    $this->app->detectEnvironment(fn (): string => $environment);
    $provider = new AppServiceProvider($this->app);
    $previous = Model::preventsLazyLoading();

    try {
        // Act
        $provider->boot();

        // Assert
        expect(Model::preventsLazyLoading())->toBe($prevented);
    } finally {
        Model::preventLazyLoading($previous);
        DB::prohibitDestructiveCommands(false);
    }
})->with(['production' => ['production', false], 'local' => ['local', true], 'testing' => ['testing', true]]);

test('production database protection cannot be bypassed with force', function (string $command): void {
    // Arrange
    $this->app->detectEnvironment(fn (): string => 'production');
    $provider = new AppServiceProvider($this->app);

    try {
        $provider->boot();

        // An unconfigured connection prevents database access even if the guard regresses.
        // Act
        $result = Artisan::call($command, ['--force' => true, '--database' => 'production-guard-test-unconfigured']);

        // Assert
        expect($result)->toBe(1)
            ->and(Artisan::output())->toContain('prohibited from running');
    } finally {
        DB::prohibitDestructiveCommands(false);
    }
})->with(['db:wipe', 'migrate:fresh', 'migrate:refresh', 'migrate:reset', 'migrate:rollback']);

test('Nightwatch identifies administrators by a keyed digest without their profile details', function (): void {
    // Arrange
    $admin = User::factory()->admin()->make(['id' => 42, 'name' => 'Private Administrator', 'email' => 'private@example.test']);
    config()->set('app.key', 'private-application-key');
    $resolver = app(Core::class)->userDetailsResolver
        ?? throw new UnexpectedValueException('The Nightwatch user resolver is not registered.');

    // Act
    $userDetails = $resolver($admin);

    // Assert
    expect($userDetails)->toBe(['id' => hash_hmac('sha256', '42', 'private-application-key')])
        ->and(serialize($userDetails))->not->toContain('Private Administrator', 'private@example.test');
});

test('newsletter deliveries are limited to the configured emails per second', function (): void {
    config()->set('mouse28.rate_limits.newsletter_delivery_per_second', 3);
    $resolver = RateLimiter::limiter('newsletter-delivery');
    $limit = $resolver instanceof Closure ? $resolver() : null;

    if (! $limit instanceof Limit) {
        throw new LogicException('The newsletter-delivery limiter must resolve to a limit.');
    }

    expect($limit->maxAttempts)->toBe(3)
        ->and($limit->decaySeconds)->toBe(1);
});

test('Filament upgrades keep published assets at the committed non-executable file mode', function (): void {
    // Arrange
    $publicPath = sys_get_temp_dir().'/mouse28-filament-assets-'.bin2hex(random_bytes(4));
    $this->app->usePublicPath($publicPath);

    try {
        // filament:upgrade publishes the assets, then dispatches FilamentUpgraded.
        Artisan::call('filament:assets');

        // Act
        FilamentUpgraded::dispatch();

        // Assert
        $modes = collect(File::allFiles($publicPath))
            ->mapWithKeys(fn (SplFileInfo $file): array => [$file->getRelativePathname() => decoct(fileperms($file->getPathname()) & 0777)]);

        expect($modes)->not->toBeEmpty()
            ->each->toBe('644');
    } finally {
        File::deleteDirectory($publicPath);
    }
});
