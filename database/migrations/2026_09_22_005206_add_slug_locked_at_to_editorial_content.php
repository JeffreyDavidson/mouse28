<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['posts', 'guides', 'episodes'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->timestamp('slug_locked_at')->nullable();
            });

            // Preserve legacy URLs with past publication dates, including unpublished records.
            DB::table($tableName)
                ->where('published_at', '<=', now())
                ->update(['slug_locked_at' => DB::raw('published_at')]);
        }
    }
};
