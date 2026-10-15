<?php

use App\Models\ContactInquiry;

test('creator-kit uses Mouse28 contact inquiries and keeps them until an admin deletes them', function (): void {
    expect(config('creator-kit.contact.model'))->toBe(ContactInquiry::class)
        ->and(config('creator-kit.contact.retention_days'))
        ->toBeNull();
});

test('a new inquiry is emailed to each admin address, ignoring spaces and empty entries', function (): void {
    // Arrange
    $previous = $_SERVER['MAIL_ADMIN_ADDRESS'] ?? null;
    $_SERVER['MAIL_ADMIN_ADDRESS'] = ' jeffrey@example.test, cassie@example.test ,, ';

    // Act
    try {
        /** @var array{contact: array{notify: list<string>}} $configuration */
        $configuration = require config_path('creator-kit.php');
    } finally {
        if ($previous === null) {
            unset($_SERVER['MAIL_ADMIN_ADDRESS']);
        } else {
            $_SERVER['MAIL_ADMIN_ADDRESS'] = $previous;
        }
    }

    // Assert
    expect($configuration['contact']['notify'])->toBe(['jeffrey@example.test', 'cassie@example.test']);
});
