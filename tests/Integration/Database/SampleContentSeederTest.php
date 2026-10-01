<?php

use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use Database\Seeders\SampleContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(SampleContentSeeder::class);

pest()->use(RefreshDatabase::class);

test('the sample seeder creates one active, one pending, and one unsubscribed reader', function (): void {
    $this->seed(SampleContentSeeder::class);

    expect(Subscriber::query()->count())->toBe(3)
        ->and(Subscriber::query()->where('email', 'sample-active-reader@example.test')->sole()->isActive())->toBeTrue()
        ->and(Subscriber::query()->where('email', 'sample-pending-reader@example.test')->sole()->verified_at)->toBeNull()
        ->and(Subscriber::query()->where('email', 'sample-former-reader@example.test')->sole()->unsubscribed_at)->not->toBeNull();
});

test('running the sample seeder twice does not duplicate readers', function (): void {
    $this->seed(SampleContentSeeder::class);
    $this->seed(SampleContentSeeder::class);

    expect(Subscriber::query()->count())->toBe(3);
});

test('the sample seeder creates one live, one draft and one scheduled issue', function (): void {
    $this->seed(SampleContentSeeder::class);

    expect(NewsletterIssue::query()->count())->toBe(3)
        ->and(NewsletterIssue::published()->count())->toBe(1)
        ->and(NewsletterIssue::drafts()->count())->toBe(1)
        ->and(NewsletterIssue::scheduled()->count())->toBe(1)
        ->and(NewsletterIssue::query()->whereNotNull('sent_at')->count())->toBe(0);
});

test('running the sample seeder twice does not duplicate issues', function (): void {
    $this->seed(SampleContentSeeder::class);
    $this->seed(SampleContentSeeder::class);

    expect(NewsletterIssue::query()->count())->toBe(3);
});
