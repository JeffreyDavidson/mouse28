<?php

use App\Enums\SocialPlatform;
use App\Models\SocialProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(SocialProfile::class);

pest()->use(RefreshDatabase::class);

test('a social profile casts its platform and flags', function (): void {
    $profile = SocialProfile::factory()->create([
        'platform' => SocialPlatform::TikTok,
        'is_enabled' => 1,
        'show_in_footer' => 0,
        'sort_order' => '5',
    ])->refresh();

    expect($profile->platform)->toBe(SocialPlatform::TikTok)
        ->and($profile->is_enabled)
        ->toBeTrue()
        ->and($profile->show_in_footer)
        ->toBeFalse()
        ->and($profile->sort_order)
        ->toBe(5);
});

test('profiles for the footer are enabled, flagged for the footer and ordered', function (): void {
    $second = SocialProfile::factory()->create(['sort_order' => 20]);
    $first = SocialProfile::factory()->create(['sort_order' => 10]);
    SocialProfile::factory()->create(['is_enabled' => false]);
    SocialProfile::factory()->create(['show_in_footer' => false]);

    expect(SocialProfile::query()
        ->forFooter()
        ->pluck('id')
        ->all())->toBe([$first->id, $second->id]);
});

test('profiles for the contact page are enabled, flagged for the page and ordered by id within a sort order', function (): void {
    $first = SocialProfile::factory()
        ->onContactPage()
        ->create(['sort_order' => 10]);
    $second = SocialProfile::factory()
        ->onContactPage()
        ->create(['sort_order' => 10]);
    SocialProfile::factory()
        ->onContactPage()
        ->create(['is_enabled' => false]);
    SocialProfile::factory()->create();

    expect(SocialProfile::query()
        ->forContactPage()
        ->pluck('id')
        ->all())->toBe([$first->id, $second->id]);
});
