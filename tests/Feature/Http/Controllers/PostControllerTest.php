<?php

use App\Livewire\BlogIndex;
use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use App\Support\ResponsiveArtwork;
use Dom\HTMLDocument;
use Dom\XPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('blog pages stay within their query budget as content grows', function (string $page, int $queries): void {
    $episode = Episode::factory()->create();
    $post = Post::factory()->create(['episode_id' => $episode->id, 'category' => 'disney-tips']);
    Post::factory()->count(30)->create(['category' => 'disney-tips']);
    $url = $page === 'index' ? route('blog.index') : route('blog.show', $post);

    $this->expectsDatabaseQueryCount($queries);

    get($url)
        ->assertOk();
})->with(['archive' => ['index', 5], 'article with episode' => ['show', 4]]);

test('blog featured cover is prioritized while archive cards remain deferred', function (): void {
    Storage::fake('public');
    $contents = UploadedFile::fake()->image('cover.webp', 600, 300)->getContent();
    Storage::disk('public')->put('posts/cover.webp', $contents);
    Storage::disk('public')->put(
        ResponsiveArtwork::variantPath(hash('sha256', $contents), 480),
        UploadedFile::fake()->image('variant.webp', 480, 240)->getContent(),
    );
    Post::factory()->count(2)->create(['cover_image' => 'posts/cover.webp']);

    $response = get(route('blog.index'))
        ->assertOk();

    $document = HTMLDocument::createFromString($response->getContent(), LIBXML_NOERROR);
    $images = $document->querySelectorAll('img[src="/storage/posts/cover.webp"]');

    expect($images->item(0)->getAttribute('loading'))->toBe('eager')
        ->and($images->item(0)->getAttribute('fetchpriority'))->toBe('high')
        ->and($images->item(1)->getAttribute('loading'))->toBe('lazy')
        ->and($images->item(0)->getAttribute('srcset'))->toContain('480w', '600w')
        ->and($images->item(0)->getAttribute('sizes'))->not->toStartWith('auto')
        ->and($images->item(1)->getAttribute('sizes'))->toStartWith('auto, ');
});

test('hidden content uses the same recovery page without revealing its title', function (): void {
    $draftPost = Post::factory()->draft()->create([
        'title' => 'Unannounced family update',
    ]);

    get(route('blog.show', $draftPost))
        ->assertNotFound()
        ->assertSee('That page wandered off')
        ->assertDontSee($draftPost->title);
});

test('public index page renders', function (): void {
    get(route('blog.index'))
        ->assertOk()
        ->assertSeeLivewire(BlogIndex::class)
        ->assertSee('Blog');
});

test('blog navigation identifies Blog as the current destination', function (): void {
    $response = get(route('blog.index'))
        ->assertOk();

    $document = HTMLDocument::createFromString($response->getContent(), LIBXML_NOERROR);
    $xpath = new XPath($document);
    $links = $xpath->query('//*[local-name()="a"][contains(concat(" ", normalize-space(@class), " "), " dispatch-nav-link ") and @aria-current="page"]');

    expect($links)->toHaveCount(1)
        ->and(trim($links->item(0)->textContent))->toBe('Blog')
        ->and($links->item(0)->getAttribute('href'))->toBe(route('blog.index'));
});

test('blog pages render one newsletter signup', function (): void {
    $post = Post::factory()->create();

    foreach ([route('blog.index'), route('blog.show', $post)] as $url) {
        $response = get($url)
            ->assertOk();

        expect(substr_count($response->getContent(), 'action="'.route('newsletter.store').'"'))->toBe(1);
    }
});

test('post social image URLs are absolute', function (): void {
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

test('empty blog discovery offers useful paths forward', function (): void {
    get(route('blog.index'))
        ->assertOk()
        ->assertSee(route('guides.index'), false)
        ->assertSee(route('episodes.index'), false);
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

test('published post detail page renders', function (): void {
    $post = Post::query()->create([
        'title' => 'An Accessible Day at the Parks',
        'slug' => 'accessible-day-at-the-parks',
        'excerpt' => 'A practical guide for planning a comfortable park day.',
        'body' => 'Start with a flexible plan. '.str_repeat('accessible park planning ', 198),
        'category' => 'park-accessibility',
        'author' => 'jeffrey',
        'is_published' => true,
        'published_at' => now()->subDay(),
    ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('3 min read')
        ->assertDontSee('1 of 3 min read')
        ->assertSee('Start with a flexible plan', false)
        ->assertSee('editorial-reading-column', false)
        ->assertDontSee('data-article-secondary', false)
        ->assertSee('id="back-to-top"', false)
        ->assertSee('aria-hidden="true"', false)
        ->assertSee('tabindex="-1"', false)
        ->assertSee('inline-flex size-12 items-center justify-center rounded-full', false)
        ->assertDontSee('inline-flex size-11', false);
});

test('only currently published content is publicly visible', function (): void {
    $publishedPost = Post::factory()->create(['title' => 'Published park post']);
    $draftPost = Post::factory()->draft()->create(['title' => 'Draft park post']);
    $scheduledPost = Post::factory()->scheduled()->create(['title' => 'Scheduled park post']);

    get(route('blog.index'))
        ->assertOk()
        ->assertSee($publishedPost->title)
        ->assertDontSee($draftPost->title)
        ->assertDontSee($scheduledPost->title);

    get(route('blog.show', $scheduledPost))->assertNotFound();
});

test('blog search category sorting and pagination preserve filters', function (): void {
    Post::factory()->create([
        'title' => 'Newest accessible plan',
        'category' => 'park-accessibility',
        'published_at' => now()->subDay(),
    ]);
    Post::factory()->create([
        'title' => 'Oldest accessible plan',
        'category' => 'park-accessibility',
        'published_at' => now()->subWeek(),
    ]);
    Post::factory()->create([
        'title' => 'Unrelated dining review',
        'category' => 'food-reviews',
    ]);

    get(route('blog.index', [
        'category' => 'park-accessibility',
        'q' => 'accessible',
        'sort' => 'oldest',
    ]))
        ->assertOk()
        ->assertSeeInOrder(['Oldest accessible plan', 'Newest accessible plan'])
        ->assertDontSee('Unrelated dining review');
});

test('editorial review information is shown on the public page', function (): void {
    config()->set('mouse28.post_review_interval_days', 180);

    $currentPost = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => today()->subDays(30),
    ]);
    $stalePost = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => today()->subDays(181),
    ]);

    get(route('blog.show', $currentPost))
        ->assertOk()
        ->assertSee('Last reviewed')
        ->assertSee($currentPost->source_url, false)
        ->assertDontSee('due for editorial review');

    get(route('blog.show', $stalePost))
        ->assertOk()
        ->assertSee('due for editorial review');
});

test('posts do not reveal an unpublished related episode', function (): void {
    $draftEpisode = Episode::factory()->draft()->create([
        'title' => 'Unannounced podcast episode',
    ]);
    $post = Post::factory()->create([
        'episode_id' => $draftEpisode->getKey(),
    ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertDontSee($draftEpisode->title)
        ->assertDontSee(route('episodes.show', $draftEpisode), false);
});

test('category label links to its filtered index', function (): void {
    $post = Post::factory()->create(['category' => 'park-accessibility']);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee(route('blog.index', ['category' => $post->category]), false);
});

test('invalid blog filters do not create indexable archive variants', function (): void {
    get(route('blog.index', [
        'category' => 'not-a-category',
        'sort' => 'not-a-sort',
    ]))
        ->assertOk()
        ->assertSee('<title>Disney Parks Blog | Mouse28</title>', false)
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('blog.index').'">', false);
});

test('landing page provides search and social metadata', function (): void {
    Podcast::query()->create([
        'name' => 'Mouse28 Weekly',
        'description' => 'A weekly Disney parks podcast for accessibility-minded families.',
        'cover_image' => 'podcasts/show-cover.jpg',
    ]);

    get(route('blog.index'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Disney Parks Blog | Mouse28">', false)
        ->assertSee('<meta property="og:url" content="'.route('blog.index').'">', false);
});

test('archive canonical preserves meaningful filters and pagination', function (): void {
    Post::factory()->count(13)->create(['category' => 'park-accessibility']);

    $blogCanonical = route('blog.index', [
        'category' => 'park-accessibility',
        'page' => 2,
    ]);

    get($blogCanonical)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e($blogCanonical).'">', false);
});

test('text searches are not indexed', function (): void {
    get(route('blog.index', ['q' => 'sensory']))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('blog.index').'">', false);
});

test('an uncategorized post keeps its public fallback presentation', function (): void {
    $post = Post::factory()->create(['category' => null, 'cover_image' => null]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('Mouse28 dispatch')
        ->assertDontSee('category=', false);
});

test('blog posts include article and breadcrumb structured data', function (): void {
    $post = Post::factory()->create([
        'title' => 'Accessible <Park> Plan',
        'cover_image' => 'posts/plan.jpg',
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => '2026-08-01',
        'updated_at' => '2026-07-01',
    ]);

    $response = get(route('blog.show', $post));

    $response->assertOk();
    $data = $this->structuredData($response);

    expect($data['@context'])->toBe('https://schema.org')
        ->and($data['@graph'][0]['@type'])->toBe('BlogPosting')
        ->and($data['@graph'][0]['headline'])->toBe($post->title)
        ->and($data['@graph'][0]['mainEntityOfPage'])->toBe(route('blog.show', $post))
        ->and($data['@graph'][0]['citation'])->toBe($post->source_url)
        ->and($data['@graph'][0]['dateModified'])->toStartWith('2026-08-01')
        ->and($data['@graph'][1]['@type'])->toBe('BreadcrumbList')
        ->and(array_column($data['@graph'][1]['itemListElement'], 'name'))
        ->toBe(['Home', 'Blog', $post->title]);
});

test('page copy and metadata avoid em dashes', function (): void {
    get(route('blog.index'))
        ->assertOk()
        ->assertDontSee('—');
});

test('page uses the dispatch editorial system', function (): void {
    get(route('blog.index'))
        ->assertOk()
        ->assertSee('data-brand-wordmark', false)
        ->assertSee('data-editorial-blog', false)
        ->assertSee('js-dispatch-pages', false);
});

test('reading page uses the dispatch reading surface', function (): void {
    $post = Post::factory()->create();

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee('editorial-detail-hero', false)
        ->assertSee('editorial-reading-column', false)
        ->assertDontSee('—');
});

test('form placeholders use readable text colors', function (): void {
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
});
