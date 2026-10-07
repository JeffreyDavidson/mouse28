<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Give `podcasts` the Laravel Architect's multi-show columns: a slug, a long
     * description, a color, an active flag, a sort order and soft deletes. The existing
     * show is slugged "mouse28" (or its name) and stays active. Plain added columns
     * alter the table in place on SQLite, and every step is safe to run again.
     */
    public function up(): void
    {
        Schema::table('podcasts', function (Blueprint $table): void {
            if (! Schema::hasColumn('podcasts', 'slug')) {
                // Nullable so the column can be added to existing rows; every row is slugged below.
                $table->string('slug')->nullable()->after('name');
            }

            if (! Schema::hasColumn('podcasts', 'long_description')) {
                $table->text('long_description')->nullable()->after('description');
            }

            if (! Schema::hasColumn('podcasts', 'color')) {
                $table->string('color', 7)->nullable()->after('cover_image_path');
            }

            if (! Schema::hasColumn('podcasts', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            if (! Schema::hasColumn('podcasts', 'sort_order')) {
                $table->unsignedInteger('sort_order')->default(0);
            }

            if (! Schema::hasColumn('podcasts', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        $this->backfillSlugs();

        if (! Schema::hasIndex('podcasts', ['slug'], 'unique')) {
            Schema::table('podcasts', function (Blueprint $table): void {
                $table->unique('slug');
            });
        }
    }

    /** The one existing show becomes "mouse28"; any other row is slugged from its name. */
    private function backfillSlugs(): void
    {
        $firstId = (int) DB::table('podcasts')->min('id');

        foreach (DB::table('podcasts')->whereNull('slug')->orderBy('id')->get(['id', 'name']) as $podcast) {
            $base = (int) $podcast->id === $firstId ? 'mouse28' : Str::slug((string) $podcast->name);
            $slug = $base;
            $suffix = 2;

            while (DB::table('podcasts')->where('slug', $slug)->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            DB::table('podcasts')->where('id', $podcast->id)->update(['slug' => $slug]);
        }
    }
};
