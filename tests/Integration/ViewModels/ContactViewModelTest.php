<?php

use App\ViewModels\ContactViewModel;

covers(ContactViewModel::class);

test('contact payload uses the configured address and enables the form when Turnstile is configured', function (): void {
    config()->set([
        'mouse28.contact.email' => 'contact@example.test',
        'services.turnstile.site_key' => 'site-key',
        'services.turnstile.secret_key' => 'secret-key',
    ]);

    expect(app(ContactViewModel::class)->data())->toBe([
        'contactEmail' => 'contact@example.test',
        'contactFormAvailable' => true,
    ]);
});

test('contact payload disables the form when either Turnstile credential is missing', function (string $missing): void {
    config()->set([
        'mouse28.contact.email' => 'contact@example.test',
        'services.turnstile.site_key' => 'site-key',
        'services.turnstile.secret_key' => 'secret-key',
    ]);
    config()->set("services.turnstile.{$missing}");

    expect(app(ContactViewModel::class)->data())->toBe([
        'contactEmail' => 'contact@example.test',
        'contactFormAvailable' => false,
    ]);
})->with(['site_key', 'secret_key']);
