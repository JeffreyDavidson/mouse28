<?php

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
