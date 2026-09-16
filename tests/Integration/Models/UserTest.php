<?php

use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

test('app authentication credentials are encrypted hidden and protected from mass assignment', function (): void {
    $user = User::factory()->admin()->create();
    $provider = AppAuthentication::make()->recoverable();
    $secret = $provider->generateSecret();
    $codes = $provider->generateRecoveryCodes();

    $provider->saveSecret($user, $secret);
    $provider->saveRecoveryCodes($user, $codes);
    $user->refresh();

    expect($user->getAppAuthenticationSecret())->toBe($secret)
        ->and($user->getRawOriginal('app_authentication_secret'))->not->toBe($secret)
        ->and($user->getAppAuthenticationRecoveryCodes())->toHaveCount(8)
        ->and($user->toArray())->not->toHaveKeys(['app_authentication_secret', 'app_authentication_recovery_codes'])
        ->and($user->isFillable('app_authentication_secret'))->toBeFalse()
        ->and($user->isFillable('app_authentication_recovery_codes'))->toBeFalse()
        ->and($provider->verifyRecoveryCode($codes[0], $user))->toBeTrue()
        ->and($provider->verifyRecoveryCode($codes[0], $user))->toBeFalse();
});

test('existing users have app authentication disabled until enrollment', function (): void {
    $user = User::factory()->admin()->withoutAppAuthentication()->create();

    expect(AppAuthentication::make()->isEnabled($user))->toBeFalse()
        ->and($user->getAppAuthenticationSecret())->toBeNull()
        ->and($user->getAppAuthenticationRecoveryCodes())->toBeNull();
});
