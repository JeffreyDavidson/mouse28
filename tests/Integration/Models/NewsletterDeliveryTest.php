<?php

use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(NewsletterDelivery::class);

pest()->use(RefreshDatabase::class);

test('a delivery belongs to one issue and one reader', function (): void {
    $issue = NewsletterIssue::factory()->create();
    $reader = Subscriber::factory()->create();

    $delivery = NewsletterDelivery::factory()->for($issue)->for($reader)->create();

    expect($delivery->newsletterIssue?->is($issue))->toBeTrue()
        ->and($delivery->subscriber?->is($reader))->toBeTrue()
        ->and($delivery->sent_at)->toBeNull();
});

test('a reader can only be queued once for an issue', function (): void {
    $issue = NewsletterIssue::factory()->create();
    $reader = Subscriber::factory()->create();
    NewsletterDelivery::factory()->for($issue)->for($reader)->create();

    expect(fn () => NewsletterDelivery::factory()->for($issue)->for($reader)->create())
        ->toThrow(UniqueConstraintViolationException::class);
});

test('deliveries do not outlive their reader', function (): void {
    $reader = Subscriber::factory()->create();
    NewsletterDelivery::factory()->for($reader)->create();

    $reader->delete();

    expect(NewsletterDelivery::query()->count())->toBe(0);
});
