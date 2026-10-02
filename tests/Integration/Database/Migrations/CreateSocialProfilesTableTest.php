<?php

use App\Enums\SocialPlatform;
use App\Models\Podcast;
use App\Models\SocialProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

function runSocialProfilesMigration(): void
{
    Schema::dropIfExists('social_profiles');

    $migration = require database_path('migrations/2026_10_02_173351_create_social_profiles_table.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The social profiles migration could not be loaded.');
    }

    $migration->up();
}

test('existing podcast Instagram and TikTok links become social profiles', function (): void {
    Podcast::settings()->update([
        'instagram_url' => 'https://instagram.com/mouse28',
        'tiktok_url' => 'https://tiktok.com/@mouse28',
    ]);

    runSocialProfilesMigration();

    $profiles = SocialProfile::query()->orderBy('sort_order')->get();

    expect($profiles->pluck('platform')->all())->toBe([SocialPlatform::Instagram, SocialPlatform::TikTok])
        ->and($profiles->pluck('url')->all())->toBe(['https://instagram.com/mouse28', 'https://tiktok.com/@mouse28'])
        ->and($profiles->every(fn (SocialProfile $profile): bool => $profile->is_enabled && $profile->show_in_footer))->toBeTrue()
        ->and($profiles->contains(fn (SocialProfile $profile): bool => $profile->show_on_contact))->toBeFalse();
});

test('blank or missing podcast links create no profiles', function (): void {
    Podcast::settings()->update(['instagram_url' => '', 'tiktok_url' => null]);

    runSocialProfilesMigration();

    expect(SocialProfile::query()->exists())->toBeFalse();
});

test('the migration copes with no podcast row at all', function (): void {
    DB::table('podcasts')->delete();

    runSocialProfilesMigration();

    expect(SocialProfile::query()->exists())->toBeFalse();
});
