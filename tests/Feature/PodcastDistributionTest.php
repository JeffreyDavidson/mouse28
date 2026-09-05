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

test('episode destinations override show links and missing destinations fall back', function (): void {
    $podcast = Podcast::query()->create([
        'name' => 'Mouse28 Travel Podcast',
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => 'https://open.spotify.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
    ]);
    $episode = Episode::factory()->create([
        'apple_url' => 'https://podcasts.apple.com/episode/42',
        'spotify_url' => null,
        'youtube_url' => null,
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee($episode->apple_url, false)
        ->assertSee('Listen to this episode')
        ->assertSee('https://open.spotify.com/show/mouse28', false)
        ->assertSee('Visit the show')
        ->assertSee('https://youtube.com/@mouse28', false)
        ->assertSee('Visit the channel')
        ->assertSee(config('podcast.rss_url'), false)
        ->assertSee('"name":"Mouse28 Travel Podcast"', false);
});

test('episode pages hide podcast platforms that are not configured', function (): void {
    $episode = Episode::factory()->create();

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertDontSee('Apple Podcasts')
        ->assertDontSee('Spotify')
        ->assertDontSee('Not configured')
        ->assertSee(config('podcast.rss_url'), false);
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

test('the legacy podcast feed route permanently redirects to Transistor', function (): void {
    get(route('rss.podcast'))
        ->assertRedirect(config('podcast.rss_url'))
        ->assertStatus(301);
});

test('episode pages embed only valid Transistor share URLs', function (): void {
    $episode = Episode::factory()->create([
        'transistor_url' => 'https://share.transistor.fm/s/428d650c',
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('src="https://share.transistor.fm/e/428d650c"', false)
        ->assertSee('title="Listen to '.$episode->title.'"', false)
        ->assertSee('Open the player in a new tab');

    $episode->update(['transistor_url' => 'https://example.com/not-a-transistor-player']);

    get(route('episodes.show', $episode->fresh()))
        ->assertOk()
        ->assertDontSee('https://example.com/not-a-transistor-player', false)
        ->assertDontSee('<iframe', false);
});
