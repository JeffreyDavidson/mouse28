<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_stories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('location')->nullable();
            $table->string('child_name')->nullable();
            $table->integer('child_age')->nullable();
            $table->string('title');
            $table->longText('story');
            $table->string('favorite_park')->nullable();
            $table->text('favorite_tip')->nullable();
            $table->boolean('photo_consent')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->datetime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_stories');
    }
};
