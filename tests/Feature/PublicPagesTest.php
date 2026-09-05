<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public index pages render', function (string $route, string $content): void {
    get(route($route))
        ->assertOk()
        ->assertSee($content);
})->with([
    'home' => ['home', 'Mouse28'],
    'about' => ['about', 'Disney looks different'],
    'blog' => ['blog.index', 'Blog'],
    'guides' => ['guides.index', 'Park guides.'],
    'podcast' => ['episodes.index', 'The Mouse28 Podcast'],
    'contact' => ['contact.show', 'Send us a note'],
]);

test('about and contact pages avoid em dashes in their copy', function (string $route): void {
    get(route($route))
        ->assertOk()
        ->assertDontSee('—');
})->with([
    'about' => ['about'],
    'contact' => ['contact.show'],
]);

test('public index and utility pages use the dispatch editorial system', function (string $route, string $marker): void {
    get(route($route))
        ->assertOk()
        ->assertSee('data-brand-wordmark', false)
        ->assertSee($marker, false)
        ->assertSee('js-dispatch-pages', false);
})->with([
    'blog archive' => ['blog.index', 'data-editorial-blog'],
    'guide archive' => ['guides.index', 'data-guide-archive'],
    'podcast archive' => ['episodes.index', 'data-podcast-archive'],
    'about' => ['about', 'data-about-editorial'],
    'contact' => ['contact.show', 'dispatch-letter-form'],
    'search' => ['search', 'dispatch-page-field'],
]);

test('primary navigation uses one consistent active-page treatment', function (): void {
    $aboutPage = get(route('about'))
        ->assertOk()
        ->assertSee('dispatch-nav-link', false)
        ->assertDontSee('nav-link-active', false);

    expect($aboutPage->getContent())
        ->toContain('href="'.route('about').'"')
        ->toContain('aria-current="page"')
        ->and(substr_count($aboutPage->getContent(), 'class="dispatch-nav-link'))
        ->toBe(5);
});

test('public reading pages use dispatch reading surfaces', function (): void {
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $episode = Episode::factory()->create();

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee('editorial-detail-hero', false)
        ->assertSee('editorial-reading-column', false);

    get(route('guides.show', $guide))
        ->assertOk()
        ->assertSee('data-guide-detail', false)
        ->assertSee('dispatch-reader-sheet', false)
        ->assertSee('guide-reading-column', false)
        ->assertDontSee('—')
        ->assertSee('/images/guides/'.$guide->category.'.webp', false);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('episode-detail-hero', false)
        ->assertSee('dispatch-page-field', false);
});

test('guide pages use category artwork when an editor has not uploaded a cover', function (): void {
    $guide = Guide::factory()->create([
        'category' => 'accessibility',
        'cover_image' => null,
    ]);

    get(route('guides.index'))
        ->assertOk()
        ->assertSee('/images/guides/accessibility.webp', false)
        ->assertSee('data-guide-artwork', false);

    get(route('guides.show', $guide))
        ->assertOk()
        ->assertSee('/images/guides/accessibility.webp', false)
        ->assertSee('fetchpriority="high"', false);
});

test('empty discovery states offer useful paths forward', function (): void {
    get(route('blog.index'))
        ->assertOk()
        ->assertSee(route('guides.index'), false)
        ->assertSee(route('episodes.index'), false);

    get(route('search'))
        ->assertOk()
        ->assertSee('Start somewhere inspiring')
        ->assertSee('Browse practical guides')
        ->assertSee('Listen to the podcast');
});

test('about page uses an editorial family story with separate host profiles', function (): void {
    get(route('about'))
        ->assertOk()
        ->assertSee('data-about-editorial', false)
        ->assertSee('Jeffrey Davidson')
        ->assertSee('Cassie Davidson')
        ->assertDontSee('Park Visits')
        ->assertDontSee('Chapter One')
        ->assertDontSee('8 years old');
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

test('homepage uses one newsletter form and responsive hero artwork', function (): void {
    $response = get(route('home'))
        ->assertOk()
        ->assertSee('/images/hero-family-640.webp 640w', false)
        ->assertSee('/images/hero-family-1024.webp 1024w', false)
        ->assertSee('dispatch-cloth', false)
        ->assertSee('dispatch-feature-book', false)
        ->assertSee('dispatch-latest-sheet', false)
        ->assertSee('dispatch-guide-spread', false)
        ->assertSee('dispatch-podcast-panel', false)
        ->assertSee('data-brand-wordmark', false)
        ->assertSee('data-dispatch-motion="hero-paper"', false)
        ->assertSee('data-dispatch-motion="hero-photo"', false)
        ->assertSee('data-dispatch-reveal="story-folio"', false)
        ->assertDontSee('data-dispatch-journey', false)
        ->assertDontSee('data-dispatch-stop', false)
        ->assertDontSee('data-animate', false)
        ->assertSee('Our first dispatch is being prepared.')
        ->assertDontSee('/storage/posts/welcome-to-mouse-28.webp', false)
        ->assertSee('We use your email to send Mouse28 updates.');

    expect(substr_count($response->getContent(), 'action="'.route('newsletter.store').'"'))->toBe(1);
});

test('homepage only presents published content as stories and guides', function (): void {
    $featuredPost = Post::factory()->create([
        'title' => 'A Real Featured Dispatch',
        'cover_image' => null,
        'published_at' => now()->subHour(),
    ]);
    $latestPost = Post::factory()->create([
        'title' => 'A Real Latest Dispatch',
        'cover_image' => null,
        'published_at' => now()->subDay(),
    ]);
    $draftPost = Post::factory()->draft()->create([
        'title' => 'A Private Draft Dispatch',
    ]);
    $guide = Guide::factory()->create([
        'title' => 'A Real Planning Guide',
        'category' => 'accessibility',
        'cover_image' => null,
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSee($featuredPost->title)
        ->assertSee($latestPost->title)
        ->assertSee($guide->title)
        ->assertSee('/images/guides/accessibility.webp', false)
        ->assertDontSee($draftPost->title)
        ->assertDontSee('Accessibility planning')
        ->assertDontSee('Sensory-friendly park days')
        ->assertDontSee('Honest family stories')
        ->assertDontSee('/storage/posts/our-disney-park-bag-essentials.webp', false)
        ->assertDontSee('/storage/posts/the-ride-that-surprised-us.webp', false)
        ->assertDontSee('/storage/posts/disney-dining-with-a-picky-eater.webp', false);
});

test('homepage turns an empty guide shelf into useful planning stories', function (): void {
    $planningPost = Post::factory()->create([
        'title' => 'Plan a Calmer Park Morning',
        'category' => 'park-accessibility',
        'cover_image' => null,
        'published_at' => now()->subDay(),
    ]);
    $draftPlanningPost = Post::factory()->draft()->create([
        'title' => 'Private Planning Notes',
        'category' => 'disney-tips',
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSee('Start planning with these stories')
        ->assertSee('Explore planning stories')
        ->assertSee($planningPost->title)
        ->assertSee('data-post-artwork-fallback', false)
        ->assertDontSee('Browse all guides')
        ->assertDontSee($draftPlanningPost->title);
});

test('public content pages present one newsletter signup', function (): void {
    $post = Post::factory()->create();
    $episode = Episode::factory()->create();

    foreach ([
        route('blog.index'),
        route('blog.show', $post),
        route('episodes.index'),
        route('episodes.show', $episode),
    ] as $url) {
        $response = get($url)->assertOk();

        expect(substr_count($response->getContent(), 'action="'.route('newsletter.store').'"'))->toBe(1);
    }
});

test('blog discovery controls precede results in the document order', function (): void {
    Post::factory()->create([
        'title' => 'Featured Story',
        'published_at' => now()->subHour(),
    ]);
    $post = Post::factory()->create([
        'title' => 'Archive Story',
        'published_at' => now()->subDay(),
    ]);

    get(route('blog.index'))
        ->assertOk()
        ->assertSeeInOrder(['data-blog-filters', 'data-blog-results', $post->title]);
});

test('blog index uses an artwork led archive without dashboard widgets', function (): void {
    Post::factory()->count(3)->create([
        'cover_image' => null,
    ]);

    get(route('blog.index'))
        ->assertOk()
        ->assertSee('data-editorial-blog', false)
        ->assertSee('editorial-feature', false)
        ->assertSee('editorial-story-grid', false)
        ->assertSee('data-equal-width-stories', false)
        ->assertSee('data-post-artwork', false)
        ->assertDontSee('Blog Stats')
        ->assertDontSee('Categories</h3>', false);
});

test('homepage defers the below-fold featured post image', function (): void {
    Post::factory()->create([
        'cover_image' => 'posts/featured.webp',
    ]);

    $response = get(route('home'))
        ->assertOk();

    expect($response->getContent())->toMatch(
        '/<img[^>]*src="\/storage\/posts\/featured\.webp"[^>]*loading="lazy"[^>]*decoding="async"[^>]*>/',
    );
});

test('public forms use readable placeholder text colors', function (): void {
    Post::factory()->create();
    config()->set('services.turnstile.site_key', 'test-site-key');
    config()->set('services.turnstile.secret_key', 'test-secret-key');

    get(route('blog.index'))
        ->assertOk()
        ->assertSee('placeholder:text-navy/60', false)
        ->assertSee('placeholder:text-white/60', false)
        ->assertDontSee('placeholder:text-navy/25', false)
        ->assertDontSee('placeholder:text-white/25', false)
        ->assertDontSee('placeholder-white/', false);

    get(route('contact.show'))
        ->assertOk()
        ->assertSee('placeholder:text-navy/65', false)
        ->assertDontSee('placeholder:text-navy/30', false);
});

test('published post detail page renders', function (): void {
    $post = Post::query()->create([
        'title' => 'An Accessible Day at the Parks',
        'slug' => 'accessible-day-at-the-parks',
        'excerpt' => 'A practical guide for planning a comfortable park day.',
        'body' => 'Start with a flexible plan and make room for sensory breaks.',
        'category' => 'park-accessibility',
        'author' => 'jeffrey',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('Start with a flexible plan', false)
        ->assertSee('editorial-reading-column', false)
        ->assertDontSee('data-article-secondary', false)
        ->assertSee('id="back-to-top"', false)
        ->assertSee('aria-hidden="true"', false)
        ->assertSee('tabindex="-1"', false)
        ->assertSee('inline-flex size-12 items-center justify-center rounded-full', false)
        ->assertDontSee('inline-flex size-11', false);
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
        'audio_url' => 'https://cdn.example.com/planning-a-sensory-friendly-visit.mp3',
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
        ->assertSee('aria-label="Play Planning a Sensory-Friendly Visit"', false)
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

test('podcast index identifies episodes with hosted audio as playable', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('episodes/audio/available.mp3', 'audio');

    Episode::factory()->create([
        'audio_path' => 'episodes/audio/available.mp3',
    ]);

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('Listen now')
        ->assertDontSee('Episode details');
});
