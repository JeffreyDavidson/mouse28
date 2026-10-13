<?php

use App\Enums\AssetReviewStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_reviews', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('version');
            $table->foreignId('source_post_id')->nullable()->constrained('posts')->nullOnDelete();
            $table->json('files');
            $table->string('status')->default(AssetReviewStatus::Pending->value)->index();
            $table->unsignedInteger('revision')->default(1);
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }
};
