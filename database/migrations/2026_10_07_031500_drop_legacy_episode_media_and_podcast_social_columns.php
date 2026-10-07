<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Episode columns retired with the self-hosted player and per-episode platform links. */
    private const array EPISODE_COLUMNS = ['audio_url', 'audio_path', 'apple_url', 'spotify_url'];

    /** Podcast columns whose links moved to `social_profiles`. */
    private const array PODCAST_COLUMNS = ['instagram_url', 'tiktok_url'];

    /**
     * Drop the legacy episode media columns and the podcast Instagram and TikTok
     * columns. Both tables are checked before any column is dropped, so data that
     * exists nowhere else stops the migration with nothing changed: any episode value
     * (trashed episodes included), since nothing replaced those fields, or a podcast
     * social link that no social profile holds. Each drop is skipped when the column is
     * already gone, so the migration can safely run again. No files are deleted.
     */
    public function up(): void
    {
        $this->assertNoEpisodeMediaWouldBeLost();
        $this->assertNoSocialLinkWouldBeLost();

        $this->dropExistingColumns('episodes', self::EPISODE_COLUMNS);
        $this->dropExistingColumns('podcasts', self::PODCAST_COLUMNS);
    }

    private function assertNoEpisodeMediaWouldBeLost(): void
    {
        $values = 0;

        foreach ($this->existingColumns('episodes', self::EPISODE_COLUMNS) as $column) {
            $values += DB::table('episodes')->where($column, '<>', '')->count();
        }

        if ($values > 0) {
            throw new RuntimeException("{$values} legacy episode media value(s) are not stored anywhere else, so the legacy columns were not dropped.");
        }
    }

    private function assertNoSocialLinkWouldBeLost(): void
    {
        $missing = 0;

        foreach ($this->existingColumns('podcasts', self::PODCAST_COLUMNS) as $column) {
            $missing += DB::table('podcasts')
                ->where($column, '<>', '')
                ->whereNotExists(function (Builder $query) use ($column): void {
                    $query->selectRaw('1')
                        ->from('social_profiles')
                        ->whereColumn('social_profiles.url', "podcasts.{$column}");
                })
                ->count();
        }

        if ($missing > 0) {
            throw new RuntimeException("{$missing} podcast social link(s) have no matching social profile, so the legacy columns were not dropped.");
        }
    }

    /**
     * @param  list<string>  $columns
     * @return list<string>
     */
    private function existingColumns(string $table, array $columns): array
    {
        return array_values(array_filter($columns, fn (string $column): bool => Schema::hasColumn($table, $column)));
    }

    /** @param list<string> $columns */
    private function dropExistingColumns(string $table, array $columns): void
    {
        $existing = $this->existingColumns($table, $columns);

        if ($existing === []) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($existing): void {
            $blueprint->dropColumn($existing);
        });
    }
};
