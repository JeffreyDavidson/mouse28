<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('podcast feed redirects to its canonical provider', function (): void {
    get(route('rss.podcast'))
        ->assertRedirect(config('podcast.rss_url'))
        ->assertStatus(301);
});

test('the legacy podcast feed route permanently redirects to Transistor', function (): void {
    get(route('rss.podcast'))
        ->assertRedirect(config('podcast.rss_url'))
        ->assertStatus(301);
});
