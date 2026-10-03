<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Credit posts and guides to one or more authors (users), in byline order. A
     * credit is removed when either side is permanently deleted. These are new
     * tables, so the existing posts, guides and users tables, with their CHECK
     * constraints, are left untouched. The Laravel Architect keeps a single
     * `posts.user_id`; this many-to-many shape is mouse28-only.
     */
    public function up(): void
    {
        foreach (['post' => 'posts', 'guide' => 'guides'] as $type => $contentTable) {
            if (Schema::hasTable("{$type}_user")) {
                continue;
            }

            Schema::create("{$type}_user", function (Blueprint $table) use ($type, $contentTable): void {
                $table->foreignId("{$type}_id")
                    ->constrained($contentTable)
                    ->cascadeOnDelete();
                $table->foreignId('user_id')
                    ->constrained()
                    ->cascadeOnDelete();
                $table->unsignedSmallInteger('position')->default(0);
                $table->primary(["{$type}_id", 'user_id']);
            });
        }
    }
};
