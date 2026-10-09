<?php

use App\Enums\GuideCategory;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\ViewModels\SearchViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

covers(SearchViewModel::class);

pest()->use(RefreshDatabase::class);

test('search results map each group to the cards the page shows', function (): void {
    config()->set('mouse28.guides_enabled', true);
    $post = Post::factory()
        ->for(Category::factory()->state(['name' => 'Sample Topic']))
        ->create(['title' => 'Sensory planning post', 'excerpt' => 'Post excerpt']);
    $guide = Guide::factory()->create([
        'title' => 'Sensory planning guide',
        'excerpt' => 'Guide excerpt',
        'category' => GuideCategory::Accessibility,
    ]);
    $episode = Episode::factory()->create([
        'title' => 'Sensory planning episode',
        'episode_number' => 42,
        'description' => 'Episode description',
    ]);

    $results = app(SearchViewModel::class)->data('Sensory planning')['results'];

    expect(array_map(fn (LengthAwarePaginator $group): array => $group->items(), $results))->toBe([
        'posts' => [[
            'url' => route('blog.show', $post),
            'eyebrow' => 'Sample Topic',
            'title' => 'Sensory planning post',
            'description' => 'Post excerpt',
        ]],
        'guides' => [[
            'url' => route('guides.show', $guide),
            'eyebrow' => GuideCategory::Accessibility->getLabel(),
            'title' => 'Sensory planning guide',
            'description' => 'Guide excerpt',
        ]],
        'episodes' => [[
            'url' => route('episodes.show', $episode),
            'eyebrow' => 'Episode 42',
            'title' => 'Sensory planning episode',
            'description' => 'Episode description',
        ]],
    ]);
});

test('search results count every match across the groups', function (): void {
    config()->set('mouse28.guides_enabled', true);
    config()->set('search.per_page', 1);
    Post::factory()
        ->count(2)
        ->create(['title' => 'Sensory post']);
    Guide::factory()->create(['title' => 'Sensory guide']);
    Episode::factory()->create(['title' => 'Sensory episode']);

    $data = app(SearchViewModel::class)->data('Sensory');

    expect($data['query'])->toBe('Sensory')
        ->and($data['resultCount'])
        ->toBe(4);
});

test('search results label each group with its section title', function (): void {
    expect(app(SearchViewModel::class)->data('')['typeLabels'])->toBe([
        'posts' => 'Blog posts',
        'guides' => 'Guides',
        'episodes' => 'Podcast episodes',
    ]);
});

test('search results are empty for a blank query', function (): void {
    Post::factory()->create(['title' => 'Sensory post']);

    $data = app(SearchViewModel::class)->data('');

    expect($data['query'])->toBeEmpty()
        ->and($data['results'])
        ->toBeEmpty()
        ->and($data['resultCount'])
        ->toBe(0);
});

test('search results link each page with the search terms and back to the group heading', function (): void {
    config()->set('mouse28.guides_enabled', true);
    config()->set('search.per_page', 1);
    Post::factory()
        ->count(2)
        ->create(['title' => 'Sensory post']);
    Guide::factory()
        ->count(2)
        ->create(['title' => 'Sensory guide']);
    Episode::factory()
        ->count(2)
        ->create(['title' => 'Sensory episode']);
    app()->instance('request', Request::create(route('search', ['q' => 'Sensory', 'postsPage' => 1])));

    $results = app(SearchViewModel::class)->data('Sensory')['results'];

    expect(array_map(fn (LengthAwarePaginator $group): ?string => $group->nextPageUrl(), $results))->toBe([
        'posts' => route('search', ['q' => 'Sensory', 'postsPage' => 2]).'#search-posts',
        'guides' => route('search', ['q' => 'Sensory', 'postsPage' => 1, 'guidesPage' => 2]).'#search-guides',
        'episodes' => route('search', ['q' => 'Sensory', 'postsPage' => 1, 'episodesPage' => 2]).'#search-episodes',
    ]);
});

test('search omits guides when the feature is disabled', function (): void {
    config()->set('mouse28.guides_enabled', false);
    Guide::factory()->create(['title' => 'Sensory planning guide']);
    Post::factory()->create(['title' => 'Sensory planning post']);
    Episode::factory()->create(['title' => 'Sensory planning episode']);

    $data = app(SearchViewModel::class)->data('Sensory planning');

    expect(array_keys($data['results']))->toBe(['posts', 'episodes'])
        ->and($data['resultCount'])
        ->toBe(2);
});
