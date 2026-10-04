<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The stored image path column that replaced `cover_image` in each table.
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
     * Drop the legacy `cover_image` columns the stored media migration kept.
     * Every table is checked before any column is dropped, so a row that still
     * holds an image only in `cover_image` stops the migration with nothing
     * changed. Each drop is skipped when the column is already gone, so the
     * migration can safely run again.
     */
    public function up(): void
    {
        foreach (self::COLUMNS as $tableName => $column) {
            $this->assertNoImageOnlyInCoverImage($tableName, $column);
        }

        foreach (array_keys(self::COLUMNS) as $tableName) {
            $this->dropCoverImage($tableName);
        }
    }

    private function assertNoImageOnlyInCoverImage(string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, 'cover_image')) {
            return;
        }

        $uncopied = DB::table($tableName)
            ->where('cover_image', '<>', '')
            ->where(function (Builder $query) use ($column): void {
                $query->whereNull($column)->orWhere($column, '');
            })
            ->count();

        if ($uncopied > 0) {
            throw new RuntimeException("{$uncopied} {$tableName} row(s) still have a cover_image but no {$column}.");
        }
    }

    private function dropCoverImage(string $tableName): void
    {
        if (! Schema::hasColumn($tableName, 'cover_image')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropColumn('cover_image');
        });
    }
};
