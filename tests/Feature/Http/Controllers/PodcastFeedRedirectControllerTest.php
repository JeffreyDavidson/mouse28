<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('podcast feed redirects to its canonical provider', function (): void {
    get(route('rss.podcast'))
        ->assertRedirect(config()->string('podcast.rss_url'))
        ->assertMovedPermanently();
});

test('podcast feed redirects podcast apps without starting a session or setting cookies', function (): void {
    config()->set('session.driver', 'database');

    $response = get(route('rss.podcast'))
        ->assertMovedPermanently()
        ->assertHeaderMissing('Set-Cookie');

    expect($response->headers->getCookies())->toBeEmpty();
    assertDatabaseCount('sessions', 0);
});
