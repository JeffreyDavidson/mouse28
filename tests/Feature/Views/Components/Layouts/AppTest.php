<?php

use App\Models\Episode;
use App\Models\Post;
use Dom\HTMLDocument;
use Dom\XPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

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

test('the canonical Transistor feed is advertised without persisting default settings', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSee(config('podcast.rss_url'), false)
        ->assertSee('RSS Feed');

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee(config('podcast.rss_url'), false);
});
