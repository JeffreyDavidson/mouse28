<?php

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

// A later migration drops `cover_image`; put it back so these rows look like they did before the backfill.
beforeEach(function (): void {
    foreach (['posts', 'episodes', 'guides', 'podcasts'] as $table) {
        if (Schema::hasColumn($table, 'cover_image')) {
            continue;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->string('cover_image')->nullable();
        });
    }
});

function runStoredMediaPathsMigration(): void
{
    $migration = require database_path('migrations/2026_10_03_215858_add_stored_media_paths_to_content_tables.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The stored media paths migration could not be loaded.');
    }

    $migration->up();
}

/**
 * Inserts a row directly so it looks like one written before the new path column existed.
 */
function legacyCoverRow(string $table, ?string $cover, bool $trashed = false): int
{
    $now = Date::now();
    $row = match ($table) {
        'posts' => ['title' => 'Legacy row', 'slug' => uniqid('legacy-'), 'deleted_at' => $trashed ? $now : null],
        'guides' => ['title' => 'Legacy row', 'slug' => uniqid('legacy-'), 'category' => 'accessibility', 'deleted_at' => $trashed ? $now : null],
        'episodes' => ['title' => 'Legacy row', 'slug' => uniqid('legacy-'), 'episode_number' => random_int(1, 99_999), 'deleted_at' => $trashed ? $now : null],
        // The single-row podcasts table has no soft deletes.
        default => ['name' => 'Legacy podcast'],
    };

    return DB::table($table)->insertGetId([...$row, 'cover_image' => $cover, 'created_at' => $now, 'updated_at' => $now]);
}

/**
 * @return array{cover_image: mixed, path: mixed}
 */
function storedMediaColumns(string $table, string $column, int $id): array
{
    $row = DB::table($table)->where('id', $id)->first(['cover_image', $column]);

    return ['cover_image' => $row?->cover_image, 'path' => $row?->{$column}];
}

dataset('stored media tables', [
    'posts' => ['posts', 'featured_image_path'],
    'episodes' => ['episodes', 'featured_image_path'],
    'guides' => ['guides', 'featured_image_path'],
    'podcasts' => ['podcasts', 'cover_image_path'],
]);

test('the migration adds a nullable path column and keeps cover_image', function (string $table, string $column): void {
    $nullable = array_column(Schema::getColumns($table), 'nullable', 'name');

    expect(Schema::hasColumn($table, $column))->toBeTrue()
        ->and($nullable[$column])->toBeTrue()
        ->and(Schema::hasColumn($table, 'cover_image'))->toBeTrue();
})->with('stored media tables');

test('the backfill copies cover_image into the new path column', function (string $table, string $column, ?string $cover, bool $trashed): void {
    $id = legacyCoverRow($table, $cover, $trashed);
    DB::table($table)->where('id', $id)->update([$column => null]);

    runStoredMediaPathsMigration();

    expect(storedMediaColumns($table, $column, $id))->toBe(['cover_image' => $cover, 'path' => $cover]);
})->with('stored media tables')->with([
    'a stored cover' => ['covers/cover.webp', false],
    'a trashed row' => ['covers/trashed.webp', true],
    'no cover' => [null, false],
]);

test('the backfill leaves an empty cover_image without a path', function (string $table, string $column): void {
    $id = legacyCoverRow($table, '');
    DB::table($table)->where('id', $id)->update([$column => null]);

    runStoredMediaPathsMigration();

    expect(storedMediaColumns($table, $column, $id))->toBe(['cover_image' => '', 'path' => '']);
})->with('stored media tables');

test('the backfill leaves a path already written alone', function (string $table, string $column): void {
    $id = legacyCoverRow($table, 'covers/old.webp');
    DB::table($table)->where('id', $id)->update([$column => 'covers/new.webp']);

    runStoredMediaPathsMigration();

    expect(storedMediaColumns($table, $column, $id))->toBe(['cover_image' => 'covers/old.webp', 'path' => 'covers/new.webp']);
})->with('stored media tables');

test('running the stored media migration again changes nothing', function (string $table, string $column): void {
    $ids = [legacyCoverRow($table, 'covers/one.webp'), legacyCoverRow($table, null)];
    DB::table($table)->whereIn('id', $ids)->update([$column => null]);
    runStoredMediaPathsMigration();
    $afterFirstRun = DB::table($table)->orderBy('id')->get(['id', 'cover_image', $column])->toArray();

    runStoredMediaPathsMigration();

    expect(DB::table($table)->orderBy('id')->get(['id', 'cover_image', $column])->toArray())->toEqual($afterFirstRun);
})->with('stored media tables');

test('the migration refuses to finish while a row has a cover_image but no path', function (string $table, string $column): void {
    $id = legacyCoverRow($table, 'covers/kept.webp');
    DB::table($table)->where('id', $id)->update([$column => null]);
    // Simulates the previous release clearing the path right after the backfill copied it.
    $cleared = false;
    DB::listen(function (QueryExecuted $query) use ($table, $column, $id, &$cleared): void {
        if (! $cleared && str_starts_with($query->sql, 'update') && str_contains($query->sql, $table) && str_contains($query->sql, 'cover_image')) {
            $cleared = true;
            DB::table($table)->where('id', $id)->update([$column => null]);
        }
    });

    expect(fn () => runStoredMediaPathsMigration())
        ->toThrow(RuntimeException::class, "1 {$table} row(s) still have a cover_image but no {$column} after the backfill.");
})->with('stored media tables');
