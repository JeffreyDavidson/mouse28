<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('homepage stays within its query budget as content grows', function (): void {
    config()->set('mouse28.guides_enabled', false);
    Post::factory()->count(30)->create();
    Episode::factory()->count(15)->create();

    $this->expectsDatabaseQueryCount(3);

    get(route('home'))
        ->assertOk();
});

test('public index page renders', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee('Mouse28');
});

test('homepage advertises the canonical Transistor feed without persisting defaults', function (): void {
    get(route('home'))->assertOk()->assertSeeHtml(config()->string('podcast.rss_url'))
        ->assertSee('RSS Feed');

    expect(Podcast::query()->doesntExist())->toBeTrue();
});

test('homepage renders configured podcast distribution links', function (): void {
    $links = [
        'apple_url' => 'https://podcasts.apple.com/show/mouse28',
        'spotify_url' => 'https://open.spotify.com/show/mouse28',
        'youtube_url' => 'https://youtube.com/@mouse28',
    ];
    Podcast::query()->create(['name' => 'Mouse28', ...$links]);

    $response = get(route('home'))
        ->assertOk();

    foreach ($links as $url) {
        $response->assertSeeHtml($url);
    }

    $response->assertSeeHtml(config()->string('podcast.rss_url'))
        ->assertDontSee('Apple Podcasts · Soon')
        ->assertDontSee('Spotify · Soon');
});

test('homepage uses one newsletter form and responsive hero artwork', function (): void {
    $response = get(route('home'))->assertOk()->assertSeeHtml('/images/hero-family-640.webp 640w')->assertSeeHtml('/images/hero-family-768.webp 768w')->assertSeeHtml('/images/hero-family-1024.webp 1024w')->assertSeeHtml('dispatch-cloth')->assertSeeHtml('dispatch-feature-book')->assertSeeHtml('dispatch-latest-sheet')->assertSeeHtml('dispatch-guide-spread')->assertSeeHtml('dispatch-podcast-panel')->assertSeeHtml('data-brand-wordmark')->assertSeeHtml('data-dispatch-motion="hero-paper"')->assertSeeHtml('data-dispatch-motion="hero-photo"')->assertSeeHtml('data-dispatch-reveal="story-folio"')->assertDontSeeHtml('data-dispatch-journey')->assertDontSeeHtml('data-dispatch-stop')->assertDontSeeHtml('data-animate')->assertSee('Our first dispatch is being prepared.')->assertDontSeeHtml('/storage/posts/welcome-to-mouse-28.webp')->assertSee('We use your email to send Mouse28 updates.')->assertSeeHtml('id="footer-newsletter-email"')->assertSee('Connect')->assertDontSeeHtml('id="home-newsletter-email"');

    expect(substr_count($this->responseContent($response), 'action="'.route('newsletter.store').'"'))->toBe(1);
});

test('homepage offers a smaller bundled podcast cover without replacing the original', function (): void {
    get(route('home'))->assertOk()->assertSeeHtml('srcset="/images/podcast/mouse28-cover-640.webp 640w, /images/podcast/mouse28-cover.webp 1200w"')->assertSeeHtml('sizes="auto, 264px"');

    $candidate = public_path('images/podcast/mouse28-cover-640.webp');
    $original = public_path('images/podcast/mouse28-cover.webp');

    expect($candidate)->toBeFile()
        ->and($original)->toBeFile();

    $dimensions = getimagesize($candidate) ?: throw new UnexpectedValueException('The podcast cover is not an image.');
    $candidateSize = filesize($candidate);
    $originalSize = filesize($original);

    if ($candidateSize === false || $originalSize === false) {
        throw new UnexpectedValueException('The podcast cover sizes could not be read.');
    }

    expect([$dimensions[0], $dimensions[1], $dimensions['mime']])->toBe([640, 640, 'image/webp'])
        ->and($candidateSize)->toBeLessThan($originalSize);
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
        ->assertSee($latestPost->title)->assertSee($guide->title)->assertSeeHtml('/images/guides/accessibility.webp')
        ->assertDontSee($draftPost->title)
        ->assertDontSee('Accessibility planning')
        ->assertDontSee('Sensory-friendly park days')->assertDontSee('Honest family stories')->assertDontSeeHtml('/storage/posts/our-disney-park-bag-essentials.webp')->assertDontSeeHtml('/storage/posts/the-ride-that-surprised-us.webp')->assertDontSeeHtml('/storage/posts/disney-dining-with-a-picky-eater.webp');
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
        ->assertSee('Explore planning stories')->assertSee($planningPost->title)->assertSeeHtml('data-post-artwork-fallback')
        ->assertDontSee('Browse all guides')
        ->assertDontSee($draftPlanningPost->title);
});

test('homepage defers the below-fold featured post image', function (): void {
    Post::factory()->create([
        'cover_image' => 'posts/featured.webp',
    ]);

    $response = get(route('home'))
        ->assertOk();

    expect($this->responseContent($response))->toMatch(
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

    get(route('home'))->assertOk()->assertSeeHtml('<meta name="description" content="Accessibility tips, sensory-friendly park planning, family experiences, and the Mouse28 podcast from Jeffrey and Cassie Davidson.">')->assertSeeHtml('<meta name="theme-color" content="#1a1040" />')->assertSeeHtml('<link rel="preload" href="/fonts/mouse28/poppins-400.woff2" as="font" type="font/woff2" crossorigin />')->assertSeeHtml('<link rel="preload" href="/fonts/mouse28/besley-latin.woff2" as="font" type="font/woff2" crossorigin />')->assertSeeHtml('<meta property="og:image" content="'.url('/images/hero-family.jpg').'">');
});

test('homepage newsletter form renders bot protection', function (): void {
    config()->set('services.resend.key', 'resend-test-key');
    config()->set('services.resend.audience_id', 'audience-test-id');
    config()->set('services.turnstile.site_key', 'turnstile-test-site-key');
    config()->set('services.turnstile.secret_key', 'turnstile-test-secret-key');
    config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    config()->set('services.turnstile.newsletter_action', 'newsletter');
    config()->set('services.turnstile.allowed_hostnames', ['mouse28.com']);

    get(route('home'))->assertOk()->assertSeeHtml('data-action="newsletter"')->assertSeeHtml('data-appearance="interaction-only"')->assertSeeHtml('name="website_url"');
});
