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

test('a test send goes to each admin address, ignoring spaces and empty entries', function (): void {
    // Arrange
    $previous = $_SERVER['MAIL_ADMIN_ADDRESS'] ?? null;
    $_SERVER['MAIL_ADMIN_ADDRESS'] = ' jeffrey@example.test, cassie@example.test ,, ';

    // Act
    try {
        /** @var array{newsletter: array{test_recipients: list<string>}} $configuration */
        $configuration = require config_path('creator-kit.php');
    } finally {
        if ($previous === null) {
            unset($_SERVER['MAIL_ADMIN_ADDRESS']);
        } else {
            $_SERVER['MAIL_ADMIN_ADDRESS'] = $previous;
        }
    }

    // Assert
    expect($configuration['newsletter']['test_recipients'])->toBe(['jeffrey@example.test', 'cassie@example.test']);
});

test('an unusable confirmation link goes back to the home page sign-up form', function (): void {
    expect(config('creator-kit.newsletter.signup_form'))->toBe([
        'route' => 'home',
        'fragment' => 'newsletter',
        'error_bag' => 'newsletter',
    ]);
});
