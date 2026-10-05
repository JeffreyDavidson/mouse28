<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runPostCategoryIdMigration(): void
{
    $migration = require database_path('migrations/2026_10_03_015030_add_category_id_to_posts_table.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The post category_id migration could not be loaded.');
    }

    $migration->up();
}

/**
 * The given property of each foreign key on posts, keyed by the table it references.
 *
 * @return array<mixed>
 */
function postForeignKeys(string $property): array
{
    return array_column(Schema::getForeignKeys('posts'), $property, 'foreign_table');
}

/** How many foreign keys on posts reference the categories table. */
function postCategoryForeignKeyCount(): int
{
    return count(array_keys(array_column(Schema::getForeignKeys('posts'), 'foreign_table'), 'categories', true));
}

test('posts gain a nullable, indexed category_id', function (): void {
    $nullable = array_column(Schema::getColumns('posts'), 'nullable', 'name');

    expect($nullable['category_id'])->toBeTrue()
        ->and(Schema::hasIndex('posts', ['category_id']))->toBeTrue();
});

test('posts.category_id references categories and is set to null when its category is deleted', function (): void {
    expect(postCategoryForeignKeyCount())->toBe(1)
        ->and(postForeignKeys('columns'))->toHaveKey('categories', ['category_id'])
        ->and(postForeignKeys('foreign_columns'))->toHaveKey('categories', ['id'])
        ->and(postForeignKeys('on_delete'))->toHaveKey('categories', 'set null');
});

test('deleting a category leaves its posts without a category', function (): void {
    $category = Category::factory()->create();
    $post = Post::factory()->for($category)->create();
    $otherPost = Post::factory()->create();

    DB::table('categories')->where('id', $category->id)->delete();

    expect(DB::table('posts')->where('id', $post->id)->value('category_id'))->toBeNull()
        ->and(DB::table('posts')->where('id', $otherPost->id)->value('category_id'))->toBe($otherPost->category_id);
});

test('adding category_id keeps the other posts constraints and indexes', function (): void {
    expect(Schema::hasIndex('posts', ['slug'], 'unique'))->toBeTrue()
        ->and(Schema::hasIndex('posts', ['status', 'published_at']))->toBeTrue();
});

test('running the category_id migration again changes nothing', function (): void {
    $post = Post::factory()->create();
    $columns = Schema::getColumnListing('posts');
    $indexes = count(Schema::getIndexes('posts'));

    runPostCategoryIdMigration();

    expect(Schema::getColumnListing('posts'))->toBe($columns)
        ->and(Schema::getIndexes('posts'))->toHaveCount($indexes)
        ->and(postCategoryForeignKeyCount())->toBe(1)
        ->and(DB::table('posts')->where('id', $post->id)->value('category_id'))->toBe($post->category_id);
});
