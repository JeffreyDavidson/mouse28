<?php

use App\Models\User;
use App\Providers\TelescopeServiceProvider;
use Illuminate\Support\Facades\Gate;

test('the application registers the Telescope service provider', function (): void {
    expect($this->app->getProvider(TelescopeServiceProvider::class))
        ->toBeInstanceOf(TelescopeServiceProvider::class);
});

test('only administrators may view Telescope', function (): void {
    $administrator = User::factory()->admin()->make();
    $editor = User::factory()->make();

    expect(Gate::forUser($administrator)->allows('viewTelescope'))->toBeTrue()
        ->and(Gate::forUser($editor)->allows('viewTelescope'))->toBeFalse();
});
