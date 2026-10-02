<?php

use App\Data\ContactMessageData;
use App\Enums\ContactType;

test('contact message data retains typed fields through serialization', function (): void {
    $data = new ContactMessageData(
        name: 'Jane Doe',
        email: 'jane@example.com',
        type: ContactType::Accessibility,
        message: 'A park accessibility question.',
    );

    $restored = unserialize(serialize($data));

    if (! $restored instanceof ContactMessageData) {
        throw new UnexpectedValueException('Expected contact data after serialization.');
    }

    expect($restored)->toEqual($data)
        ->and($restored->type)->toBe(ContactType::Accessibility);
});
