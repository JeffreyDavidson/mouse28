<?php

use App\Enums\SocialPlatform;
use App\Filament\Resources\SocialProfiles\Pages\CreateSocialProfile;
use App\Filament\Resources\SocialProfiles\Pages\EditSocialProfile;
use App\Filament\Resources\SocialProfiles\Pages\ListSocialProfiles;
use App\Filament\Resources\SocialProfiles\SocialProfileResource;
use App\Models\SocialProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('administrators see social profiles and others are forbidden', function (): void {
    $profile = SocialProfile::factory()->create(['platform' => SocialPlatform::Instagram, 'label' => '@mouse28']);

    actingAs(User::factory()->create());
    get(SocialProfileResource::getUrl())->assertForbidden();

    actingAs(User::factory()->admin()->create());
    get(SocialProfileResource::getUrl())
        ->assertOk()
        ->assertSee(['Instagram', '@mouse28']);

    livewire(ListSocialProfiles::class)->assertCanSeeTableRecords([$profile]);
});

test('an administrator can add a profile with sensible defaults', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(CreateSocialProfile::class)
        ->fillForm([
            'platform' => SocialPlatform::Facebook->value,
            'url' => 'https://facebook.com/mouse28',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $profile = SocialProfile::query()->sole();

    expect($profile->platform)->toBe(SocialPlatform::Facebook)
        ->and($profile->url)->toBe('https://facebook.com/mouse28')
        ->and($profile->is_enabled)->toBeTrue()
        ->and($profile->show_in_footer)->toBeTrue()
        ->and($profile->show_on_contact)->toBeFalse();
});

test('profile data is validated', function (array $data, array $errors): void {
    actingAs(User::factory()->admin()->create());

    livewire(CreateSocialProfile::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors);

    expect(SocialProfile::query()->exists())->toBeFalse();
})->with([
    'no platform' => [['url' => 'https://example.com/a'], ['platform' => 'required']],
    'no url' => [['platform' => 'instagram', 'url' => ''], ['url' => 'required']],
    'insecure url' => [['platform' => 'instagram', 'url' => 'http://example.com/a'], ['url']],
    'not a url' => [['platform' => 'instagram', 'url' => 'not a url'], ['url']],
    'url too long' => [['platform' => 'instagram', 'url' => 'https://example.com/'.str_repeat('a', 2040)], ['url' => 'max']],
    'other without a label' => [['platform' => 'other', 'url' => 'https://example.com/a', 'label' => ''], ['label' => 'required_if']],
]);

test('an administrator can edit and delete a profile', function (): void {
    actingAs(User::factory()->admin()->create());
    $profile = SocialProfile::factory()->create();

    livewire(EditSocialProfile::class, ['record' => $profile->getRouteKey()])
        ->fillForm(['url' => 'https://tiktok.com/@mouse28', 'show_on_contact' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($profile->refresh()->url)->toBe('https://tiktok.com/@mouse28')
        ->and($profile->show_on_contact)->toBeTrue();

    livewire(EditSocialProfile::class, ['record' => $profile->getRouteKey()])
        ->callAction('delete');

    expect(SocialProfile::query()->exists())->toBeFalse();
});
