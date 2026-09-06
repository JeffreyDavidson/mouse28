<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

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
        ->assertSee('Blog');
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
