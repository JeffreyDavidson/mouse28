<?php

use App\Enums\SocialPlatform;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\SocialProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('homepage returns its view model data', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertViewIs('pages.home')
        ->assertViewHas('featuredPost')
        ->assertViewHas('latestPosts')
        ->assertViewHas('latestEpisodes')
        ->assertViewHas('latestGuides')
        ->assertViewHas('planningPosts');
});

test('homepage stays within its query budget as content grows', function (): void {
    config()->set('mouse28.guides_enabled', false);
    Post::factory()
        ->count(30)
        ->create();
    Episode::factory()
        ->count(15)
        ->create();

    // Includes one query for the footer social links and one for the post categories.
    $this->expectsDatabaseQueryCount(5);

    get(route('home'))
        ->assertOk();
});

test('homepage renders', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee('Mouse28');
});

test('homepage advertises the canonical Transistor feed without persisting defaults', function (): void {
    get(route('home'))->assertOk()
        ->assertSeeHtml(config()->string('podcast.rss_url'))
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
    $response = get(route('home'))->assertOk()
        ->assertSeeHtml('/images/hero-family-640.webp 640w')
        ->assertSeeHtml('/images/hero-family-768.webp 768w')
        ->assertSeeHtml('/images/hero-family-1024.webp 1024w')
        ->assertSeeHtml('dispatch-cloth')
        ->assertSeeHtml('dispatch-feature-book')
        ->assertSeeHtml('dispatch-latest-sheet')
        ->assertSeeHtml('dispatch-guide-spread')
        ->assertSeeHtml('dispatch-podcast-panel')
        ->assertSeeHtml('data-brand-wordmark')
        ->assertSeeHtml('data-dispatch-motion="hero-paper"')
        ->assertSeeHtml('data-dispatch-motion="hero-photo"')
        ->assertSeeHtml('data-dispatch-reveal="story-folio"')
        ->assertDontSeeHtml('data-dispatch-journey')
        ->assertDontSeeHtml('data-dispatch-stop')
        ->assertDontSeeHtml('data-animate')
        ->assertSee('Our first dispatch is being prepared.')
        ->assertDontSeeHtml('/storage/posts/welcome-to-mouse-28.webp')
        ->assertSee('We email you a link to confirm. Your address is only used for Mouse28 updates.')
        ->assertSeeHtml('id="footer-newsletter-email"')
        ->assertSee('Connect')
        ->assertDontSeeHtml('id="home-newsletter-email"');

    expect(substr_count($this->responseContent($response), 'action="'.route('newsletter.subscribe').'"'))->toBe(1);
});

test('homepage preloads responsive AVIF hero artwork with a WebP fallback', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSeeHtml('rel="preload"')
        ->assertSeeHtml('href="/images/hero-family-1600.avif"')
        ->assertSeeHtml('as="image"')
        ->assertSeeHtml('type="image/avif"')
        ->assertSeeHtml('/images/hero-family-768.avif 768w')
        ->assertSeeHtml('type="image/webp"')
        ->assertSeeHtml('/images/hero-family-768.webp 768w');

    foreach ([640, 768, 1024, 1600] as $width) {
        expect(public_path("images/hero-family-{$width}.avif"))->toBeFile();
    }
});

test('homepage offers a smaller bundled podcast cover without replacing the original', function (): void {
    get(route('home'))->assertOk()
        ->assertSeeHtml('srcset="/images/podcast/mouse28-cover-640.webp 640w, /images/podcast/mouse28-cover.webp 1200w"')
        ->assertSeeHtml('sizes="auto, 264px"');

    $candidate = public_path('images/podcast/mouse28-cover-640.webp');
    $original = public_path('images/podcast/mouse28-cover.webp');

    expect($candidate)->toBeFile()
        ->and($original)
        ->toBeFile();

    $dimensions = getimagesize($candidate) ?: throw new UnexpectedValueException('The podcast cover is not an image.');
    $candidateSize = filesize($candidate);
    $originalSize = filesize($original);

    if ($candidateSize === false || $originalSize === false) {
        throw new UnexpectedValueException('The podcast cover sizes could not be read.');
    }

    expect([$dimensions[0], $dimensions[1], $dimensions['mime']])->toBe([640, 640, 'image/webp'])
        ->and($candidateSize)
        ->toBeLessThan($originalSize);
});

test('homepage only presents published content as stories and guides', function (): void {
    $featuredPost = Post::factory()->create([
        'title' => 'A Real Featured Dispatch',
        'featured_image_path' => null,
        'published_at' => now()->subHour(),
    ]);
    $latestPost = Post::factory()->create([
        'title' => 'A Real Latest Dispatch',
        'featured_image_path' => null,
        'published_at' => now()->subDay(),
    ]);
    $draftPost = Post::factory()
        ->draft()
        ->create([
            'title' => 'A Private Draft Dispatch',
        ]);
    $guide = Guide::factory()->create([
        'title' => 'A Real Planning Guide',
        'category' => 'accessibility',
        'featured_image_path' => null,
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSee($featuredPost->title)
        ->assertSee($latestPost->title)
        ->assertSee($guide->title)
        ->assertSeeHtml('/images/guides/accessibility.webp')
        ->assertDontSee($draftPost->title)
        ->assertDontSee('Accessibility planning')
        ->assertDontSee('Sensory-friendly park days')
        ->assertDontSee('Honest family stories')
        ->assertDontSeeHtml('/storage/posts/our-disney-park-bag-essentials.webp')
        ->assertDontSeeHtml('/storage/posts/the-ride-that-surprised-us.webp')
        ->assertDontSeeHtml('/storage/posts/disney-dining-with-a-picky-eater.webp');
});

test('homepage turns an empty guide shelf into useful planning stories', function (): void {
    $planningPost = Post::factory()
        ->inCategory('park-accessibility')
        ->create([
            'title' => 'Plan a Calmer Park Morning',
            'featured_image_path' => null,
            'published_at' => now()->subDay(),
        ]);
    $draftPlanningPost = Post::factory()
        ->inCategory('disney-tips')
        ->draft()
        ->create([
            'title' => 'Private Planning Notes',
        ]);

    get(route('home'))
        ->assertOk()
        ->assertSee('Start planning with these stories')
        ->assertSee('Explore planning stories')
        ->assertSee($planningPost->title)
        ->assertSeeHtml('data-post-artwork-fallback')
        ->assertDontSee('Browse all guides')
        ->assertDontSee($draftPlanningPost->title);
});

test('homepage defers the below-fold featured post image', function (): void {
    Post::factory()->create([
        'featured_image_path' => 'posts/featured.webp',
    ]);

    $response = get(route('home'))
        ->assertOk();

    expect($this->responseContent($response))->toMatch(
        '/<img[^>]*src="[^"]*\/storage\/posts\/featured\.webp"[^>]*loading="lazy"[^>]*decoding="async"[^>]*>/',
    );
});

test('homepage uses the documented content order without community stories', function (): void {
    Post::factory()
        ->count(2)
        ->create();
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
        'cover_image_path' => 'podcasts/show-cover.jpg',
    ]);

    get(route('home'))->assertOk()
        ->assertSeeHtml('<meta name="description" content="Accessibility tips, sensory-friendly park planning, family experiences, and the Mouse28 podcast from Jeffrey and Cassie Davidson.">')
        ->assertSeeHtml('<meta name="theme-color" content="#1a1040" />')
        ->assertSeeHtml('<link rel="preload" href="/fonts/mouse28/poppins-400.woff2" as="font" type="font/woff2" crossorigin />')
        ->assertSeeHtml('<link rel="preload" href="/fonts/mouse28/besley-latin.woff2" as="font" type="font/woff2" crossorigin />')
        ->assertSeeHtml('<meta property="og:image" content="'.url('/images/hero-family.jpg').'">');
});

test('homepage newsletter form renders bot protection', function (): void {
    config()->set('services.resend.key', 'resend-test-key');
    config()->set('services.turnstile.site_key', 'turnstile-test-site-key');
    config()->set('services.turnstile.secret_key', 'turnstile-test-secret-key');
    config()->set('services.turnstile.siteverify_url', 'https://challenges.cloudflare.com/turnstile/v0/siteverify');
    config()->set('services.turnstile.newsletter_action', 'newsletter');
    config()->set('services.turnstile.allowed_hostnames', ['mouse28.com']);

    get(route('home'))->assertOk()
        ->assertSeeHtml('data-action="newsletter"')
        ->assertSeeHtml('data-appearance="interaction-only"')
        ->assertSeeHtml('name="website_url"');
});

test('homepage links that open a new tab announce it', function (): void {
    $response = get(route('home'))->assertOk();

    expect($this->unannouncedNewTabLinks($response))->toBeEmpty();
});

test('the footer lists enabled footer profiles as external links', function (): void {
    SocialProfile::factory()->create([
        'platform' => SocialPlatform::Instagram,
        'url' => 'https://instagram.com/mouse28',
        'sort_order' => 10,
    ]);
    SocialProfile::factory()->create([
        'platform' => SocialPlatform::TikTok,
        'url' => 'https://tiktok.com/@mouse28',
        'label' => '@mouse28',
        'sort_order' => 20,
    ]);
    SocialProfile::factory()->create(['url' => 'https://hidden.example.com/off', 'is_enabled' => false]);
    SocialProfile::factory()->create(['url' => 'https://hidden.example.com/contact-only', 'show_in_footer' => false]);

    get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Instagram', 'TikTok'])
        ->assertSeeHtml('href="https://instagram.com/mouse28"')
        ->assertSeeHtml('href="https://tiktok.com/@mouse28"')
        ->assertSeeHtml('rel="noopener noreferrer"')
        ->assertDontSee(['hidden.example.com']);
});

test('the footer shows no social links when none are configured', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertDontSee('Follow');
});

test('the home page head keeps its canonical slash, social tags and feeds', function (): void {
    // Act
    $response = get(route('home'));

    // Assert
    $response->assertOk()
        ->assertSeeHtml('<link rel="canonical" href="'.url('/').'/">')
        ->assertSeeHtml('<meta name="robots" content="index,follow">')
        ->assertSeeHtml('<meta property="og:site_name" content="Mouse28">')
        ->assertSeeHtml('<meta property="og:type" content="website">')
        ->assertSeeHtml('<meta name="twitter:card" content="summary_large_image">')
        ->assertSeeHtml('<meta property="og:image:alt"')
        ->assertSeeHtml('<meta name="twitter:image:alt"')
        ->assertSeeHtml('type="application/rss+xml" title="Mouse28 Blog"')
        ->assertDontSeeHtml('property="og:locale"');
});

test('homepage shows an evening post date on its Eastern day', function (): void {
    $this->travelTo('2026-10-10 12:00:00');
    Post::factory()->create(['published_at' => '2026-10-09 12:00:00']);
    Post::factory()->create(['published_at' => '2026-10-06 01:00:00']);

    get(route('home'))
        ->assertOk()
        ->assertSee('Oct 5, 2026')
        ->assertDontSee('Oct 6, 2026');
});
