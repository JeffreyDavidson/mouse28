<?php

use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runAuthorPivotTablesMigration(): void
{
    $migration = require database_path('migrations/2026_10_03_144332_create_post_user_and_guide_user_tables.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The author pivot tables migration could not be loaded.');
    }

    $migration->up();
}

dataset('author pivot tables', [
    'posts' => ['post_user', 'post_id', 'posts'],
    'guides' => ['guide_user', 'guide_id', 'guides'],
]);

test('each author pivot holds the content key, the user key and a position under a composite primary key', function (string $pivot, string $contentKey): void {
    $nullable = array_column(Schema::getColumns($pivot), 'nullable', 'name');
    $defaults = array_column(Schema::getColumns($pivot), 'default', 'name');

    expect(array_keys($nullable))->toEqualCanonicalizing([$contentKey, 'user_id', 'position'])
        ->and($nullable['position'])
        ->toBeFalse()
        ->and($defaults['position'])
        ->toContain('0')
        ->and(Schema::hasIndex($pivot, [$contentKey, 'user_id'], 'primary'))
        ->toBeTrue();
})->with('author pivot tables');

test('each author pivot cascades deletes from both sides', function (string $pivot, string $contentKey, string $contentTable): void {
    $onDelete = array_column(Schema::getForeignKeys($pivot), 'on_delete', 'foreign_table');

    expect($onDelete)->toEqualCanonicalizing([$contentTable => 'cascade', 'users' => 'cascade']);
})->with('author pivot tables');

test('running the author pivot migration again keeps the tables and their credits', function (): void {
    $author = User::factory()
        ->author()
        ->create();
    Post::factory()
        ->withAuthors($author)
        ->create();
    Guide::factory()
        ->withAuthors($author)
        ->create();

    runAuthorPivotTablesMigration();

    expect(DB::table('post_user')->count())->toBe(1)
        ->and(DB::table('guide_user')->count())
        ->toBe(1);
});
