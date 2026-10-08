<?php

use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runDropLegacyPostColumnsMigration(): void
{
    $migration = require database_path('migrations/2026_10_05_150608_drop_legacy_post_link_category_and_author_columns.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The drop legacy post columns migration could not be loaded.');
    }

    $migration->up();
}

// The migrated schema no longer has the legacy columns, so put them back to look like production did before the drop.
beforeEach(function (): void {
    Schema::table('posts', function (Blueprint $table): void {
        $table->foreignId('episode_id')
            ->nullable()
            ->constrained()
            ->nullOnDelete();
        $table->string('category')
            ->nullable();
        $table->string('author')
            ->nullable();
    });

    Schema::table('guides', function (Blueprint $table): void {
        $table->string('author')
            ->default('both');
    });
});

dataset('author tables', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'guides' => [fn () => Guide::factory(), 'guides'],
]);

test('the migration drops the legacy columns', function (): void {
    // Act
    runDropLegacyPostColumnsMigration();

    // Assert
    $remaining = collect([['posts', 'episode_id'], ['posts', 'category'], ['posts', 'author'], ['guides', 'author']])
        ->filter(fn (array $pair): bool => Schema::hasColumn(...$pair))
        ->all();

    expect($remaining)->toBeEmpty();
});

test('the migration keeps the category link and the posts indexes', function (): void {
    // Arrange
    $category = Category::factory()->create();
    $post = Post::factory()->create(['category_id' => $category->id]);

    // Act
    runDropLegacyPostColumnsMigration();

    // Assert
    expect(DB::table('posts')
        ->where('id', $post->id)
        ->value('category_id'))->toBe($category->id)
        ->and(Schema::hasIndex('posts', ['slug'], 'unique'))
        ->toBeTrue()
        ->and(Schema::hasIndex('posts', ['status', 'published_at']))
        ->toBeTrue();
});

test('the migration refuses to drop anything while a post has an episode link missing from the pivot', function (): void {
    // Arrange
    $post = Post::factory()->create();
    DB::table('posts')
        ->where('id', $post->id)
        ->update(['episode_id' => Episode::factory()
            ->create()
            ->id]);

    // Act
    $run = fn () => runDropLegacyPostColumnsMigration();

    // Assert
    expect($run)->toThrow(RuntimeException::class, '1 post(s) still have an episode_id with no matching episode_post row.');
    expect(Schema::hasColumn('posts', 'category'))->toBeTrue();
});

test('the migration refuses to drop anything while a post has a category but no category_id', function (): void {
    // Arrange
    $post = Post::factory()->create(['category_id' => null]);
    DB::table('posts')
        ->where('id', $post->id)
        ->update(['category' => 'disney-tips']);

    // Act
    $run = fn () => runDropLegacyPostColumnsMigration();

    // Assert
    expect($run)->toThrow(RuntimeException::class, '1 post(s) still have a category with no category_id.');
    expect(Schema::hasColumn('posts', 'author'))->toBeTrue();
});

test('the migration refuses to drop anything while a row has an author but no credited author user', function (PostFactory|GuideFactory $factory, string $table): void {
    // Arrange
    $record = $factory->createOne();
    $record->authors()
        ->detach();
    DB::table($table)
        ->where('id', $record->id)
        ->update(['author' => 'jeffrey']);

    // Act
    $run = fn () => runDropLegacyPostColumnsMigration();

    // Assert
    $pivot = $table === 'posts' ? 'post_user' : 'guide_user';
    expect($run)->toThrow(RuntimeException::class, "1 {$table} row(s) still have an author with no {$pivot} row.")
        ->and(Schema::hasColumn('posts', 'episode_id'))
        ->toBeTrue();
})->with('author tables');

test('the migration can run again after the columns are gone', function (): void {
    // Arrange
    runDropLegacyPostColumnsMigration();

    // Act
    runDropLegacyPostColumnsMigration();

    // Assert
    expect(Schema::hasColumn('posts', 'episode_id'))->toBeFalse();
});
