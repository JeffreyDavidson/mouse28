<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function podcastShapeMigration(string $file): Migration
{
    $migration = require database_path("migrations/{$file}");

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException("The {$file} migration could not be loaded.");
    }

    return $migration;
}

function runEpisodePodcastBackfill(): void
{
    $migration = podcastShapeMigration('2026_10_06_231450_add_podcast_and_guest_fields_to_episodes_table.php');

    if (! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The episode migration has no up() method.');
    }

    $migration->up();
}

function insertEpisodeRow(string $slug, ?int $podcastId = null, string $status = 'published'): int
{
    $highestNumber = DB::table('episodes')->max('episode_number');

    return DB::table('episodes')->insertGetId([
        'title' => $slug,
        'slug' => $slug,
        'description' => 'Description.',
        'episode_number' => is_numeric($highestNumber) ? (int) $highestNumber + 1 : 1,
        'season_number' => 1,
        'status' => $status,
        'podcast_id' => $podcastId,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

test('podcasts gain the multi-show columns with the existing show slugged and active', function (): void {
    expect(Schema::hasColumns('podcasts', ['slug', 'long_description', 'color', 'is_active', 'sort_order', 'deleted_at']))->toBeTrue()
        ->and(Schema::hasColumns('episodes', ['podcast_id', 'guest_name', 'guest_title', 'guest_url']))->toBeTrue();
});

test('existing episodes are attached to the one podcast and the backfill can run again', function (): void {
    $podcastId = DB::table('podcasts')->insertGetId(['name' => 'Show', 'slug' => 'show', 'created_at' => now(), 'updated_at' => now()]);
    $first = insertEpisodeRow('first');
    $second = insertEpisodeRow('second');

    runEpisodePodcastBackfill();
    runEpisodePodcastBackfill();

    expect(DB::table('episodes')->whereIn('id', [$first, $second])->pluck('podcast_id')->unique()->values()->all())->toBe([$podcastId]);
});

test('existing episodes without any podcast get the default Mouse28 show', function (): void {
    $episode = insertEpisodeRow('orphan');

    runEpisodePodcastBackfill();

    $podcast = DB::table('podcasts')->sole();
    expect($podcast->name)->toBe('Mouse28')
        ->and($podcast->slug)->toBe('mouse28')
        ->and(DB::table('episodes')->where('id', $episode)->value('podcast_id'))->toBe($podcast->id);
});

test('the backfill refuses to guess when several podcasts exist', function (): void {
    DB::table('podcasts')->insert([
        ['name' => 'One', 'slug' => 'one', 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Two', 'slug' => 'two', 'created_at' => now(), 'updated_at' => now()],
    ]);
    insertEpisodeRow('orphan');

    expect(fn () => runEpisodePodcastBackfill())->toThrow(RuntimeException::class);
});

test('the podcast slug backfill names the existing show mouse28', function (): void {
    $migration = podcastShapeMigration('2026_10_06_231448_add_show_fields_to_podcasts_table.php');
    $id = DB::table('podcasts')->insertGetId(['name' => 'Mouse28', 'slug' => null, 'created_at' => now(), 'updated_at' => now()]);

    new ReflectionMethod($migration, 'backfillSlugs')->invoke($migration);

    expect(DB::table('podcasts')->where('id', $id)->value('slug'))->toBe('mouse28');
});

test('deleting a podcast row deletes its episodes in the database too', function (): void {
    $podcastId = DB::table('podcasts')->insertGetId(['name' => 'Show', 'slug' => 'show', 'created_at' => now(), 'updated_at' => now()]);
    $episode = insertEpisodeRow('linked', $podcastId);

    DB::table('podcasts')->where('id', $podcastId)->delete();

    expect(DB::table('episodes')->where('id', $episode)->exists())->toBeFalse();
});
