<?php

use App\Models\SocialProfile;
use App\ViewModels\ContactViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(ContactViewModel::class);

pest()->use(RefreshDatabase::class);

test('contact payload uses the configured address and enables the form when Turnstile is configured', function (): void {
    config()->set([
        'mouse28.contact.email' => 'contact@example.test',
        'services.turnstile.site_key' => 'site-key',
        'services.turnstile.secret_key' => 'secret-key',
    ]);

    $data = app(ContactViewModel::class)->data();

    expect($data['contactEmail'])->toBe('contact@example.test')
        ->and($data['contactFormAvailable'])
        ->toBeTrue()
        ->and($data['socialProfiles'])
        ->toBeEmpty();
});

test('contact payload disables the form when either Turnstile credential is missing', function (string $missing): void {
    config()->set([
        'mouse28.contact.email' => 'contact@example.test',
        'services.turnstile.site_key' => 'site-key',
        'services.turnstile.secret_key' => 'secret-key',
    ]);
    config()->set("services.turnstile.{$missing}");

    $data = app(ContactViewModel::class)->data();

    expect($data['contactEmail'])->toBe('contact@example.test')
        ->and($data['contactFormAvailable'])
        ->toBeFalse();
})->with(['site_key', 'secret_key']);

test('contact payload lists only enabled contact page profiles', function (): void {
    $shown = SocialProfile::factory()
        ->onContactPage()
        ->create();
    SocialProfile::factory()->create();
    SocialProfile::factory()
        ->onContactPage()
        ->create(['is_enabled' => false]);

    expect(app(ContactViewModel::class)->data()['socialProfiles']->modelKeys())->toBe([$shown->id]);
});
