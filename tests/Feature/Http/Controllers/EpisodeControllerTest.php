<?php

use App\Models\Episode;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public index page renders', function (): void {
    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('The Mouse28 Podcast');
});

test('episode pages use concise public-facing labels', function (): void {
    $episode = Episode::factory()->create([
        'episode_number' => 28,
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertDontSee('animate-pulse', false)
        ->assertSee('data-episode-meta', false);
});

test('published episode detail page renders', function (): void {
    $episode = Episode::query()->create([
        'title' => 'Planning a Sensory-Friendly Visit',
        'slug' => 'planning-a-sensory-friendly-visit',
        'description' => 'How our family prepares for a day in the parks.',
        'show_notes' => '<p>Our favorite planning strategies.</p>',
        'transcript' => '<p><strong>Jeffrey:</strong> Welcome to the show.</p>',
        'episode_number' => 1,
        'season_number' => 1,
        'duration_seconds' => 1800,
        'transistor_url' => 'https://share.transistor.fm/s/428d650c',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSee('Our favorite planning strategies', false)
        ->assertSee('episode-detail-hero', false)
        ->assertSee('Listen to this episode')
        ->assertDontSee('Now Playing')
        ->assertSee('title="Listen to Planning a Sensory-Friendly Visit"', false)
        ->assertSee('data-episode-layout="rich"', false)
        ->assertSee('id="episode-transcript"', false)
        ->assertSee('aria-controls="episode-transcript"', false)
        ->assertSee(':aria-expanded="expanded.toString()"', false);
});

test('sparse episode detail pages use a compact continuation layout', function (): void {
    $episode = Episode::factory()->create([
        'audio_url' => null,
        'audio_path' => null,
        'show_notes' => '<p>Coming soon.</p>',
        'transcript' => null,
    ]);
    $previousEpisode = Episode::factory()->create([
        'published_at' => $episode->published_at->subDay(),
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('data-episode-layout="sparse"', false)
        ->assertSee('data-episode-continuation="compact"', false)
        ->assertSee($previousEpisode->title);
});

test('empty podcast page uses a truthful show introduction without a decorative player', function (): void {
    get(route('episodes.index'))
        ->assertOk()
        ->assertSee("We're warming up the mics", false)
        ->assertDontSee('podcast-player-preview', false);
});

test('podcast index leads with the show and uses a season tracklist', function (): void {
    Episode::factory()->count(2)->create([
        'season_number' => 1,
    ]);

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('data-podcast-archive', false)
        ->assertSee('podcast-cover-frame', false)
        ->assertSee('podcast-ledger', false)
        ->assertSee('Episode archive')
        ->assertDontSee('Show Stats')
        ->assertDontSee('Latest Episode</h3>', false);
});

test('podcast pages describe episodes without audio as details instead of playable media', function (): void {
    $episode = Episode::factory()->create([
        'audio_url' => null,
        'audio_path' => null,
        'transcript' => null,
    ]);

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('Episode details')
        ->assertDontSee('Listen now');

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('A transcript is not available for this episode.')
        ->assertDontSee('Transcript coming soon');
});

test('podcast index identifies episodes with a Transistor player as playable', function (): void {
    Episode::factory()->create([
        'transistor_url' => 'https://share.transistor.fm/s/428d650c',
    ]);

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('Listen now')
        ->assertDontSee('Episode details');
});

test('only currently published content is publicly visible', function (): void {
    $publishedEpisode = Episode::factory()->create(['title' => 'Published park episode']);
    $scheduledEpisode = Episode::factory()->scheduled()->create(['title' => 'Scheduled park episode']);

    get(route('episodes.show', $scheduledEpisode))->assertNotFound();
    get(route('episodes.show', $publishedEpisode))->assertOk();
});

test('legacy hosted episode audio remains available to structured data without rendering a second player', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('episodes/audio/hosted-episode.mp3', 'hosted audio');

    $episode = Episode::factory()->create([
        'audio_path' => 'episodes/audio/hosted-episode.mp3',
        'audio_url' => 'https://cdn.example.com/legacy-episode.mp3',
    ]);
    $audioUrl = Storage::disk('public')->url($episode->audio_path);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('"contentUrl":"'.$audioUrl.'"', false)
        ->assertDontSee('<audio', false)
        ->assertDontSee($episode->audio_url, false);
});

test('episode pages link to the adjacent published episodes', function (): void {
    $olderEpisode = Episode::factory()->create([
        'title' => 'Older published episode',
        'published_at' => now()->subDays(3),
    ]);
    $episode = Episode::factory()->create([
        'title' => 'Current episode',
        'published_at' => now()->subDays(2),
    ]);
    $newerEpisode = Episode::factory()->create([
        'title' => 'Newer published episode',
        'published_at' => now()->subDay(),
    ]);
    $draftEpisode = Episode::factory()->draft()->create([
        'title' => 'Private draft episode',
    ]);
    $scheduledEpisode = Episode::factory()->scheduled()->create([
        'title' => 'Future scheduled episode',
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('Previous episode')
        ->assertSee($olderEpisode->title)
        ->assertSee(route('episodes.show', $olderEpisode), false)
        ->assertSee('Next episode')
        ->assertSee($newerEpisode->title)
        ->assertSee(route('episodes.show', $newerEpisode), false)
        ->assertDontSee($draftEpisode->title)
        ->assertDontSee($scheduledEpisode->title);
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

test('episode metadata falls back to its title and description', function (): void {
    $episode = Episode::factory()->create([
        'title' => 'Trailer: Meet Mouse28',
        'description' => 'Meet Jeffrey and Cassie and learn what the Mouse28 podcast is about.',
        'cover_image' => 'episodes/trailer-meet-mouse28.webp',
        'meta_title' => null,
        'meta_description' => null,
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('<title>Trailer: Meet Mouse28 | Mouse28</title>', false)
        ->assertSee('<meta name="description" content="Meet Jeffrey and Cassie and learn what the Mouse28 podcast is about.">', false)
        ->assertSee('<meta property="og:image" content="'.url('/storage/episodes/trailer-meet-mouse28.webp').'">', false);
});

test('landing page provides search and social metadata', function (): void {
    Podcast::query()->create([
        'name' => 'Mouse28 Weekly',
        'description' => 'A weekly Disney parks podcast for accessibility-minded families.',
        'cover_image' => 'podcasts/show-cover.jpg',
    ]);

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Mouse28 Weekly Podcast">', false)
        ->assertSee('<meta property="og:description" content="A weekly Disney parks podcast for accessibility-minded families.">', false)
        ->assertSee('<meta property="og:image" content="'.url('/storage/podcasts/show-cover.jpg').'">', false);
});

test('archive canonical preserves meaningful filters and pagination', function (): void {
    Episode::factory()->count(13)->create();

    $episodeCanonical = route('episodes.index', ['page' => 2]);

    get($episodeCanonical)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e($episodeCanonical).'">', false);
});
