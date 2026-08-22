<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public landing pages have specific search and social metadata', function (): void {
    Podcast::query()->create([
        'name' => 'Mouse28 Weekly',
        'description' => 'A weekly Disney parks podcast for accessibility-minded families.',
        'cover_image' => 'podcasts/show-cover.jpg',
    ]);

    get(route('home'))
        ->assertOk()
        ->assertSee('<meta name="description" content="Accessibility tips, sensory-friendly park planning, family experiences, and the Mouse28 podcast from Jeffrey and Cassie Davidson.">', false)
        ->assertSee('<meta property="og:image" content="'.url('/images/hero-family.jpg').'">', false);

    get(route('blog.index'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Disney Parks Blog — Mouse28">', false)
        ->assertSee('<meta property="og:url" content="'.route('blog.index').'">', false);

    get(route('guides.index'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Disney Parks Guides — Mouse28">', false);

    get(route('episodes.index'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Mouse28 Weekly Podcast">', false)
        ->assertSee('<meta property="og:description" content="A weekly Disney parks podcast for accessibility-minded families.">', false)
        ->assertSee('<meta property="og:image" content="'.url('/storage/podcasts/show-cover.jpg').'">', false);

    get(route('about'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="About the Davidson Family — Mouse28">', false);

    get(route('contact.show'))
        ->assertOk()
        ->assertSee('<meta name="description" content="Contact Jeffrey and Cassie about Mouse28, Disney park accessibility, family travel, collaborations, or the podcast.">', false);
});

test('archive canonicals preserve meaningful filters and pagination', function (): void {
    Post::factory()->count(13)->create(['category' => 'park-accessibility']);
    Guide::factory()->count(13)->create(['category' => 'family-planning']);
    Episode::factory()->count(13)->create();

    $blogCanonical = route('blog.index', [
        'category' => 'park-accessibility',
        'page' => 2,
    ]);
    $guideCanonical = route('guides.index', [
        'category' => 'family-planning',
        'page' => 2,
    ]);
    $episodeCanonical = route('episodes.index', ['page' => 2]);

    get($blogCanonical)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e($blogCanonical).'">', false);

    get($guideCanonical)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e($guideCanonical).'">', false);

    get($episodeCanonical)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e($episodeCanonical).'">', false);
});

test('search and blog text filters are not indexed', function (): void {
    get(route('search', ['q' => 'sensory']))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('search').'">', false);

    get(route('blog.index', ['q' => 'sensory']))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('blog.index').'">', false);
});

test('invalid blog filters do not create indexable archive variants', function (): void {
    get(route('blog.index', [
        'category' => 'not-a-category',
        'sort' => 'not-a-sort',
    ]))
        ->assertOk()
        ->assertSee('<title>Disney Parks Blog — Mouse28</title>', false)
        ->assertSee('<meta name="robots" content="index,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('blog.index').'">', false);
});

test('relative content images become absolute social image urls', function (): void {
    $post = Post::factory()->create([
        'og_image' => 'posts/social-card.jpg',
    ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee('<meta property="og:image" content="'.url('/storage/posts/social-card.jpg').'">', false)
        ->assertSee('<meta name="twitter:image" content="'.url('/storage/posts/social-card.jpg').'">', false);
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
