<?php

use App\Data\BlogFilters;
use App\Enums\BlogSort;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

covers(BlogFilters::class);

pest()->use(RefreshDatabase::class);

test('blog filters keep an existing category, a trimmed capped search and a known sort', function (): void {
    $category = Category::factory()->create(['name' => 'Sample Topic', 'slug' => 'sample-topic']);

    $filters = BlogFilters::fromInput('sample-topic', '  '.str_repeat('a', 120).'  ', 'oldest');

    expect($filters->category?->is($category))->toBeTrue()
        ->and($filters->category?->name)
        ->toBe('Sample Topic')
        ->and($filters->categorySlug())
        ->toBe('sample-topic')
        ->and($filters->search)
        ->toBe(str_repeat('a', 100))
        ->and($filters->sort)
        ->toBe(BlogSort::Oldest)
        ->and($filters->isDefault())
        ->toBeFalse();
});

test('blog filters cap the search at the configured length', function (): void {
    config()->set('mouse28.blog_search_max_length', 5);

    expect(BlogFilters::fromInput('', 'accessible', '')->search)->toBe('acces');
});

test('blog filters fall back to every story, no search and newest first', function (): void {
    $filters = BlogFilters::fromInput('not-a-category', '   ', 'not-a-sort');

    expect($filters->category)->toBeNull()
        ->and($filters->categorySlug())
        ->toBeEmpty()
        ->and($filters->search)
        ->toBeEmpty()
        ->and($filters->sort)
        ->toBe(BlogSort::Newest)
        ->and($filters->isDefault())
        ->toBeTrue();
});

test('blog filters look a category up once and not at all without one', function (string $slug, int $queries): void {
    Category::factory()->create(['slug' => 'sample-topic']);
    DB::enableQueryLog();

    BlogFilters::fromInput($slug, '', '');

    expect(DB::getQueryLog())->toHaveCount($queries);
})->with([
    'existing category' => ['sample-topic', 1],
    'unknown category' => ['not-a-category', 1],
    'no category' => ['', 0],
]);

test('default blog filters show every story newest first', function (): void {
    expect(new BlogFilters)->toEqual(new BlogFilters(null, '', BlogSort::Newest))
        ->and((new BlogFilters)->isDefault())
        ->toBeTrue();
});
