<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\NewsletterIssueFactory;
use Database\Factories\PostFactory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runDropLegacyPublishedAndBodyMigration(): void
{
    $migration = require database_path('migrations/2026_10_05_143704_drop_legacy_published_and_body_columns.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The drop legacy published and body migration could not be loaded.');
    }

    $migration->up();
}

// The migrated schema no longer has the legacy columns, so put them back to look like production did before the drop.
beforeEach(function (): void {
    foreach (['posts', 'episodes', 'guides', 'newsletter_issues'] as $table) {
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->boolean('is_published')->default(false);
        });
    }

    foreach (['posts', 'guides'] as $table) {
        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->longText('body')->nullable();
        });
    }
});

dataset('published flag tables', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'episodes' => [fn () => Episode::factory(), 'episodes'],
    'guides' => [fn () => Guide::factory(), 'guides'],
    'newsletter issues' => [fn () => NewsletterIssue::factory(), 'newsletter_issues'],
]);

dataset('body tables', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'guides' => [fn () => Guide::factory(), 'guides'],
]);

test('the migration drops is_published and body', function (): void {
    // Act
    runDropLegacyPublishedAndBodyMigration();

    // Assert
    $remaining = collect([
        ['posts', 'is_published'], ['episodes', 'is_published'], ['guides', 'is_published'], ['newsletter_issues', 'is_published'],
        ['posts', 'body'], ['guides', 'body'],
    ])->filter(fn (array $pair): bool => Schema::hasColumn(...$pair))->all();

    expect($remaining)->toBeEmpty();
});

test('the migration keeps status and content', function (PostFactory|GuideFactory $factory, string $table): void {
    // Arrange
    $record = $factory->createOne(['content' => 'Kept content.']);

    // Act
    runDropLegacyPublishedAndBodyMigration();

    // Assert
    expect(DB::table($table)->where('id', $record->id)->value('content'))->toBe('Kept content.')
        ->and(DB::table($table)->where('id', $record->id)->value('status'))->toBe($record->publishStatus()->value);
})->with('body tables');

test('the migration refuses to drop anything while a published row is still a draft', function (PostFactory|EpisodeFactory|GuideFactory|NewsletterIssueFactory $factory, string $table): void {
    // Arrange
    $record = $factory->draft()->createOne();
    DB::table($table)->where('id', $record->id)->update(['is_published' => true]);

    // Act
    $run = fn () => runDropLegacyPublishedAndBodyMigration();

    // Assert
    expect($run)->toThrow(RuntimeException::class, "1 published {$table} row(s) still have the draft status.");
    expect(Schema::hasColumn('posts', 'is_published'))->toBeTrue()
        ->and(Schema::hasColumn('posts', 'body'))->toBeTrue();
})->with('published flag tables');

test('the migration refuses to drop anything while a row has a body but no content', function (PostFactory|GuideFactory $factory, string $table): void {
    // Arrange
    $record = $factory->createOne();
    DB::table($table)->where('id', $record->id)->update(['body' => 'Only in body.', 'content' => null]);

    // Act
    $run = fn () => runDropLegacyPublishedAndBodyMigration();

    // Assert
    expect($run)->toThrow(RuntimeException::class, "1 {$table} row(s) still have a body but no content.");
    expect(Schema::hasColumn('posts', 'is_published'))->toBeTrue();
})->with('body tables');

test('the migration can run again after the columns are gone', function (): void {
    // Arrange
    runDropLegacyPublishedAndBodyMigration();

    // Act
    runDropLegacyPublishedAndBodyMigration();

    // Assert
    expect(Schema::hasColumn('posts', 'body'))->toBeFalse();
});
