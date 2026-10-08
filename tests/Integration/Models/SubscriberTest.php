<?php

use App\Enums\SuppressionReason;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

covers(Subscriber::class);

pest()->use(RefreshDatabase::class);

test('only confirmed sign-ups that have not unsubscribed are active', function (): void {
    $active = Subscriber::factory()->create();
    $pending = Subscriber::factory()
        ->pending()
        ->create();
    $unsubscribed = Subscriber::factory()
        ->unsubscribed()
        ->create();

    expect(Subscriber::query()
        ->active()
        ->pluck('id')
        ->all())->toBe([$active->id])
        ->and($active->isActive())
        ->toBeTrue()
        ->and($pending->isActive())
        ->toBeFalse()
        ->and($unsubscribed->isActive())
        ->toBeFalse();
});

test('the verification token hash is not mass assignable', function (): void {
    $subscriber = new Subscriber;

    $subscriber->fill([
        'email' => 'reader@example.test',
        'verification_token_hash' => hash('sha256', 'secret-token'),
    ]);

    expect($subscriber->email)->toBe('reader@example.test')
        ->and($subscriber->getAttributes())
        ->not->toHaveKey('verification_token_hash');
});

test('the verification token hash is hidden from serialization', function (): void {
    $subscriber = new Subscriber;
    $subscriber->email = 'reader@example.test';
    $subscriber->verification_token_hash = hash('sha256', 'secret-token');

    expect($subscriber->toArray())
        ->toHaveKey('email', 'reader@example.test')
        ->not->toHaveKey('verification_token_hash');
});

test('stale readers are prunable', function (int $subscribedDaysAgo, ?int $verifiedDaysAgo, ?int $unsubscribedDaysAgo): void {
    $this->freezeTime();
    $subscriber = Subscriber::factory()->create([
        'subscribed_at' => Date::now()->subDays($subscribedDaysAgo),
        'verified_at' => $verifiedDaysAgo === null ? null : Date::now()->subDays($verifiedDaysAgo),
        'unsubscribed_at' => $unsubscribedDaysAgo === null ? null : Date::now()->subDays($unsubscribedDaysAgo),
    ]);

    expect(new Subscriber()->prunable()
        ->pluck('id')
        ->all())->toBe([$subscriber->id]);
})->with([
    'unconfirmed past the grace period' => [8, null, null],
    'unsubscribed past the grace period' => [365, 365, 31],
]);

test('current readers are kept when pruning', function (int $subscribedDaysAgo, ?int $verifiedDaysAgo, ?int $unsubscribedDaysAgo): void {
    $this->freezeTime();
    Subscriber::factory()->create([
        'subscribed_at' => Date::now()->subDays($subscribedDaysAgo),
        'verified_at' => $verifiedDaysAgo === null ? null : Date::now()->subDays($verifiedDaysAgo),
        'unsubscribed_at' => $unsubscribedDaysAgo === null ? null : Date::now()->subDays($unsubscribedDaysAgo),
    ]);

    expect(new Subscriber()->prunable()
        ->exists())->toBeFalse();
})->with([
    'unconfirmed within the grace period' => [6, null, null],
    'unsubscribed within the grace period' => [365, 365, 29],
    'active' => [365, 365, null],
]);

test('suppressed readers are never active or pruned', function (): void {
    $this->freezeTime();
    $suppressed = Subscriber::factory()
        ->suppressed(SuppressionReason::Complained)
        ->create([
            'subscribed_at' => Date::now()->subYear(),
            'unsubscribed_at' => Date::now()->subYear(),
            'suppressed_at' => Date::now()->subYear(),
        ]);
    $flagged = Subscriber::factory()->create(['suppressed_at' => Date::now()]);

    expect(Subscriber::query()
        ->active()
        ->exists())->toBeFalse()
        ->and($suppressed->isSuppressed())
        ->toBeTrue()
        ->and($flagged->isActive())
        ->toBeFalse()
        ->and($suppressed->suppression_reason)
        ->toBe(SuppressionReason::Complained)
        ->and(new Subscriber()->prunable()
            ->exists())
        ->toBeFalse();
});
