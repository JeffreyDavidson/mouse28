<?php

use App\Models\Episode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runDropLegacyEpisodeMediaMigration(): void
{
    $migration = require database_path('migrations/2026_10_07_031500_drop_legacy_episode_media_and_podcast_social_columns.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The drop legacy episode media migration could not be loaded.');
    }

    $migration->up();
}

/** @return list<string> */
function remainingLegacyEpisodeMediaColumns(): array
{
    $columns = [
        'episodes' => ['audio_url', 'audio_path', 'apple_url', 'spotify_url'],
        'podcasts' => ['instagram_url', 'tiktok_url'],
    ];
    $remaining = [];

    foreach ($columns as $table => $names) {
        foreach ($names as $column) {
            if (Schema::hasColumn($table, $column)) {
                $remaining[] = "{$table}.{$column}";
            }
        }
    }

    return $remaining;
}

// The migrated schema no longer has the legacy columns, so put them back to look like production did before the drop.
beforeEach(function (): void {
    if (! Schema::hasColumn('episodes', 'audio_url')) {
        Schema::table('episodes', function (Blueprint $table): void {
            $table->string('audio_url')->nullable();
            $table->string('audio_path')->nullable();
            $table->string('apple_url')->nullable();
            $table->string('spotify_url')->nullable();
        });
    }

    if (! Schema::hasColumn('podcasts', 'instagram_url')) {
        Schema::table('podcasts', function (Blueprint $table): void {
            $table->string('instagram_url')->nullable();
            $table->string('tiktok_url')->nullable();
        });
    }
});

test('the empty legacy columns are dropped and the migration can run again', function (): void {
    $episode = Episode::factory()->create();
    DB::table('episodes')->where('id', $episode->id)->update(['audio_url' => '', 'apple_url' => null]);
    DB::table('podcasts')->update(['instagram_url' => '', 'tiktok_url' => null]);

    runDropLegacyEpisodeMediaMigration();
    runDropLegacyEpisodeMediaMigration();

    expect(remainingLegacyEpisodeMediaColumns())->toBeEmpty()
        ->and(Episode::query()->whereKey($episode->id)->exists())->toBeTrue();
});

test('an episode value in any legacy column stops the drop with nothing changed', function (string $column): void {
    $episode = Episode::factory()->create();
    $episode->delete();
    DB::table('episodes')->where('id', $episode->id)->update([$column => 'https://example.test/legacy']);

    expect(fn () => runDropLegacyEpisodeMediaMigration())->toThrow(RuntimeException::class)
        ->and(remainingLegacyEpisodeMediaColumns())->toHaveCount(6);
})->with(['audio_url', 'audio_path', 'apple_url', 'spotify_url']);

test('a podcast social link with no matching social profile stops the drop', function (string $column): void {
    primaryPodcast();
    DB::table('podcasts')->update([$column => 'https://example.test/profile']);

    expect(fn () => runDropLegacyEpisodeMediaMigration())->toThrow(RuntimeException::class)
        ->and(remainingLegacyEpisodeMediaColumns())->toHaveCount(6);
})->with(['instagram_url', 'tiktok_url']);

test('a podcast social link already copied to a social profile does not stop the drop', function (): void {
    primaryPodcast();
    DB::table('podcasts')->update(['instagram_url' => 'https://instagram.com/mouse28']);
    DB::table('social_profiles')->insert([
        'platform' => 'instagram',
        'label' => 'Instagram',
        'url' => 'https://instagram.com/mouse28',
        'is_enabled' => true,
        'show_in_footer' => true,
        'show_on_contact' => false,
        'sort_order' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    runDropLegacyEpisodeMediaMigration();

    expect(remainingLegacyEpisodeMediaColumns())->toBeEmpty();
});
