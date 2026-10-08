<?php

use App\Enums\SearchContentType;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Queries\SearchQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;

pest()->use(RefreshDatabase::class);

covers(SearchQuery::class);

/**
 * The slugs in each result group, keyed by group.
 *
 * @param  array<string, LengthAwarePaginator<int, Post>|LengthAwarePaginator<int, Guide>|LengthAwarePaginator<int, Episode>>  $results
 * @return array<string, array<mixed>>
 */
function searchSlugs(array $results): array
{
    $slugs = [];

    foreach ($results as $type => $group) {
        $slugs[$type] = $group->getCollection()
            ->pluck('slug')
            ->all();
    }

    return $slugs;
}

test('search query groups the matching published content by type in page order', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()->create(['title' => 'Sensory post', 'slug' => 'sensory-post']);
    Guide::factory()->create(['title' => 'Sensory guide', 'slug' => 'sensory-guide']);
    Episode::factory()->create(['title' => 'Sensory episode', 'slug' => 'sensory-episode']);
    Post::factory()->create(['title' => 'Unrelated post']);
    Post::factory()
        ->draft()
        ->create(['title' => 'Sensory draft post']);
    Guide::factory()
        ->draft()
        ->create(['title' => 'Sensory draft guide']);
    Episode::factory()
        ->scheduled()
        ->create(['title' => 'Sensory scheduled episode']);

    $results = app(SearchQuery::class)->get('Sensory');

    expect(searchSlugs($results))->toBe([
        'posts' => ['sensory-post'],
        'guides' => ['sensory-guide'],
        'episodes' => ['sensory-episode'],
    ]);
});

test('search query leaves guides out while the feature is disabled', function (?SearchContentType $type, array $expectedGroups): void {
    config()->set('mouse28.guides_enabled', false);
    Guide::factory()->create(['title' => 'Sensory guide']);

    expect(array_keys(app(SearchQuery::class)->get('Sensory', $type)))->toBe($expectedGroups);
})->with([
    'every type' => [null, ['posts', 'episodes']],
    'guides only' => [SearchContentType::Guides, []],
]);

test('search query searches only the requested type', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()->create(['title' => 'Sensory post']);
    Episode::factory()->create(['title' => 'Sensory episode', 'slug' => 'sensory-episode']);

    $results = app(SearchQuery::class)->get('Sensory', SearchContentType::Episodes);

    expect(searchSlugs($results))->toBe(['episodes' => ['sensory-episode']]);
});

test('search query returns no groups for a blank query', function (?string $query): void {
    Post::factory()->create(['title' => 'Sensory post']);

    expect(app(SearchQuery::class)->get($query))->toBeEmpty();
})->with([
    'missing' => [null],
    'empty' => [''],
    'whitespace' => ['   '],
]);

test('search query trims the query before matching', function (): void {
    Post::factory()->create(['title' => 'Sensory post', 'slug' => 'sensory-post']);

    expect(searchSlugs(app(SearchQuery::class)->get('  Sensory  '))['posts'])->toBe(['sensory-post']);
});

test('search query selects only the fields the results show', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()->create(['title' => 'Sensory post']);
    Guide::factory()->create(['title' => 'Sensory guide']);
    Episode::factory()->create(['title' => 'Sensory episode']);

    $results = app(SearchQuery::class)->get('Sensory');

    expect($results['posts']->sole()
        ->getAttributes())
        ->toHaveKeys(['slug', 'title', 'excerpt', 'category_id'])
        ->not->toHaveKeys(['content', 'status', 'published_at'])
        ->and($results['guides']->sole()
            ->getAttributes())
        ->toHaveKeys(['slug', 'title', 'excerpt', 'category'])
        ->not->toHaveKeys(['content', 'status'])
        ->and($results['episodes']->sole()
            ->getAttributes())
        ->toHaveKeys(['slug', 'episode_number', 'title', 'description'])
        ->not->toHaveKeys(['show_notes', 'transcript', 'status']);
});

test('search query loads post categories with only the name the results show', function (): void {
    Post::factory()
        ->for(Category::factory())
        ->create(['title' => 'Sensory post']);

    $category = app(SearchQuery::class)->get('Sensory')['posts']->sole()
        ->getRelation('category');

    if (! $category instanceof Category) {
        throw new UnexpectedValueException('The post category was not loaded.');
    }

    expect(array_keys($category->getAttributes()))->toBe(['id', 'name']);
});

test('search query matches the written content of every type', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()->create(['title' => 'Arrival tips', 'slug' => 'arrival-tips', 'excerpt' => '', 'content' => 'Find the quietzone near the entrance.']);
    Guide::factory()->create(['title' => 'Arrival guide', 'slug' => 'arrival-guide', 'excerpt' => '', 'content' => 'Another quietzone sits by the lake.']);
    Episode::factory()->create(['title' => 'Arrival notes', 'slug' => 'arrival-notes', 'description' => '', 'show_notes' => 'The quietzone opens early.', 'transcript' => '']);
    Episode::factory()->create(['title' => 'Arrival talk', 'slug' => 'arrival-talk', 'description' => '', 'show_notes' => '', 'transcript' => 'We found a quietzone.']);
    Post::factory()->create(['title' => 'Unrelated', 'excerpt' => '', 'content' => 'Nothing to see here.']);

    expect(searchSlugs(app(SearchQuery::class)->get('quietzone')))->toEqualCanonicalizing([
        'posts' => ['arrival-tips'],
        'guides' => ['arrival-guide'],
        'episodes' => ['arrival-notes', 'arrival-talk'],
    ]);
});

test('search query treats wildcard characters as literal text', function (string $query, int $expectedPerType): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()->create(['title' => 'Magic Kingdom 100% guide', 'excerpt' => '', 'content' => '']);
    Guide::factory()->create(['title' => 'Magic Kingdom 100% guide', 'excerpt' => '', 'content' => '']);
    Episode::factory()->create(['title' => 'Magic Kingdom 100% guide', 'description' => '', 'show_notes' => '', 'transcript' => '']);
    Post::factory()->create(['title' => 'Magic Kingdom 1000 steps', 'excerpt' => '', 'content' => '']);
    Guide::factory()->create(['title' => 'Magic Kingdom 1000 steps', 'excerpt' => '', 'content' => '']);
    Episode::factory()->create(['title' => 'Magic Kingdom 1000 steps', 'description' => '', 'show_notes' => '', 'transcript' => '']);

    $results = app(SearchQuery::class)->get($query);

    expect(array_map(fn (LengthAwarePaginator $group): int => $group->total(), $results))->toBe([
        'posts' => $expectedPerType,
        'guides' => $expectedPerType,
        'episodes' => $expectedPerType,
    ]);
})->with([
    'percent sign' => ['100%', 1],
    'underscore' => ['_', 0],
]);

test('search query pages each group through its own parameter at the configured size', function (): void {
    config()->set('mouse28.guides_enabled', true);
    config()->set('search.per_page', 2);

    $results = app(SearchQuery::class)->get('Castle');

    expect(array_map(fn (LengthAwarePaginator $group): array => [$group->getPageName(), $group->perPage()], $results))->toBe([
        'posts' => ['postsPage', 2],
        'guides' => ['guidesPage', 2],
        'episodes' => ['episodesPage', 2],
    ]);
});

test('search query breaks publish-time ties by id so its order is stable on MySQL', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()->create(['title' => 'Sensory post']);
    Guide::factory()->create(['title' => 'Sensory guide']);
    Episode::factory()->create(['title' => 'Sensory episode']);

    $orderings = publishTimeOrderings(fn (): array => app(SearchQuery::class)->get('Sensory'));

    expect($orderings)->toHaveCount(3)
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});

test('search query lists newer matches first', function (): void {
    Post::factory()->create(['title' => 'Sensory older', 'slug' => 'sensory-older', 'published_at' => now()->subDays(2)]);
    Post::factory()->create(['title' => 'Sensory newer', 'slug' => 'sensory-newer', 'published_at' => now()->subDay()]);

    expect(searchSlugs(app(SearchQuery::class)->get('Sensory'))['posts'])->toBe(['sensory-newer', 'sensory-older']);
});
