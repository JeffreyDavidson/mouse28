<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\ViewModels\SearchViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(SearchViewModel::class);

pest()->use(RefreshDatabase::class);

test('search results select only the fields rendered by the page', function (): void {
    config()->set('mouse28.guides_enabled', true);

    Post::factory()->create([
        'title' => 'Sensory planning post',
        'body' => 'Sensory planning post body',
    ]);
    Guide::factory()->create([
        'title' => 'Sensory planning guide',
        'body' => 'Sensory planning guide body',
    ]);
    Episode::factory()->create([
        'title' => 'Sensory planning episode',
        'show_notes' => 'Sensory planning episode notes',
        'transcript' => 'Sensory planning episode transcript',
    ]);

    $results = app(SearchViewModel::class)->data('Sensory planning');

    expect($results['posts']->sole()->getAttributes())
        ->toHaveKeys(['slug', 'title', 'excerpt', 'category'])
        ->not->toHaveKeys(['body', 'meta_description'])
        ->and($results['guides']->sole()->getAttributes())
        ->toHaveKeys(['slug', 'title', 'excerpt', 'category'])
        ->not->toHaveKeys(['body', 'meta_description'])
        ->and($results['episodes']->sole()->getAttributes())
        ->toHaveKeys(['slug', 'episode_number', 'title', 'description'])
        ->not->toHaveKeys(['show_notes', 'transcript', 'meta_description']);
});

test('search omits guides when the feature is disabled', function (): void {
    config()->set('mouse28.guides_enabled', false);

    Guide::factory()->create(['title' => 'Sensory planning guide']);
    Post::factory()->create(['title' => 'Sensory planning post']);
    Episode::factory()->create(['title' => 'Sensory planning episode']);

    $results = app(SearchViewModel::class)->data('Sensory planning');

    expect($results['guides']->total())->toBe(0)
        ->and($results['posts']->total())->toBe(1)
        ->and($results['episodes']->total())->toBe(1);
});

test('search treats wildcard characters as literal text', function (string $query, int $expectedPerType): void {
    config()->set('mouse28.guides_enabled', true);

    Post::factory()->create(['title' => 'Magic Kingdom 100% guide', 'excerpt' => '', 'body' => '']);
    Guide::factory()->create(['title' => 'Magic Kingdom 100% guide', 'excerpt' => '', 'body' => '']);
    Episode::factory()->create(['title' => 'Magic Kingdom 100% guide', 'description' => '', 'show_notes' => '', 'transcript' => '']);
    Post::factory()->create(['title' => 'Magic Kingdom 1000 steps', 'excerpt' => '', 'body' => '']);
    Guide::factory()->create(['title' => 'Magic Kingdom 1000 steps', 'excerpt' => '', 'body' => '']);
    Episode::factory()->create(['title' => 'Magic Kingdom 1000 steps', 'description' => '', 'show_notes' => '', 'transcript' => '']);

    $results = app(SearchViewModel::class)->data($query);

    expect($results['posts']->total())->toBe($expectedPerType)
        ->and($results['guides']->total())->toBe($expectedPerType)
        ->and($results['episodes']->total())->toBe($expectedPerType);
})->with([
    'percent sign' => ['100%', 1],
    'underscore' => ['_', 0],
]);

test('search results use the configured page size for each section', function (string $query): void {
    config()->set('mouse28.guides_enabled', true);
    config()->set('mouse28.search_results_per_page', 2);

    $results = app(SearchViewModel::class)->data($query);

    expect($results['posts']->perPage())->toBe(2)
        ->and($results['guides']->perPage())->toBe(2)
        ->and($results['episodes']->perPage())->toBe(2);
})->with([
    'with a query' => ['Castle'],
    'without a query' => [''],
]);
