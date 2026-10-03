<?php

use App\Models\Post;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

function runPostCategoryCopyMigration(): void
{
    $migration = require database_path('migrations/2026_10_03_015033_copy_post_categories_to_categories.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The post category copy migration could not be loaded.');
    }

    $migration->up();
}

/**
 * Creates a post through the factory, then writes its legacy category string
 * directly and clears category_id, as rows written before the table existed hold it.
 */
function legacyCategoryPost(?string $category, bool $trashed = false): int
{
    $post = Post::factory()->create();

    DB::table('posts')->where('id', $post->id)->update([
        'category' => $category,
        'category_id' => null,
        'deleted_at' => $trashed ? '2026-09-01 08:00:00' : null,
    ]);

    return $post->id;
}

function postCategorySlug(int $postId): ?string
{
    $slug = DB::table('posts')
        ->leftJoin('categories', 'categories.id', '=', 'posts.category_id')
        ->where('posts.id', $postId)
        ->value('categories.slug');

    return is_string($slug) ? $slug : null;
}

/** @return array<mixed> category names keyed by slug */
function categoryNamesBySlug(): array
{
    return DB::table('categories')->orderBy('id')->pluck('name', 'slug')->all();
}

dataset('legacy post categories', [
    'disney-tips' => ['disney-tips', 'Disney Tips'],
    'park-accessibility' => ['park-accessibility', 'Park Accessibility'],
    'episode-recap' => ['episode-recap', 'Episode Recap'],
    'family-life' => ['family-life', 'Family Life'],
    'autism-awareness' => ['autism-awareness', 'Autism Awareness'],
    'disney-news' => ['disney-news', 'Disney News'],
    'food-reviews' => ['food-reviews', 'Food Reviews'],
    'resort-reviews' => ['resort-reviews', 'Resort Reviews'],
    'disney-plus' => ['disney-plus', 'Disney+'],
    'merchandise' => ['merchandise', 'Merchandise'],
    'general' => ['general', 'General'],
]);

test('the migration creates one category per legacy post category in order', function (): void {
    DB::table('categories')->delete();

    runPostCategoryCopyMigration();

    expect(categoryNamesBySlug())->toBe([
        'disney-tips' => 'Disney Tips',
        'park-accessibility' => 'Park Accessibility',
        'episode-recap' => 'Episode Recap',
        'family-life' => 'Family Life',
        'autism-awareness' => 'Autism Awareness',
        'disney-news' => 'Disney News',
        'food-reviews' => 'Food Reviews',
        'resort-reviews' => 'Resort Reviews',
        'disney-plus' => 'Disney+',
        'merchandise' => 'Merchandise',
        'general' => 'General',
    ])
        ->and(DB::table('categories')->whereNotNull('description')->count())->toBe(0)
        ->and(DB::table('categories')->whereNull('created_at')->count())->toBe(0);
});

test('the migration keeps a category that already exists', function (): void {
    DB::table('categories')->where('slug', 'general')->update(['name' => 'Renamed General', 'description' => 'Kept']);

    runPostCategoryCopyMigration();

    expect(DB::table('categories')->where('slug', 'general')->get(['name', 'description'])->map(fn (object $row): array => (array) $row)->all())
        ->toBe([['name' => 'Renamed General', 'description' => 'Kept']])
        ->and(DB::table('categories')->count())->toBe(11);
});

test('the backfill links each post to the category with its legacy slug', function (string $slug, string $name, bool $trashed): void {
    $postId = legacyCategoryPost($slug, $trashed);

    runPostCategoryCopyMigration();

    expect(postCategorySlug($postId))->toBe($slug)
        ->and(DB::table('categories')->where('slug', $slug)->value('name'))->toBe($name);
})->with('legacy post categories')->with([
    'a live post' => false,
    'a trashed post' => true,
]);

test('the backfill links a legacy slug to a category created outside the standard list', function (): void {
    DB::table('categories')->insert(['name' => 'Water Parks', 'slug' => 'water-parks']);
    $postId = legacyCategoryPost('water-parks');

    runPostCategoryCopyMigration();

    expect(postCategorySlug($postId))->toBe('water-parks');
});

test('the backfill leaves posts without a legacy category uncategorized', function (bool $trashed): void {
    $postId = legacyCategoryPost(null, $trashed);

    runPostCategoryCopyMigration();

    expect(DB::table('posts')->where('id', $postId)->value('category_id'))->toBeNull();
})->with([
    'a live post' => false,
    'a trashed post' => true,
]);

test('the backfill keeps a category_id that is already set', function (): void {
    $postId = legacyCategoryPost('disney-tips');
    $generalId = DB::table('categories')->where('slug', 'general')->value('id');
    DB::table('posts')->where('id', $postId)->update(['category_id' => $generalId]);

    runPostCategoryCopyMigration();

    expect(postCategorySlug($postId))->toBe('general');
});

test('the backfill leaves the legacy category column in place', function (): void {
    $postId = legacyCategoryPost('family-life');

    runPostCategoryCopyMigration();

    expect(DB::table('posts')->where('id', $postId)->value('category'))->toBe('family-life');
});

test('running the category backfill again changes nothing', function (): void {
    legacyCategoryPost('disney-tips');
    legacyCategoryPost('general', trashed: true);
    legacyCategoryPost(null);
    runPostCategoryCopyMigration();
    $categories = DB::table('categories')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all();
    $links = DB::table('posts')->orderBy('id')->pluck('category_id', 'id')->all();

    runPostCategoryCopyMigration();

    expect(DB::table('categories')->orderBy('id')->get()->map(fn (object $row): array => (array) $row)->all())->toBe($categories)
        ->and(DB::table('posts')->orderBy('id')->pluck('category_id', 'id')->all())->toBe($links);
});

test('the backfill refuses to finish while a legacy category has no matching row', function (): void {
    legacyCategoryPost('retired-category');

    expect(fn () => runPostCategoryCopyMigration())
        ->toThrow(RuntimeException::class, '1 post(s) still have a category with no category_id after the backfill.');
});

test('the backfill refuses to finish while a copied category link goes missing', function (): void {
    $postId = legacyCategoryPost('disney-tips');
    $cleared = false;
    // Simulates the link disappearing between the copy and the check.
    DB::listen(function (QueryExecuted $query) use ($postId, &$cleared): void {
        if (! $cleared && str_starts_with($query->sql, 'update') && str_contains($query->sql, 'posts') && str_contains($query->sql, 'category_id')) {
            $cleared = true;
            DB::table('posts')->where('id', $postId)->update(['category_id' => null]);
        }
    });

    expect(fn () => runPostCategoryCopyMigration())
        ->toThrow(RuntimeException::class, '1 post(s) still have a category with no category_id after the backfill.');
});
