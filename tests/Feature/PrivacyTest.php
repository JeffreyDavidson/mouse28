<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('privacy information explains collection retention and available contact options', function (): void {
    get(route('privacy'))
        ->assertOk()
        ->assertSee('Privacy information')
        ->assertSee('until we manually delete them')
        ->assertSee('Resend')
        ->assertSee('Cloudflare Turnstile')
        ->assertSee('Transistor')
        ->assertSee('Sentry')
        ->assertSee('Nightwatch')
        ->assertSee('href="'.route('contact.show').'"', false);
});

test('privacy information is discoverable from the shared footer and sitemap', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee('href="'.route('privacy').'"', false);

    get(route('sitemap'))
        ->assertOk()
        ->assertSee('<loc>'.route('privacy').'</loc>', false);
});
