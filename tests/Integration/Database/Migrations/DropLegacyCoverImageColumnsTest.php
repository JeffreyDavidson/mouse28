<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runDropLegacyCoverImageMigration(): void
{
    $migration = require database_path('migrations/2026_10_04_221212_drop_legacy_cover_image_columns.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The drop legacy cover image migration could not be loaded.');
    }

    $migration->up();
}

// The migrated schema no longer has the legacy column, so put it back to look like production did before the drop.
beforeEach(function (): void {
    foreach (['posts', 'episodes', 'guides', 'podcasts'] as $table) {
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->string('cover_image')->nullable();
        });
    }
});

function insertLegacyRow(string $table, ?string $cover, ?string $path): int
{
    $now = Date::now();
    $column = $table === 'podcasts' ? 'cover_image_path' : 'featured_image_path';
    $row = match ($table) {
        'posts' => ['title' => 'Legacy row', 'slug' => uniqid('legacy-'), 'body' => ''],
        'guides' => ['title' => 'Legacy row', 'slug' => uniqid('legacy-'), 'body' => '', 'category' => 'accessibility'],
        'episodes' => ['title' => 'Legacy row', 'slug' => uniqid('legacy-'), 'episode_number' => random_int(1, 99_999)],
        default => ['name' => 'Legacy podcast'],
    };

    return DB::table($table)->insertGetId([...$row, 'cover_image' => $cover, $column => $path, 'created_at' => $now, 'updated_at' => $now]);
}

dataset('legacy cover tables', [
    'posts' => ['posts'],
    'episodes' => ['episodes'],
    'guides' => ['guides'],
    'podcasts' => ['podcasts'],
]);

test('the migration drops cover_image from every table', function (): void {
    // Arrange
    insertLegacyRow('posts', 'posts/a.webp', 'posts/a.webp');

    // Act
    runDropLegacyCoverImageMigration();

    // Assert
    $remaining = collect(['posts', 'episodes', 'guides', 'podcasts'])
        ->filter(fn (string $table): bool => Schema::hasColumn($table, 'cover_image'))
        ->all();

    expect($remaining)->toBeEmpty();
});

test('the migration keeps the stored image path', function (string $table): void {
    // Arrange
    $column = $table === 'podcasts' ? 'cover_image_path' : 'featured_image_path';
    $id = insertLegacyRow($table, 'covers/a.webp', 'covers/a.webp');

    // Act
    runDropLegacyCoverImageMigration();

    // Assert
    expect(DB::table($table)->where('id', $id)->value($column))->toBe('covers/a.webp');
})->with('legacy cover tables');

test('the migration refuses to drop anything while a row has a cover_image but no path', function (string $table): void {
    // Arrange
    insertLegacyRow($table, 'covers/only-legacy.webp', null);

    // Act
    $run = fn () => runDropLegacyCoverImageMigration();

    // Assert
    expect($run)->toThrow(RuntimeException::class, "1 {$table} row(s) still have a cover_image but no");
    expect(Schema::hasColumn('posts', 'cover_image'))->toBeTrue()
        ->and(Schema::hasColumn('podcasts', 'cover_image'))->toBeTrue();
})->with('legacy cover tables');

test('the migration ignores an empty cover_image without a path', function (string $table): void {
    // Arrange
    insertLegacyRow($table, '', null);

    // Act
    runDropLegacyCoverImageMigration();

    // Assert
    expect(Schema::hasColumn($table, 'cover_image'))->toBeFalse();
})->with('legacy cover tables');

test('the migration can run again after the columns are gone', function (): void {
    // Arrange
    runDropLegacyCoverImageMigration();

    // Act
    runDropLegacyCoverImageMigration();

    // Assert
    expect(Schema::hasColumn('posts', 'cover_image'))->toBeFalse();
});
