<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Podcast metadata (single row — the show itself)
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Mouse28');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('apple_url')->nullable();
            $table->string('spotify_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('rss_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('podcasts');
    }
};
