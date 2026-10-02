<?php

use App\Models\Guide;
use App\Models\Post;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runContentColumnMigration(): void
{
    $migration = require database_path('migrations/2026_10_02_212427_add_content_to_posts_and_guides.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The content column migration could not be loaded.');
    }

    $migration->up();
}

/**
 * Creates a row through the factory, then rewrites its text columns directly so
 * it looks like a row written before the content column existed.
 */
function legacyBodyRow(PostFactory|GuideFactory $factory, string $body, bool $trashed = false): int
{
    $record = $factory->createOne();

    DB::table($record->getTable())->where('id', $record->id)->update([
        'body' => $body,
        'content' => null,
        'deleted_at' => $trashed ? '2026-09-01 08:00:00' : null,
    ]);

    return $record->id;
}

/**
 * Reads both text columns of every row, trashed ones included.
 *
 * @return array<int, array{body: string, content: ?string}>
 */
function contentColumns(PostFactory|GuideFactory $factory): array
{
    $columns = [];

    foreach ($factory->newModel()->newQueryWithoutScopes()->orderBy('id')->get() as $record) {
        $columns[$record->id] = ['body' => $record->body, 'content' => $record->content];
    }

    return $columns;
}

dataset('legacy body tables', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'guides' => [fn () => Guide::factory(), 'guides'],
]);

test('the migration adds a nullable content column and keeps the body column', function (PostFactory|GuideFactory $factory, string $table): void {
    $nullable = array_column(Schema::getColumns($table), 'nullable', 'name');

    expect(Schema::hasColumn($table, 'content'))->toBeTrue()
        ->and($nullable['content'])->toBeTrue()
        ->and(Schema::hasColumn($table, 'body'))->toBeTrue();
})->with('legacy body tables');

test('the backfill copies the body into the content unchanged', function (PostFactory|GuideFactory $factory, string $table, string $body, bool $trashed): void {
    $id = legacyBodyRow($factory, $body, $trashed);

    runContentColumnMigration();

    expect(contentColumns($factory)[$id])->toBe(['body' => $body, 'content' => $body]);
})->with('legacy body tables')->with([
    'plain text' => ['Plan a flexible arrival.', false],
    'an empty body' => ['', false],
    'unicode and markdown' => ["## Café ✨ planning\n\n- **Bold** 日本語 🐭\n- [Official](https://example.com/a?b=1&c=2)\n\n> Quote with \"quotes\" and 'apostrophes'", false],
    'a trashed row' => ['Archived planning advice.', true],
]);

test('the backfill leaves content already written alone', function (PostFactory|GuideFactory $factory, string $table): void {
    $id = legacyBodyRow($factory, 'Old body text.');
    DB::table($table)->where('id', $id)->update(['content' => 'Newer content text.']);

    runContentColumnMigration();

    expect(contentColumns($factory)[$id])->toBe(['body' => 'Old body text.', 'content' => 'Newer content text.']);
})->with('legacy body tables');

test('running the content migration again changes nothing', function (PostFactory|GuideFactory $factory, string $table): void {
    legacyBodyRow($factory, 'First body.');
    legacyBodyRow($factory, '');
    legacyBodyRow($factory, 'Trashed body.', trashed: true);
    runContentColumnMigration();
    $afterFirstRun = contentColumns($factory);

    runContentColumnMigration();

    expect(contentColumns($factory))->toBe($afterFirstRun);
})->with('legacy body tables');

test('the migration refuses to finish while a row has a body but no content', function (PostFactory|GuideFactory $factory, string $table): void {
    $id = legacyBodyRow($factory, 'Body that must not be lost.');
    // Simulates the previous release clearing the content after the backfill copied it.
    DB::listen(function (QueryExecuted $query) use ($table, $id): void {
        if (str_starts_with($query->sql, 'update') && str_contains($query->sql, $table) && str_contains($query->sql, 'body')) {
            DB::table($table)->where('id', $id)->update(['content' => null]);
        }
    });

    expect(fn () => runContentColumnMigration())
        ->toThrow(RuntimeException::class, "1 {$table} row(s) still have a body but no content after the backfill.");
})->with('legacy body tables');
