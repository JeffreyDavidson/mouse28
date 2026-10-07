<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runPostReviewFieldsMigration(): void
{
    $migration = require database_path('migrations/2026_10_07_222557_add_review_fields_to_posts_table.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The post review fields migration could not be loaded.');
    }

    $migration->up();
}

/** How many foreign keys on posts.reviewed_by reference the users table. */
function postReviewerForeignKeyCount(): int
{
    return collect(Schema::getForeignKeys('posts'))
        ->where('columns', ['reviewed_by'])
        ->where('foreign_table', 'users')
        ->count();
}

test('posts gain nullable review notes, reviewer and review time', function (): void {
    $nullable = array_column(Schema::getColumns('posts'), 'nullable', 'name');

    expect($nullable)->toHaveKey('review_notes', true)
        ->toHaveKey('reviewed_by', true)
        ->toHaveKey('reviewed_at', true);
});

test('posts.reviewed_by references users and is set to null when the reviewer is deleted', function (): void {
    $foreignKey = collect(Schema::getForeignKeys('posts'))
        ->firstWhere('columns', ['reviewed_by']);

    expect(postReviewerForeignKeyCount())->toBe(1)
        ->and($foreignKey)->toMatchArray([
            'foreign_table' => 'users',
            'foreign_columns' => ['id'],
            'on_delete' => 'set null',
        ]);
});

test('deleting a reviewer keeps the post and its notes but forgets the reviewer', function (): void {
    $reviewer = User::factory()->create();
    $post = Post::factory()->create(['review_notes' => 'Check the park hours.', 'reviewed_by' => $reviewer->id]);

    DB::table('users')->where('id', $reviewer->id)->delete();

    expect(DB::table('posts')->where('id', $post->id)->first(['review_notes', 'reviewed_by']))
        ->review_notes->toBe('Check the park hours.')
        ->reviewed_by->toBeNull();
});

test('adding the review fields keeps the other posts foreign keys and indexes', function (): void {
    expect(Schema::hasIndex('posts', ['slug'], 'unique'))->toBeTrue()
        ->and(Schema::hasIndex('posts', ['status', 'published_at']))->toBeTrue()
        ->and(Schema::hasIndex('posts', ['category_id']))->toBeTrue()
        ->and(collect(Schema::getForeignKeys('posts'))->pluck('foreign_table')->all())->toContain('categories');
});

test('running the review fields migration again changes nothing', function (): void {
    $post = Post::factory()->create(['review_notes' => 'Keep this note.']);
    $columns = Schema::getColumnListing('posts');
    $indexes = count(Schema::getIndexes('posts'));

    runPostReviewFieldsMigration();

    expect(Schema::getColumnListing('posts'))->toBe($columns)
        ->and(Schema::getIndexes('posts'))->toHaveCount($indexes)
        ->and(postReviewerForeignKeyCount())->toBe(1)
        ->and(DB::table('posts')->where('id', $post->id)->value('review_notes'))->toBe('Keep this note.');
});
