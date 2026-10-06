<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runDropLegacyMetaMigration(): void
{
    $migration = require database_path('migrations/2026_10_06_003047_drop_legacy_meta_columns.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The drop legacy meta columns migration could not be loaded.');
    }

    $migration->up();
}

// The migrated schema no longer has the legacy columns, so put them back to look like production did before the drop.
beforeEach(function (): void {
    foreach (['posts', 'episodes', 'guides'] as $table) {
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->string('meta_title')->nullable();
            $blueprint->text('meta_description')->nullable();
            $blueprint->string('og_image')->nullable();
        });
    }
});

dataset('meta tables', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'episodes' => [fn () => Episode::factory(), 'episodes'],
    'guides' => [fn () => Guide::factory(), 'guides'],
]);

/** @return list<string> */
function remainingLegacyMetaColumns(): array
{
    $remaining = [];

    foreach (['posts', 'episodes', 'guides'] as $table) {
        foreach (['meta_title', 'meta_description', 'og_image'] as $column) {
            if (Schema::hasColumn($table, $column)) {
                $remaining[] = "{$table}.{$column}";
            }
        }
    }

    return $remaining;
}

test('the migration drops the legacy meta columns and keeps the SEO rows', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $table): void {
    // Arrange
    $record = $factory->withSeo('Saved title', 'Saved description')->createOne();
    DB::table($table)->where('id', $record->id)->update(['meta_title' => 'Saved title', 'meta_description' => 'Saved description']);

    // Act
    runDropLegacyMetaMigration();

    // Assert
    expect(remainingLegacyMetaColumns())->toBeEmpty()
        ->and($record->refresh()->seo->title)->toBe('Saved title')
        ->and($record->seo->description)->toBe('Saved description');
})->with('meta tables');

test('the migration ignores empty legacy values', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $table): void {
    // Arrange
    $record = $factory->createOne();
    DB::table($table)->where('id', $record->id)->update(['meta_title' => '', 'meta_description' => null, 'og_image' => '']);

    // Act
    runDropLegacyMetaMigration();

    // Assert
    expect(remainingLegacyMetaColumns())->toBeEmpty();
})->with('meta tables');

test('the migration refuses to drop anything while a legacy meta value has no SEO value', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $table, string $column): void {
    // Arrange
    $record = $factory->createOne();
    DB::table($table)->where('id', $record->id)->update([$column => 'Only in the old column']);

    // Act
    $run = fn () => runDropLegacyMetaMigration();

    // Assert
    expect($run)->toThrow(RuntimeException::class, "1 {$table} meta value(s) have no matching SEO value, so the legacy columns were not dropped.")
        ->and(Schema::hasColumn('posts', 'meta_title'))->toBeTrue();
})->with('meta tables')->with([
    'a title' => ['meta_title'],
    'a description' => ['meta_description'],
]);

test('the migration refuses to drop anything while an og_image would be lost', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $table): void {
    // Arrange
    $record = $factory->createOne();
    DB::table($table)->where('id', $record->id)->update(['og_image' => 'posts/og/social.webp']);

    // Act
    $run = fn () => runDropLegacyMetaMigration();

    // Assert
    expect($run)->toThrow(RuntimeException::class, "1 {$table} row(s) still have an og_image, which is not stored anywhere else, so the legacy columns were not dropped.")
        ->and(Schema::hasColumn('posts', 'og_image'))->toBeTrue();
})->with('meta tables');

test('the migration can run again after the columns are gone', function (): void {
    // Arrange
    runDropLegacyMetaMigration();

    // Act
    runDropLegacyMetaMigration();

    // Assert
    expect(remainingLegacyMetaColumns())->toBeEmpty();
});
