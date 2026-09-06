<?php

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('show level distribution links appear across public podcast surfaces', function (): void {
    $podcast = Podcast::query()->create([
        'name' => 'Mouse28',
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => 'https://open.spotify.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
    ]);
    Episode::factory()->create();

    foreach ([route('home'), route('episodes.index')] as $url) {
        $response = get($url)->assertOk();

        foreach ($podcast->distributionLinks() as $link) {
            $response->assertSee($link['url'], false);
        }

        $response->assertSee(config('podcast.rss_url'), false);

        $response->assertDontSee('Apple Podcasts · Soon')
            ->assertDontSee('Spotify · Soon');
    }
});

test('the canonical Transistor feed is advertised without persisting default settings', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee(config('podcast.rss_url'), false)
        ->assertSee('RSS Feed');

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee(config('podcast.rss_url'), false);
});
