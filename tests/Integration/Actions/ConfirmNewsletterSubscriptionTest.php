<?php

use App\Actions\ConfirmNewsletterSubscription;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(ConfirmNewsletterSubscription::class);

test('confirming a sign-up verifies the reader and clears pending state', function (): void {
    $reader = Subscriber::factory()
        ->unsubscribed()
        ->create();
    $reader->verification_token_hash = hash('sha256', 'confirmation-token');
    $reader->save();

    app(ConfirmNewsletterSubscription::class)->handle($reader);

    expect($reader->refresh()
        ->verified_at)->not->toBeNull()
        ->and($reader->unsubscribed_at)
        ->toBeNull()
        ->and($reader->verification_token_hash)
        ->toBeNull()
        ->and($reader->isActive())
        ->toBeTrue();
});
