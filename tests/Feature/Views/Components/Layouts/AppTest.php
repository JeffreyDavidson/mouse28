<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Dom\HTMLDocument;
use Dom\XPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public landing pages avoid em dashes in their copy and metadata', function (string $route): void {
    get(route($route))
        ->assertOk()
        ->assertDontSee('—');
})->with([
    'about' => ['about'],
    'contact' => ['contact.show'],
    'blog' => ['blog.index'],
    'guides' => ['guides.index'],
    'podcast' => ['episodes.index'],
    'search' => ['search'],
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

test('primary navigation identifies only the current destination', function (): void {
    foreach (['about' => 'About', 'blog.index' => 'Blog'] as $route => $label) {
        $response = get(route($route));

        $response->assertOk();

        $document = HTMLDocument::createFromString($response->getContent(), LIBXML_NOERROR);
        $xpath = new XPath($document);
        $links = $xpath->query('//*[local-name()="a"][contains(concat(" ", normalize-space(@class), " "), " dispatch-nav-link ") and @aria-current="page"]');

        expect($links)->toHaveCount(1)
            ->and(trim($links->item(0)->textContent))->toBe($label)
            ->and($links->item(0)->getAttribute('href'))->toBe(route($route));
    }
});

test('public reading pages use dispatch reading surfaces', function (): void {
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $episode = Episode::factory()->create();

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee('editorial-detail-hero', false)
        ->assertSee('editorial-reading-column', false)
        ->assertDontSee('—');

    get(route('guides.show', $guide))
        ->assertOk()
        ->assertSee('data-guide-detail', false)
        ->assertSee('dispatch-reader-sheet', false)
        ->assertSee('guide-reading-column', false)
        ->assertDontSee('—')
        ->assertSee('/images/guides/'.$guide->category->value.'.webp', false);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('episode-detail-hero', false)
        ->assertSee('dispatch-page-field', false)
        ->assertDontSee('—');
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

test('relative content images become absolute social image urls', function (): void {
    $post = Post::factory()->create([
        'title' => 'Accessible Disney Planning',
        'og_image' => 'posts/social-card.jpg',
    ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee('<meta property="og:image" content="'.url('/storage/posts/social-card.jpg').'">', false)
        ->assertSee('<meta property="og:image:alt" content="Accessible Disney Planning">', false)
        ->assertSee('<meta name="twitter:image" content="'.url('/storage/posts/social-card.jpg').'">', false)
        ->assertSee('<meta name="twitter:image:alt" content="Accessible Disney Planning">', false);
});

test('canonical urls preserve an http application origin', function (): void {
    $applicationUrl = config('app.url');
    URL::forceScheme(null);
    URL::forceRootUrl('http://localhost');

    try {
        $response = get('http://localhost/search');
    } finally {
        URL::forceRootUrl($applicationUrl);
        URL::forceScheme(str_starts_with($applicationUrl, 'https://') ? 'https' : null);
    }

    $response->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/search">', false);
});
