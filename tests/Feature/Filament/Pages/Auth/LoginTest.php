<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('guest can render the admin login', function (): void {
    get(route('filament.admin.auth.login'))
        ->assertOk()
        ->assertSee('Mouse28')
        ->assertSee('Welcome Back')
        ->assertSee('Sign in to continue telling your story')
        ->assertDontSee('✨');
});
