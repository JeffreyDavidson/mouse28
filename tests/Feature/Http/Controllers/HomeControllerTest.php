<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public index page renders', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee('Mouse28');
});

test('homepage renders configured podcast distribution links', function (): void {
    $podcast = Podcast::query()->create([
        'name' => 'Mouse28',
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => 'https://open.spotify.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
    ]);

    $response = get(route('home'))
        ->assertOk();

    foreach ([$podcast->apple_url, $podcast->spotify_url, $podcast->youtube_url] as $url) {
        $response->assertSee($url, false);
    }

    $response->assertSee(config('podcast.rss_url'), false)
        ->assertDontSee('Apple Podcasts · Soon')
        ->assertDontSee('Spotify · Soon');
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

test('homepage uses the documented content order without community stories', function (): void {
    Post::factory()->count(2)->create();
    Guide::factory()->create(['title' => 'Sensory planning guide']);
    Episode::factory()->create();

    get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            'Latest from the Blog',
            'Latest Stories',
            'Your Guide to the Parks',
            'From the Podcast',
            'Meet Jeffrey & Cassie',
            'Stay in the Loop',
        ])
        ->assertDontSee('Community Stories');
});

test('landing page provides search and social metadata', function (): void {
    Podcast::query()->create([
        'name' => 'Mouse28 Weekly',
        'description' => 'A weekly Disney parks podcast for accessibility-minded families.',
        'cover_image' => 'podcasts/show-cover.jpg',
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSee('<meta name="description" content="Accessibility tips, sensory-friendly park planning, family experiences, and the Mouse28 podcast from Jeffrey and Cassie Davidson.">', false)
        ->assertSee('<meta name="theme-color" content="#1a1040" />', false)
        ->assertSee('<link rel="preload" href="/fonts/mouse28/poppins-400.woff2" as="font" type="font/woff2" crossorigin />', false)
        ->assertSee('<link rel="preload" href="/fonts/mouse28/besley-latin.woff2" as="font" type="font/woff2" crossorigin />', false)
        ->assertSee('<meta property="og:image" content="'.url('/images/hero-family.jpg').'">', false);
});

test('homepage newsletter form renders bot protection', function (): void {
    config()->set('services.resend.key', 'resend-test-key');
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.turnstile.site_key', 'turnstile-test-site-key');
    config()->set('services.turnstile.secret_key', 'turnstile-test-secret-key');
    config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    config()->set('services.turnstile.newsletter_action', 'newsletter');
    config()->set('services.turnstile.allowed_hostnames', ['mouse28.com']);

    get(route('home'))
        ->assertOk()
        ->assertSee('data-action="newsletter"', false)
        ->assertSee('data-appearance="interaction-only"', false)
        ->assertSee('name="website_url"', false);
});
