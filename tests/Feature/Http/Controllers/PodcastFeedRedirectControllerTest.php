<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('podcast feed redirects to its canonical provider', function (): void {
    get(route('rss.podcast'))
        ->assertRedirect(config()->string('podcast.rss_url'))
        ->assertMovedPermanently();
});
