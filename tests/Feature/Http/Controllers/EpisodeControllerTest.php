<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Support\ResponsiveArtwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('episode index returns its view model data', function (): void {
    get(route('episodes.index'))
        ->assertOk()
        ->assertViewIs('pages.episodes.index')
        ->assertViewHas('episodes')
        ->assertViewHas('podcast')
        ->assertViewHas('podcastLinks')
        ->assertViewHas('canonicalUrl');
});

test('published episode returns its view model data', function (): void {
    $episode = Episode::factory()->create();

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertViewIs('pages.episodes.show')
        ->assertViewHas('episode', fn (Episode $viewEpisode): bool => $viewEpisode->is($episode))
        ->assertViewHas('podcast')
        ->assertViewHas('relatedPosts');
});

test('episode artwork uses available responsive candidates and falls back after replacement', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $contents = UploadedFile::fake()->image('cover.png', 800, 800)->getContent();
    $disk->put('episodes/cover.png', $contents);
    $variant = ResponsiveArtwork::variantPath(hash('sha256', $contents), 768, square: true);
    $disk->put($variant, 'generated image');
    $episode = Episode::factory()->create(['cover_image' => 'episodes/cover.png']);

    get(route('episodes.show', $episode))->assertOk()->assertSeeHtml('srcset="'.$disk->url($variant).' 768w"')->assertSeeHtml('fetchpriority="high"')->assertSeeHtml('sizes="(min-width: 1188px) 448px, (min-width: 1024px) calc(41.6667vw - 46.6667px), (min-width: 480px) 448px, calc(100vw - 32px)"');

    $disk->put('episodes/cover.png', UploadedFile::fake()->image('replacement.png', 801, 800)->getContent());

    get(route('episodes.show', $episode))->assertOk()->assertSeeHtml('src="/storage/episodes/cover.png"')->assertDontSeeHtml($disk->url($variant));
});

test('podcast pages stay within their query budget as content grows', function (string $page, int $queries): void {
    $episode = Episode::factory()->create();
    Episode::factory()->count(15)->create();
    $url = $page === 'index' ? route('episodes.index') : route('episodes.show', $episode);

    $this->expectsDatabaseQueryCount($queries);

    get($url)
        ->assertOk();
})->with(['archive' => ['index', 3], 'episode' => ['show', 5]]);

test('public index page renders', function (): void {
    get(route('episodes.index'))
        ->assertOk()->assertSee('The Mouse28 Podcast')->assertSeeHtml('src="/images/podcast/mouse28-cover.webp"');
});

test('podcast pages render one newsletter signup', function (): void {
    $episode = Episode::factory()->create();

    foreach ([route('episodes.index'), route('episodes.show', $episode)] as $url) {
        $response = get($url)->assertOk()->assertSeeHtml('id="footer-newsletter-email"')
            ->assertSee('Connect');

        expect(substr_count($this->responseContent($response), 'action="'.route('newsletter.store').'"'))->toBe(1);
    }
});

test('podcast index advertises the canonical Transistor feed without persisting defaults', function (): void {
    get(route('episodes.index'))->assertOk()->assertSeeHtml(config()->string('podcast.rss_url'));

    expect(Podcast::query()->doesntExist())->toBeTrue();
});

test('podcast index renders configured distribution links', function (): void {
    $links = [
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => 'https://open.spotify.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
    ];
    Podcast::query()->create(['name' => 'Mouse28', ...$links]);

    $response = get(route('episodes.index'))
        ->assertOk();

    foreach ($links as $url) {
        $response->assertSeeHtml($url);
    }

    $response->assertSeeHtml(config()->string('podcast.rss_url'))
        ->assertDontSee('Apple Podcasts · Soon')
        ->assertDontSee('Spotify · Soon');
});

test('episode pages use concise public-facing labels', function (): void {
    $episode = Episode::factory()->create([
        'episode_number' => 28,
    ]);

    get(route('episodes.show', $episode))->assertOk()->assertDontSeeHtml('animate-pulse')->assertSeeHtml('data-episode-meta');
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
        ->assertOk()->assertSee($episode->title)->assertSeeHtml('Our favorite planning strategies')->assertSeeHtml('episode-detail-hero')
        ->assertSee('Listen to this episode')->assertDontSee('Now Playing')->assertSeeHtml('title="Listen to Planning a Sensory-Friendly Visit"')->assertSeeHtml('data-episode-layout="rich"')->assertSeeHtml('id="episode-transcript"')->assertSeeHtml('aria-controls="episode-transcript"')->assertSeeHtml(':aria-expanded="expanded.toString()"');
});

test('episode pages sanitize rich show notes and transcripts', function (): void {
    $episode = Episode::factory()->create([
        'show_notes' => '<p>Safe show notes.</p><script>alert("show notes")</script>',
        'transcript' => '<p>Safe transcript.</p><img src="x" onerror="alert(\'transcript\')">',
    ]);

    get(route('episodes.show', $episode))->assertOk()->assertSeeHtml('<p>Safe show notes.</p>')->assertSeeHtml('<p>Safe transcript.</p>')->assertDontSeeHtml('alert("show notes")')->assertDontSeeHtml('onerror=');
});

test('sparse episode detail pages use a compact continuation layout', function (): void {
    $episode = Episode::factory()->create([
        'audio_url' => null,
        'audio_path' => null,
        'show_notes' => '<p>Coming soon.</p>',
        'transcript' => null,
    ]);
    $publishedAt = $episode->published_at ?? throw new UnexpectedValueException('The episode is not published.');
    $previousEpisode = Episode::factory()->create([
        'published_at' => $publishedAt->subDay(),
    ]);

    get(route('episodes.show', $episode))->assertOk()->assertSeeHtml('data-episode-layout="sparse"')->assertSeeHtml('data-episode-continuation="compact"')
        ->assertSee($previousEpisode->title);
});

test('empty podcast page uses a truthful show introduction without a decorative player', function (): void {
    get(route('episodes.index'))->assertOk()->assertSeeHtml("We're warming up the mics")->assertDontSeeHtml('podcast-player-preview');
});

test('podcast index leads with the show and uses a season tracklist', function (): void {
    Episode::factory()->count(2)->create([
        'season_number' => 1,
    ]);

    get(route('episodes.index'))->assertOk()->assertSeeHtml('data-podcast-archive')->assertSeeHtml('podcast-cover-frame')->assertSeeHtml('podcast-ledger')
        ->assertSee('Episode archive')->assertDontSee('Show Stats')->assertDontSeeHtml('Latest Episode</h3>');
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
        ->assertSee('Previous episode')->assertSee($olderEpisode->title)->assertSeeHtml(route('episodes.show', $olderEpisode))
        ->assertSee('Next episode')->assertSee($newerEpisode->title)->assertSeeHtml(route('episodes.show', $newerEpisode))
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

    get(route('episodes.show', $episode))->assertOk()->assertSeeHtml('https://podcasts.apple.com/episode/42')->assertSee('Listen to this episode')->assertSeeHtml('https://open.spotify.com/show/mouse28')->assertSee('Visit the show')->assertSeeHtml('https://youtube.com/@mouse28')->assertSee('Visit the channel')->assertSeeHtml(config()->string('podcast.rss_url'))->assertSeeHtml('"name":"Mouse28 Travel Podcast"');
});

test('episode pages hide podcast platforms that are not configured', function (): void {
    $episode = Episode::factory()->create();

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertDontSee('Apple Podcasts')
        ->assertDontSee('Spotify')->assertDontSee('Not configured')->assertSeeHtml(config()->string('podcast.rss_url'));
});

test('episode pages embed only valid Transistor share URLs', function (): void {
    $episode = Episode::factory()->create([
        'transistor_url' => 'https://share.transistor.fm/s/428d650c',
    ]);

    get(route('episodes.show', $episode))->assertOk()->assertSeeHtml('src="https://share.transistor.fm/e/428d650c"')->assertSeeHtml('title="Listen to '.$episode->title.'"')
        ->assertSee('Open in a new tab')
        ->assertSee('Listen elsewhere');

    $episode->update(['transistor_url' => 'https://example.com/not-a-transistor-player']);

    get(route('episodes.show', $episode->fresh()))->assertOk()->assertDontSeeHtml('https://example.com/not-a-transistor-player')->assertSee('Listen elsewhere')->assertDontSeeHtml('<iframe');
});

test('episode metadata falls back to its title and description', function (): void {
    $episode = Episode::factory()->create([
        'title' => 'Trailer: Meet Mouse28',
        'description' => 'Meet Jeffrey and Cassie and learn what the Mouse28 podcast is about.',
        'cover_image' => 'episodes/trailer-meet-mouse28.webp',
        'meta_title' => null,
        'meta_description' => null,
    ]);

    get(route('episodes.show', $episode))->assertOk()->assertSeeHtml('<title>Trailer: Meet Mouse28 | Mouse28</title>')->assertSeeHtml('<meta name="description" content="Meet Jeffrey and Cassie and learn what the Mouse28 podcast is about.">')->assertSeeHtml('<meta property="og:image" content="'.url('/storage/episodes/trailer-meet-mouse28.webp').'">');
});

test('landing page provides search and social metadata', function (): void {
    Podcast::query()->create([
        'name' => 'Mouse28 Weekly',
        'description' => 'A weekly Disney parks podcast for accessibility-minded families.',
        'cover_image' => 'podcasts/show-cover.jpg',
    ]);

    get(route('episodes.index'))->assertOk()->assertSeeHtml('<meta property="og:title" content="Mouse28 Weekly Podcast">')->assertSeeHtml('<meta property="og:description" content="A weekly Disney parks podcast for accessibility-minded families.">')->assertSeeHtml('<meta property="og:image" content="'.url('/storage/podcasts/show-cover.jpg').'">');
});

test('archive canonical preserves meaningful filters and pagination', function (): void {
    Episode::factory()->count(13)->create();

    $episodeCanonical = route('episodes.index', ['page' => 2]);

    get($episodeCanonical)->assertOk()->assertSeeHtml('<link rel="canonical" href="'.e($episodeCanonical).'">');
});

test('episodes include podcast media duration and breadcrumb structured data', function (): void {
    $episode = Episode::factory()->create([
        'season_number' => 3,
        'duration_seconds' => 3723,
        'audio_url' => 'https://cdn.example.com/episode.mp3',
    ]);

    $response = get(route('episodes.show', $episode));

    $response->assertOk();
    $data = $this->structuredData($response);
    $podcastEpisode = data_get($data, '@graph.0');

    expect(data_get($podcastEpisode, '@type'))->toBe('PodcastEpisode')
        ->and(data_get($podcastEpisode, 'duration'))->toBe('PT1H2M3S')
        ->and(data_get($podcastEpisode, 'associatedMedia.contentUrl'))->toBe($episode->audio_url)
        ->and(data_get($podcastEpisode, 'partOfSeason.@type'))->toBe('PodcastSeason')
        ->and(data_get($podcastEpisode, 'partOfSeason.seasonNumber'))->toBe(3)
        ->and(data_get($podcastEpisode, 'partOfSeries.@type'))->toBe('PodcastSeries')
        ->and(data_get($data, '@graph.1.itemListElement.1.name'))->toBe('Podcast');
});

test('page copy and metadata avoid em dashes', function (): void {
    get(route('episodes.index'))
        ->assertOk()
        ->assertDontSee('—');
});

test('page uses the dispatch editorial system', function (): void {
    get(route('episodes.index'))->assertOk()->assertSeeHtml('data-brand-wordmark')->assertSeeHtml('data-podcast-archive')->assertSeeHtml('js-dispatch-pages');
});

test('reading page uses the dispatch reading surface', function (): void {
    $episode = Episode::factory()->create();

    get(route('episodes.show', $episode))->assertOk()->assertSeeHtml('episode-detail-hero')->assertSeeHtml('dispatch-page-field')
        ->assertDontSee('—');
});
