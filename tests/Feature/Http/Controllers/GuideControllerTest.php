<?php

use App\Models\Guide;
use App\Models\Podcast;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('public index page renders', function (): void {
    get(route('guides.index'))
        ->assertOk()
        ->assertSee('Park guides.');
});

test('guides stay hidden from the public site when the feature is disabled', function (): void {
    config()->set('mouse28.guides_enabled', false);

    $guide = Guide::factory()->create([
        'title' => 'A Guide That Is Not Ready Yet',
        'slug' => 'a-guide-that-is-not-ready-yet',
    ]);

    get(route('guides.index'))->assertNotFound();
    get(route('guides.show', $guide))->assertNotFound();

    foreach ([route('home'), route('blog.index'), route('search'), '/missing-page'] as $url) {
        get($url)
            ->assertDontSee(route('guides.index'), false)
            ->assertDontSee('Browse practical guides');
    }

    get(route('home'))
        ->assertOk()
        ->assertDontSee($guide->title)
        ->assertDontSee('dispatch-guide-spread', false);

    get(route('search', ['q' => 'not ready yet']))
        ->assertOk()
        ->assertSee('No results')
        ->assertDontSee($guide->title);

    get(route('sitemap'))
        ->assertOk()
        ->assertDontSee('/guides', false);
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

test('only currently published content is publicly visible', function (): void {
    $publishedGuide = Guide::factory()->create(['title' => 'Published park guide']);
    $draftGuide = Guide::factory()->draft()->create(['title' => 'Draft park guide']);
    $scheduledGuide = Guide::factory()->scheduled()->create(['title' => 'Scheduled park guide']);

    get(route('guides.index'))
        ->assertOk()
        ->assertSee($publishedGuide->title)
        ->assertDontSee($draftGuide->title)
        ->assertDontSee($scheduledGuide->title);

    get(route('guides.show', $scheduledGuide))->assertNotFound();
    get(route('guides.show', $publishedGuide))
        ->assertOk()
        ->assertSee($publishedGuide->title)
        ->assertSee('Last reviewed');
});

test('invalid guide category falls back to all guides', function (): void {
    $guide = Guide::factory()->create();

    get(route('guides.index', ['category' => 'not-a-category']))
        ->assertOk()
        ->assertSee($guide->title);
});

test('editorial review information is shown on the public page', function (): void {
    config()->set('mouse28.guide_review_interval_days', 180);

    $currentGuide = Guide::factory()->create([
        'last_reviewed_at' => now()->subDays(30),
    ]);
    $staleGuide = Guide::factory()->create([
        'last_reviewed_at' => now()->subDays(181),
    ]);
    $unreviewedGuide = Guide::factory()->create([
        'last_reviewed_at' => null,
    ]);

    get(route('guides.show', $currentGuide))
        ->assertOk()
        ->assertDontSee('due for editorial review');

    get(route('guides.show', $staleGuide))
        ->assertOk()
        ->assertSee('due for editorial review');
});

test('related guides prioritize the category and fill open slots', function (): void {
    $guide = Guide::factory()->create([
        'title' => 'Current accessibility guide',
        'category' => 'accessibility',
    ]);
    $sameCategory = Guide::factory()->create([
        'title' => 'Related accessibility guide',
        'category' => 'accessibility',
    ]);
    $fallbackGuide = Guide::factory()->create([
        'title' => 'Useful planning guide',
        'category' => 'family-planning',
    ]);
    $draftGuide = Guide::factory()->draft()->create([
        'title' => 'Private draft guide',
        'category' => 'accessibility',
    ]);

    get(route('guides.show', $guide))
        ->assertOk()
        ->assertSeeInOrder([$sameCategory->title, $fallbackGuide->title])
        ->assertDontSee($draftGuide->title);
});

test('category label links to its filtered index', function (): void {
    $guide = Guide::factory()->create(['category' => 'family-planning']);

    get(route('guides.show', $guide))
        ->assertOk()
        ->assertSee(route('guides.index', ['category' => $guide->category]), false);
});

test('landing page provides search and social metadata', function (): void {
    Podcast::query()->create([
        'name' => 'Mouse28 Weekly',
        'description' => 'A weekly Disney parks podcast for accessibility-minded families.',
        'cover_image' => 'podcasts/show-cover.jpg',
    ]);

    get(route('guides.index'))
        ->assertOk()
        ->assertSee('<meta property="og:title" content="Disney Parks Guides | Mouse28">', false);
});

test('archive canonical preserves meaningful filters and pagination', function (): void {
    Guide::factory()->count(13)->create(['category' => 'family-planning']);

    $guideCanonical = route('guides.index', [
        'category' => 'family-planning',
        'page' => 2,
    ]);

    get($guideCanonical)
        ->assertOk()
        ->assertSee('<link rel="canonical" href="'.e($guideCanonical).'">', false);
});
