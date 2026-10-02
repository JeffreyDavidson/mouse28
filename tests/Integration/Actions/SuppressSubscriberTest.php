<?php

use App\Actions\SuppressSubscriber;
use App\Enums\SuppressionReason;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(SuppressSubscriber::class);

test('suppressing an active reader unsubscribes them and records why', function (): void {
    $reader = Subscriber::factory()->create(['email' => 'reader@example.com']);
    $reader->verification_token_hash = hash('sha256', 'token');
    $reader->save();

    $found = app(SuppressSubscriber::class)->handle(' Reader@Example.com ', SuppressionReason::Bounced);

    $reader->refresh();

    expect($found)->toBeTrue()
        ->and($reader->unsubscribed_at)->not->toBeNull()
        ->and($reader->suppressed_at)->not->toBeNull()
        ->and($reader->suppression_reason)->toBe(SuppressionReason::Bounced)
        ->and($reader->verification_token_hash)->toBeNull()
        ->and($reader->isActive())->toBeFalse();
});

test('suppressing keeps the original unsubscribe date', function (): void {
    $this->freezeTime();
    $reader = Subscriber::factory()->create(['unsubscribed_at' => now()->subDays(3)->startOfSecond()]);

    app(SuppressSubscriber::class)->handle($reader->email, SuppressionReason::Complained);

    expect($reader->refresh()->unsubscribed_at?->equalTo(now()->subDays(3)->startOfSecond()))->toBeTrue();
});

test('suppressing a pending sign-up stops its confirmation', function (): void {
    $reader = Subscriber::factory()->pending()->create();

    app(SuppressSubscriber::class)->handle($reader->email, SuppressionReason::Bounced);

    expect($reader->refresh()->isSuppressed())->toBeTrue();
});

test('suppressing twice keeps the first reason and date', function (): void {
    $this->freezeTime();
    $reader = Subscriber::factory()->create();
    $action = app(SuppressSubscriber::class);

    $action->handle($reader->email, SuppressionReason::Bounced);
    $this->travel(2)->days();
    $found = $action->handle($reader->email, SuppressionReason::Complained);

    $reader->refresh();

    expect($found)->toBeTrue()
        ->and($reader->suppression_reason)->toBe(SuppressionReason::Bounced)
        ->and($reader->suppressed_at?->isSameDay(now()->subDays(2)))->toBeTrue();
});

test('an unknown address changes nothing', function (): void {
    $reader = Subscriber::factory()->create();

    $found = app(SuppressSubscriber::class)->handle('stranger@example.com', SuppressionReason::Bounced);

    expect($found)->toBeFalse()
        ->and($reader->refresh()->isActive())->toBeTrue();
});
