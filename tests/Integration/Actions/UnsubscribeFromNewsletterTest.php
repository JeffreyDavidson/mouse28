<?php

use App\Actions\UnsubscribeFromNewsletter;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(UnsubscribeFromNewsletter::class);

test('unsubscribing records the date and clears any pending token', function (): void {
    $reader = Subscriber::factory()->create();
    $reader->verification_token_hash = hash('sha256', 'confirmation-token');
    $reader->save();

    app(UnsubscribeFromNewsletter::class)->handle($reader);

    expect($reader->refresh()
        ->unsubscribed_at)->not->toBeNull()
        ->and($reader->verification_token_hash)
        ->toBeNull()
        ->and($reader->isActive())
        ->toBeFalse();
});
