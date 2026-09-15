<?php

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('guest can render the admin login', function (): void {
    get(route('filament.admin.auth.login'))
        ->assertOk()
        ->assertSee('Mouse28')
        ->assertSee('Welcome Back')
        ->assertSee('Sign in to continue telling your story')
        ->assertDontSee('✨');
});

test('enrolled administrators must provide a valid authenticator code to sign in', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->admin()->create();

    $login = livewire(Login::class);

    $login
        ->fillForm(['email' => $user->email, 'password' => 'password'])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertNoRedirect();

    assertGuest();

    $login
        ->set('data.multiFactor.app.code', 'invalid')
        ->call('authenticate')
        ->assertHasErrors(['data.multiFactor.app.code']);

    assertGuest();

    $login
        ->set('data.multiFactor.app.code', AppAuthentication::make()->getCurrentCode($user))
        ->call('authenticate')
        ->assertRedirect();

    $login->assertHasNoErrors();

    assertAuthenticatedAs($user);
});

test('unenrolled administrators can sign in to complete required enrollment', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->admin()->withoutAppAuthentication()->create();

    livewire(Login::class)
        ->fillForm(['email' => $user->email, 'password' => 'password'])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertAuthenticatedAs($user);
});

test('administrators can sign in with a single use recovery code', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->admin()->create();
    $provider = AppAuthentication::make()->recoverable();
    $codes = $provider->generateRecoveryCodes();
    $provider->saveRecoveryCodes($user, $codes);

    livewire(Login::class)
        ->fillForm(['email' => $user->email, 'password' => 'password'])
        ->call('authenticate')
        ->set('data.multiFactor.app.useRecoveryCode', true)
        ->set('data.multiFactor.app.recoveryCode', $codes[0])
        ->call('authenticate')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    assertAuthenticatedAs($user);
    expect($provider->verifyRecoveryCode($codes[0], $user))->toBeFalse();
});

test('non administrators cannot sign in even with valid credentials', function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    $user = User::factory()->create();

    livewire(Login::class)
        ->fillForm(['email' => $user->email, 'password' => 'password'])
        ->call('authenticate')
        ->assertHasFormErrors(['email'])
        ->assertNoRedirect();

    assertGuest();
});
