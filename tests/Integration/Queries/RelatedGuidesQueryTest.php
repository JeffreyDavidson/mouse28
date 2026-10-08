<?php

use App\Enums\GuideCategory;
use App\Models\Guide;
use App\Queries\RelatedGuidesQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(RelatedGuidesQuery::class);

beforeEach(function (): void {
    $this->freezeSecond();
});

test('related guides prioritize category and respect the requested limit', function (int $limit): void {
    $current = Guide::factory()->create(['category' => GuideCategory::Accessibility]);
    $olderMatch = Guide::factory()->create([
        'category' => GuideCategory::Accessibility,
        'published_at' => now()->subDays(4),
    ]);
    $newerMatch = Guide::factory()->create([
        'category' => GuideCategory::Accessibility,
        'published_at' => now()->subDays(3),
    ]);
    $fallback = Guide::factory()->create([
        'category' => GuideCategory::FamilyPlanning,
        'published_at' => now()->subDay(),
    ]);
    Guide::factory()->create([
        'category' => GuideCategory::FamilyPlanning,
        'published_at' => now()->subDays(2),
    ]);
    Guide::factory()
        ->draft()
        ->create(['category' => GuideCategory::Accessibility]);
    Guide::factory()
        ->scheduled()
        ->create(['category' => GuideCategory::Accessibility]);
    $deleted = Guide::factory()->create(['category' => GuideCategory::Accessibility]);
    $deleted->delete();

    $related = app(RelatedGuidesQuery::class)->get($current, $limit);

    expect($related->modelKeys())->toBe(array_slice([$newerMatch->id, $olderMatch->id, $fallback->id], 0, $limit));
})->with([
    'one matching guide' => [1],
    'matching category fills limit' => [2],
    'fallback fills remaining slot' => [3],
]);

test('related guides default to three guides', function (): void {
    $current = Guide::factory()->create();
    Guide::factory()
        ->count(4)
        ->create(['published_at' => now()->subDay()]);

    $related = app(RelatedGuidesQuery::class)->get($current);

    expect($related)->toHaveCount(3);
});

test('related guides return an empty collection when no other published guide exists', function (): void {
    $current = Guide::factory()->create();
    Guide::factory()
        ->draft()
        ->create();
    Guide::factory()
        ->scheduled()
        ->create();

    $related = app(RelatedGuidesQuery::class)->get($current);

    expect($related)->toBeEmpty();
});

test('related guides select only the fields rendered by their cards', function (): void {
    $guide = Guide::factory()->create(['category' => GuideCategory::Accessibility]);
    Guide::factory()->create(['category' => GuideCategory::Accessibility]);

    $relatedGuide = app(RelatedGuidesQuery::class)
        ->get($guide)
        ->sole();

    expect($relatedGuide->getAttributes())
        ->toHaveKeys(['id', 'slug', 'title', 'category', 'featured_image_path'])
        ->not->toHaveKeys(['content', 'excerpt', 'meta_description']);
});

test('related guides break publish-time ties by id so their order is stable on MySQL', function (): void {
    $guide = Guide::factory()->create();
    Guide::factory()
        ->count(2)
        ->create();

    $orderings = publishTimeOrderings(fn () => app(RelatedGuidesQuery::class)->get($guide));

    expect($orderings)->not->toBeEmpty()
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});
