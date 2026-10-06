<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The model class stored in `seo.model_type` for each table, written out here so
     * this migration keeps working if a model is renamed or removed.
     *
     * @var array<string, class-string>
     */
    private const array MODELS = [
        'posts' => 'App\Models\Post',
        'episodes' => 'App\Models\Episode',
        'guides' => 'App\Models\Guide',
    ];

    /** @var array<string, string> legacy column => the SEO column that replaced it */
    private const array COPIED_COLUMNS = [
        'meta_title' => 'title',
        'meta_description' => 'description',
    ];

    /**
     * Drop the legacy `meta_title`, `meta_description` and `og_image` columns that the
     * SEO package's rows replaced. Every table is checked before any column is dropped,
     * so a value that exists only in a legacy column stops the migration with nothing
     * changed: a title or description with no value in its SEO row, or any `og_image`
     * (the custom share image was retired and never copied). Each drop is skipped when
     * the column is already gone, so the migration can safely run again.
     */
    public function up(): void
    {
        foreach (self::MODELS as $table => $modelType) {
            $this->assertNoMetaValueIsOnlyInTheLegacyColumn($table, $modelType);
            $this->assertNoShareImageWouldBeLost($table);
        }

        foreach (array_keys(self::MODELS) as $table) {
            $this->dropLegacyColumns($table);
        }
    }

    private function assertNoMetaValueIsOnlyInTheLegacyColumn(string $table, string $modelType): void
    {
        $missing = 0;

        foreach (self::COPIED_COLUMNS as $legacyColumn => $seoColumn) {
            if (! Schema::hasColumn($table, $legacyColumn)) {
                continue;
            }

            $missing += DB::table($table)
                ->where($legacyColumn, '<>', '')
                ->whereNotExists(function (Builder $query) use ($table, $modelType, $seoColumn): void {
                    $query->selectRaw('1')
                        ->from('seo')
                        ->where('seo.model_type', $modelType)
                        ->whereColumn('seo.model_id', "{$table}.id")
                        ->where("seo.{$seoColumn}", '<>', '');
                })
                ->count();
        }

        if ($missing > 0) {
            throw new RuntimeException("{$missing} {$table} meta value(s) have no matching SEO value, so the legacy columns were not dropped.");
        }
    }

    private function assertNoShareImageWouldBeLost(string $table): void
    {
        if (! Schema::hasColumn($table, 'og_image')) {
            return;
        }

        $images = DB::table($table)->where('og_image', '<>', '')->count();

        if ($images > 0) {
            throw new RuntimeException("{$images} {$table} row(s) still have an og_image, which is not stored anywhere else, so the legacy columns were not dropped.");
        }
    }

    private function dropLegacyColumns(string $table): void
    {
        $columns = array_values(array_filter(
            ['meta_title', 'meta_description', 'og_image'],
            fn (string $column): bool => Schema::hasColumn($table, $column),
        ));

        if ($columns === []) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns): void {
            $blueprint->dropColumn($columns);
        });
    }
};
