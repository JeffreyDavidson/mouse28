<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

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

    /**
     * Copy each post, episode and guide's legacy `meta_title` and `meta_description`
     * into its SEO row (`seo.title` and `seo.description`), soft-deleted rows
     * included, creating the row when it is missing. A value already saved in the
     * SEO row is kept, so the migration can safely run again and never overwrites an
     * edit made through the admin. `og_image` is not copied: the SEO admin field has no
     * image upload and no row holds one. The legacy columns stay until a later release
     * drops them.
     */
    public function up(): void
    {
        foreach (self::MODELS as $table => $modelType) {
            $this->copyMetaIntoSeo($table, $modelType);
            $this->assertEveryMetaValueWasCopied($table, $modelType);
        }
    }

    private function copyMetaIntoSeo(string $table, string $modelType): void
    {
        $now = Date::now();

        foreach ($this->rowsWithMeta($table)->get(['id', 'meta_title', 'meta_description']) as $row) {
            $seo = DB::table('seo')
                ->where('model_type', $modelType)
                ->where('model_id', $row->id)
                ->first();

            if ($seo === null) {
                DB::table('seo')->insert([
                    'model_type' => $modelType,
                    'model_id' => $row->id,
                    'title' => filled($row->meta_title) ? $row->meta_title : null,
                    'description' => filled($row->meta_description) ? $row->meta_description : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                continue;
            }

            DB::table('seo')->where('id', $seo->id)->update([
                'title' => blank($seo->title) && filled($row->meta_title) ? $row->meta_title : $seo->title,
                'description' => blank($seo->description) && filled($row->meta_description) ? $row->meta_description : $seo->description,
                'updated_at' => $now,
            ]);
        }
    }

    /** @return Builder rows with a legacy title or description */
    private function rowsWithMeta(string $table): Builder
    {
        return DB::table($table)->where(function (Builder $query): void {
            $query->where('meta_title', '<>', '')->orWhere('meta_description', '<>', '');
        });
    }

    /**
     * Refuse to finish while any legacy title or description has no value in its SEO
     * row, so the columns can never be dropped while they hold text the SEO row lacks.
     */
    private function assertEveryMetaValueWasCopied(string $table, string $modelType): void
    {
        $missing = 0;

        foreach (['meta_title' => 'title', 'meta_description' => 'description'] as $legacyColumn => $seoColumn) {
            $missing += DB::table($table)
                ->where($legacyColumn, '<>', '')
                ->whereNotExists(function (Builder $query) use ($table, $modelType, $seoColumn): void {
                    $query->selectRaw('1')
                        ->from('seo')
                        ->where('seo.model_type', $modelType)
                        ->whereColumn('seo.model_id', "{$table}.id")
                        ->whereNotNull("seo.{$seoColumn}")
                        ->where("seo.{$seoColumn}", '<>', '');
                })
                ->count();
        }

        if ($missing > 0) {
            throw new RuntimeException("{$missing} {$table} meta value(s) still have no matching SEO value after the backfill.");
        }
    }
};
