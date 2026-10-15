<?php

use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;

test('creator-kit uses Mouse28 newsletter models', function (): void {
    expect(config('creator-kit.newsletter.models'))->toBe([
        'subscriber' => Subscriber::class,
        'issue' => NewsletterIssue::class,
        'delivery' => NewsletterDelivery::class,
    ]);
});

test('a test send goes to the admin addresses, one per comma-separated entry', function (): void {
    // Both settings read MAIL_ADMIN_ADDRESS when the configuration loads, so they cannot drift apart.
    $adminAddresses = array_values(array_filter(array_map(trim(...), explode(',', config()->string('mail.admin_address')))));

    expect(config('creator-kit.newsletter.test_recipients'))->toBe($adminAddresses)
        ->not->toBeEmpty();
});

test('an unusable confirmation link goes back to the home page sign-up form', function (): void {
    expect(config('creator-kit.newsletter.signup_form'))->toBe([
        'route' => 'home',
        'fragment' => 'newsletter',
        'error_bag' => 'newsletter',
    ]);
});
