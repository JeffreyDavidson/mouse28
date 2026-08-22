<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PodcastDistributionTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_level_distribution_links_appear_across_public_podcast_surfaces(): void
    {
        $podcast = Podcast::query()->create([
            'name' => 'Mouse28',
            'apple_url' => 'https://podcasts.apple.com/show/mouse28',
            'spotify_url' => 'https://open.spotify.com/show/mouse28',
            'youtube_url' => 'https://youtube.com/@mouse28',
            'rss_url' => 'https://feeds.example.com/mouse28.xml',
        ]);
        Episode::factory()->create();

        foreach ([route('home'), route('episodes.index')] as $url) {
            $response = $this->get($url)->assertOk();

            foreach ($podcast->distributionLinks() as $link) {
                $response->assertSee($link['url'], false);
            }

            $response->assertDontSee('Apple Podcasts · Soon')
                ->assertDontSee('Spotify · Soon');
        }
    }

    public function test_episode_destinations_override_show_links_and_missing_destinations_fall_back(): void
    {
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

        $this->get(route('episodes.show', $episode))
            ->assertOk()
            ->assertSee($episode->apple_url, false)
            ->assertSee('Listen to this episode')
            ->assertSee('https://open.spotify.com/show/mouse28', false)
            ->assertSee('Visit the show')
            ->assertSee('https://youtube.com/@mouse28', false)
            ->assertSee('Visit the channel')
            ->assertSee('https://feeds.example.com/mouse28.xml', false)
            ->assertSee('"name":"Mouse28 Travel Podcast"', false);
    }

    public function test_generated_rss_feed_is_available_without_persisting_default_settings(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('rss.podcast'), false)
            ->assertSee('RSS Feed');

        $this->get(route('episodes.index'))
            ->assertOk()
            ->assertSee(route('rss.podcast'), false);

        $this->assertDatabaseCount('podcasts', 0);
    }
}
