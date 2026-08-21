<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('guides')) {
            Schema::table('guides', function (Blueprint $table) {
                $table->string('author')->default('both');
                $table->string('cover_image')->nullable();
                $table->string('source_url')->nullable();
                $table->date('last_reviewed_at')->nullable();
                $table->timestamp('published_at')->nullable();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('og_image')->nullable();
            });

            if (Schema::hasColumn('guides', 'park')) {
                Schema::table('guides', function (Blueprint $table) {
                    $table->string('park')->default('general')->change();
                });
            }

            DB::table('guides')
                ->where('is_published', true)
                ->update(['published_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)')]);

            DB::table('guides')->whereIn('category', [
                'das-guide',
                'sensory-tips',
                'quiet-spots',
                'rides',
            ])->update(['category' => 'accessibility']);
            DB::table('guides')->where('category', 'dining')->update(['category' => 'food-reviews']);
            DB::table('guides')->where('category', 'planning')->update(['category' => 'family-planning']);

            return;
        }

        Schema::create('guides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->string('category');
            $table->string('author')->default('both');
            $table->string('cover_image')->nullable();
            $table->string('source_url')->nullable();
            $table->date('last_reviewed_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });
    }
};
