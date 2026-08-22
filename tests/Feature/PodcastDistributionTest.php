<?php

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('show level distribution links appear across public podcast surfaces', function (): void {
    $podcast = Podcast::query()->create([
        'name' => 'Mouse28',
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => 'https://open.spotify.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
        'rss_url' => 'https://feeds.example.com/mouse28.xml',
    ]);
    Episode::factory()->create();

    foreach ([route('home'), route('episodes.index')] as $url) {
        $response = get($url)->assertOk();

        foreach ($podcast->distributionLinks() as $link) {
            $response->assertSee($link['url'], false);
        }

        $response->assertDontSee('Apple Podcasts · Soon')
            ->assertDontSee('Spotify · Soon');
    }
});

test('episode destinations override show links and missing destinations fall back', function (): void {
    Podcast::query()->create([
        'name' => 'Mouse28 Travel Podcast',
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => 'https://open.spotify.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
        'rss_url' => 'https://feeds.example.com/mouse28.xml',
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
        ->assertSee('https://feeds.example.com/mouse28.xml', false)
        ->assertSee('"name":"Mouse28 Travel Podcast"', false);
});

test('generated rss feed is available without persisting default settings', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee(route('rss.podcast'), false)
        ->assertSee('RSS Feed');

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee(route('rss.podcast'), false);

    assertDatabaseCount('podcasts', 0);
});
