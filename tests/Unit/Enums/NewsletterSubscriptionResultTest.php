<?php

use App\Enums\NewsletterSubscriptionResult;

test('newsletter subscription results retain their HTTP status mapping', function (): void {
    $statusCodes = [];

    foreach (NewsletterSubscriptionResult::cases() as $case) {
        $statusCodes[$case->name] = $case->statusCode();
    }

    expect($statusCodes)->toBe([
        'Subscribed' => 200,
        'ConfigurationMissing' => 503,
        'ProviderRejected' => 422,
        'ConnectionFailed' => 500,
    ]);
});
