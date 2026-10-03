<?php

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\Subscriber;
use App\Models\User;
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
        ->and(NewsletterIssue::query()->where('status', PublishStatus::Draft)->count())->toBe(1)
        ->and(NewsletterIssue::scheduled()->count())->toBe(1)
        ->and(NewsletterIssue::query()->whereNotNull('sent_at')->count())->toBe(0);
});

test('running the sample seeder twice does not duplicate issues', function (): void {
    $this->seed(SampleContentSeeder::class);
    $this->seed(SampleContentSeeder::class);

    expect(NewsletterIssue::query()->count())->toBe(3);
});

test('the sample seeder files each sample post under a category', function (): void {
    $this->seed(SampleContentSeeder::class);

    expect(Post::query()->with('category')->orderBy('slug')->get()->mapWithKeys(fn (Post $post): array => [$post->slug => $post->category?->slug])->all())->toBe([
        'sample-post-draft-outline' => 'disney-tips',
        'sample-post-planning-notes' => 'park-accessibility',
        'sample-post-scheduled-update' => 'family-life',
    ]);
});

test('the sample seeder adds its categories when they are missing', function (): void {
    Category::query()->delete();

    $this->seed(SampleContentSeeder::class);

    expect(Post::query()->whereNull('category_id')->count())->toBe(0)
        ->and(Category::query()->count())->toBe(11);
});

test('the sample seeder credits every sample post and guide to both authors in order', function (): void {
    $this->seed(SampleContentSeeder::class);
    $this->seed(SampleContentSeeder::class);

    $bylines = [
        ...Post::query()->with('authors')->get()->map(fn (Post $post): array => $post->authors->pluck('name')->all()),
        ...Guide::query()->with('authors')->get()->map(fn (Guide $guide): array => $guide->authors->pluck('name')->all()),
    ];

    expect($bylines)->toHaveCount(6)
        ->each->toBe(['Jeffrey Davidson', 'Cassie Davidson'])
        ->and(User::authors()->count())->toBe(2);
});
