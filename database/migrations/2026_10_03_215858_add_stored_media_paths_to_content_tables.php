<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The path column each table gets, named as in The Laravel Architect.
     *
     * @var array<string, string>
     */
    private const array COLUMNS = [
        'posts' => 'featured_image_path',
        'episodes' => 'featured_image_path',
        'guides' => 'featured_image_path',
        'podcasts' => 'cover_image_path',
    ];

    /**
     * Give posts, episodes and guides a `featured_image_path` and the podcast a
     * `cover_image_path` (The Laravel Architect's names for stored images), and
     * copy the legacy `cover_image` into them. Every step is guarded so the
     * migration can safely run again: each column is only added when missing,
     * and the copy only fills rows whose path is still NULL, including
     * soft-deleted rows. The columns are plain nullable strings, so SQLite adds
     * them in place without rebuilding a table; `cover_image` stays until a
     * later release drops it.
     */
    public function up(): void
    {
        foreach (self::COLUMNS as $tableName => $column) {
            $this->addPathColumn($tableName, $column);
            $this->copyCoverImageIntoPath($tableName, $column);
            $this->assertEveryCoverImageWasCopied($tableName, $column);
        }
    }

    private function addPathColumn(string $tableName, string $column): void
    {
        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column): void {
            $table->string($column)->nullable()->after('cover_image');
        });
    }

    private function copyCoverImageIntoPath(string $tableName, string $column): void
    {
        DB::table($tableName)
            ->whereNull($column)
            ->whereNotNull('cover_image')
            ->update([$column => DB::raw('cover_image')]);
    }

    /**
     * Refuse to finish while any row still holds an image only in `cover_image`,
     * so the legacy column can never be dropped while it holds a path the new
     * column lacks.
     */
    private function assertEveryCoverImageWasCopied(string $tableName, string $column): void
    {
        $uncopied = DB::table($tableName)
            ->where('cover_image', '<>', '')
            ->where(function (Builder $query) use ($column): void {
                $query->whereNull($column)->orWhere($column, '');
            })
            ->count();

        if ($uncopied > 0) {
            throw new RuntimeException("{$uncopied} {$tableName} row(s) still have a cover_image but no {$column} after the backfill.");
        }
    }
};
