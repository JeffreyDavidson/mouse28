<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\NewsletterIssueFactory;
use Database\Factories\PostFactory;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

// A later migration drops `is_published`; put it back so rows look like they did before `status` existed.
beforeEach(function (): void {
    foreach (['posts', 'episodes', 'guides', 'newsletter_issues'] as $table) {
        if (Schema::hasColumn($table, 'is_published')) {
            continue;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->boolean('is_published')
                ->default(false);
        });
    }
});

function runPublishStatusMigration(): void
{
    $migration = require database_path('migrations/2026_10_02_195819_add_publish_status_to_editorial_content.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The publish status migration could not be loaded.');
    }

    $migration->up();
}

/**
 * Creates a row through the factory, then rewrites its publication columns
 * directly so it looks like a row written before the status column existed.
 */
function legacyPublicationRow(PostFactory|EpisodeFactory|GuideFactory|NewsletterIssueFactory $factory, bool $isPublished, ?string $publishedAt, bool $trashed = false): int
{
    $record = $factory->createOne();

    DB::table($record->getTable())
        ->where('id', $record->id)
        ->update([
            'status' => 'draft',
            'is_published' => $isPublished,
            'published_at' => $publishedAt,
            'created_at' => '2026-08-01 08:00:00',
            'deleted_at' => $trashed ? '2026-09-01 08:00:00' : null,
        ]);

    return $record->id;
}

/**
 * Reads every row, trashed ones included, through the model casts.
 *
 * @return array<int, array{status: string, published_at: ?string}>
 */
function publicationColumns(PostFactory|EpisodeFactory|GuideFactory|NewsletterIssueFactory $factory): array
{
    $columns = [];

    foreach ($factory->newModel()
        ->newQueryWithoutScopes()
        ->orderBy('id')
        ->get() as $record) {
        $columns[$record->id] = [
            'status' => $record->publishStatus()
                ->value,
            'published_at' => $record->published_at?->toDateTimeString(),
        ];
    }

    return $columns;
}

dataset('legacy content tables', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'episodes' => [fn () => Episode::factory(), 'episodes'],
    'guides' => [fn () => Guide::factory(), 'guides'],
    'newsletter issues' => [fn () => NewsletterIssue::factory(), 'newsletter_issues'],
]);

test('the migration adds a draft-by-default status indexed with the publish date', function (PostFactory|EpisodeFactory|GuideFactory|NewsletterIssueFactory $factory, string $table): void {
    $statusDefaults = array_column(Schema::getColumns($table), 'default', 'name');

    expect(Schema::hasColumn($table, 'status'))->toBeTrue()
        ->and(Schema::hasIndex($table, ['status', 'published_at']))
        ->toBeTrue()
        ->and($statusDefaults['status'])
        ->toBeIn(["'draft'", 'draft'])
        ->and(Schema::hasColumn($table, 'is_published'))
        ->toBeTrue();
})->with('legacy content tables');

test('the backfill maps the legacy flag and publish date to a status', function (PostFactory|EpisodeFactory|GuideFactory|NewsletterIssueFactory $factory, string $table, bool $isPublished, ?string $publishedAt, bool $trashed, string $status, ?string $expectedDate): void {
    Date::setTestNow('2026-10-02 12:00:00');
    $id = legacyPublicationRow($factory, $isPublished, $publishedAt, $trashed);

    runPublishStatusMigration();

    expect(publicationColumns($factory)[$id])->toBe(['status' => $status, 'published_at' => $expectedDate]);
})->with('legacy content tables')
    ->with([
        'unpublished without a date' => [false, null, false, 'draft', null],
        'unpublished with a past date' => [false, '2026-09-01 09:00:00', false, 'draft', '2026-09-01 09:00:00'],
        'unpublished with a future date' => [false, '2026-10-09 09:00:00', false, 'draft', '2026-10-09 09:00:00'],
        'published in the past' => [true, '2026-09-01 09:00:00', false, 'published', '2026-09-01 09:00:00'],
        'published exactly now' => [true, '2026-10-02 12:00:00', false, 'published', '2026-10-02 12:00:00'],
        'published with a future date' => [true, '2026-10-09 09:00:00', false, 'scheduled', '2026-10-09 09:00:00'],
        'published without a date' => [true, null, false, 'published', '2026-08-01 08:00:00'],
        'trashed and published' => [true, '2026-09-01 09:00:00', true, 'published', '2026-09-01 09:00:00'],
        'trashed and scheduled' => [true, '2026-10-09 09:00:00', true, 'scheduled', '2026-10-09 09:00:00'],
    ]);

test('the backfill leaves statuses already set alone', function (PostFactory|EpisodeFactory|GuideFactory|NewsletterIssueFactory $factory, string $table): void {
    Date::setTestNow('2026-10-02 12:00:00');
    $id = legacyPublicationRow($factory, true, '2026-09-01 09:00:00');
    DB::table($table)
        ->where('id', $id)
        ->update(['status' => 'in_review']);

    runPublishStatusMigration();

    expect(publicationColumns($factory)[$id]['status'])->toBe('in_review');
})->with('legacy content tables');

test('running the migration again changes nothing', function (PostFactory|EpisodeFactory|GuideFactory|NewsletterIssueFactory $factory, string $table): void {
    Date::setTestNow('2026-10-02 12:00:00');
    legacyPublicationRow($factory, false, null);
    legacyPublicationRow($factory, true, '2026-09-01 09:00:00');
    legacyPublicationRow($factory, true, '2026-10-09 09:00:00');
    legacyPublicationRow($factory, true, null);
    runPublishStatusMigration();
    $afterFirstRun = publicationColumns($factory);

    Date::setTestNow('2026-10-20 12:00:00');
    runPublishStatusMigration();

    expect(publicationColumns($factory))->toBe($afterFirstRun);
})->with('legacy content tables');

test('the migration refuses to finish while a published row is still a draft', function (PostFactory|EpisodeFactory|GuideFactory|NewsletterIssueFactory $factory, string $table): void {
    Date::setTestNow('2026-10-02 12:00:00');
    $id = legacyPublicationRow($factory, true, '2026-09-01 09:00:00');
    // Simulates the previous release writing the row back as a draft after each backfill step.
    DB::listen(function (QueryExecuted $query) use ($table, $id): void {
        if (str_starts_with($query->sql, 'update') && str_contains($query->sql, $table) && str_contains($query->sql, 'is_published')) {
            DB::table($table)
                ->where('id', $id)
                ->update(['status' => 'draft']);
        }
    });

    expect(fn () => runPublishStatusMigration())
        ->toThrow(RuntimeException::class, "1 published {$table} row(s) still have the draft status after the backfill.");
})->with('legacy content tables');
