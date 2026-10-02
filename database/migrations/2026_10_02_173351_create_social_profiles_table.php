<?php

use App\Enums\SocialPlatform;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_profiles', function (Blueprint $table): void {
            $table->id();
            $table->string('platform', 32);
            $table->string('label')->nullable();
            $table->string('url', 2048);
            $table->boolean('is_enabled')->default(true);
            $table->boolean('show_in_footer')->default(true);
            $table->boolean('show_on_contact')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $podcast = DB::table('podcasts')->first();
        $now = now();

        foreach ([10 => [SocialPlatform::Instagram, 'instagram_url'], 20 => [SocialPlatform::TikTok, 'tiktok_url']] as $sortOrder => [$platform, $column]) {
            $url = $podcast?->{$column};

            if (! is_string($url) || $url === '') {
                continue;
            }

            DB::table('social_profiles')->insert([
                'platform' => $platform->value,
                'url' => $url,
                'sort_order' => $sortOrder,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
