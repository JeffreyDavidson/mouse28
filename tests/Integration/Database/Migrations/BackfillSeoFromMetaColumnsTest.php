<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

function backfillSeoMigration(): Migration
{
    $migration = require database_path('migrations/2026_10_05_174142_backfill_seo_from_meta_columns.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The SEO backfill migration could not be loaded.');
    }

    return $migration;
}

function runSeoBackfill(): void
{
    $migration = backfillSeoMigration();

    if (! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The SEO backfill migration has no up() method.');
    }

    $migration->up();
}

/** Runs the migration's private guard on its own, so a state the backfill itself would have fixed can be checked. */
function runSeoBackfillGuard(string $table, string $modelType): void
{
    $migration = backfillSeoMigration();

    new ReflectionMethod($migration, 'assertEveryMetaValueWasCopied')->invoke($migration, $table, $modelType);
}

/**
 * Creates a record through the factory, then writes legacy meta values straight
 * into its table and removes the SEO row the model created, so it looks like a
 * row written before the SEO table existed.
 */
function legacyMetaRecord(PostFactory|EpisodeFactory|GuideFactory $factory, ?string $title, ?string $description, bool $trashed = false): int
{
    $record = $factory->createOne();

    DB::table($record->getTable())->where('id', $record->id)->update([
        'meta_title' => $title,
        'meta_description' => $description,
        'deleted_at' => $trashed ? '2026-09-01 08:00:00' : null,
    ]);
    DB::table('seo')->where('model_type', $record::class)->where('model_id', $record->id)->delete();

    return $record->id;
}

/** @return array{title: mixed, description: mixed}|null */
function savedSeo(string $modelType, int $id): ?array
{
    $row = DB::table('seo')->where('model_type', $modelType)->where('model_id', $id)->first();

    return $row === null ? null : ['title' => $row->title, 'description' => $row->description];
}

dataset('seo models', [
    'posts' => [fn () => Post::factory(), Post::class],
    'episodes' => [fn () => Episode::factory(), Episode::class],
    'guides' => [fn () => Guide::factory(), Guide::class],
]);

test('the backfill copies meta values into SEO rows, soft-deleted records included', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $modelType): void {
    // Arrange
    $live = legacyMetaRecord($factory, 'Live title', 'Live description');
    $trashed = legacyMetaRecord($factory, 'Trashed title', null, trashed: true);

    // Act
    runSeoBackfill();

    // Assert
    expect(savedSeo($modelType, $live))->toBe(['title' => 'Live title', 'description' => 'Live description'])
        ->and(savedSeo($modelType, $trashed))->toBe(['title' => 'Trashed title', 'description' => null]);
})->with('seo models');

test('the backfill fills the empty parts of an existing SEO row', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $modelType): void {
    // Arrange
    $id = legacyMetaRecord($factory, 'Legacy title', 'Legacy description');
    DB::table('seo')->insert(['model_type' => $modelType, 'model_id' => $id, 'title' => null, 'description' => 'Edited description', 'created_at' => now(), 'updated_at' => now()]);

    // Act
    runSeoBackfill();

    // Assert
    expect(savedSeo($modelType, $id))->toBe(['title' => 'Legacy title', 'description' => 'Edited description']);
})->with('seo models');

test('the backfill never overwrites a value saved in the SEO row and can run again', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $modelType): void {
    // Arrange
    $id = legacyMetaRecord($factory, 'Legacy title', 'Legacy description');
    runSeoBackfill();
    DB::table('seo')->where('model_type', $modelType)->where('model_id', $id)->update(['title' => 'Edited in the admin']);

    // Act
    runSeoBackfill();

    // Assert
    expect(savedSeo($modelType, $id))->toBe(['title' => 'Edited in the admin', 'description' => 'Legacy description'])
        ->and(DB::table('seo')->where('model_type', $modelType)->where('model_id', $id)->count())->toBe(1);
})->with('seo models');

test('the backfill leaves records without meta values alone', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $modelType): void {
    // Arrange
    $id = legacyMetaRecord($factory, null, '');

    // Act
    runSeoBackfill();

    // Assert
    expect(savedSeo($modelType, $id))->toBeNull();
})->with('seo models');

test('the backfill guard refuses a meta value that has no SEO value', function (string $table, string $modelType): void {
    // Arrange
    $factory = match ($table) {
        'posts' => Post::factory(),
        'episodes' => Episode::factory(),
        default => Guide::factory(),
    };
    legacyMetaRecord($factory, 'Only in the old column', null);

    // Act
    $run = fn () => runSeoBackfillGuard($table, $modelType);

    // Assert
    expect($run)->toThrow(RuntimeException::class, "1 {$table} meta value(s) still have no matching SEO value after the backfill.");
})->with([
    'posts' => ['posts', Post::class],
    'episodes' => ['episodes', Episode::class],
    'guides' => ['guides', Guide::class],
]);
