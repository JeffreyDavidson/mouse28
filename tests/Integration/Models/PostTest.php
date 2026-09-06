<?php

use App\Enums\ContentAuthor;
use App\Enums\PostCategory;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('editorial review dates determine the review queue', function (): void {
    config()->set('mouse28.post_review_interval_days', 180);

    $currentPost = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => today()->subDays(30),
    ]);
    $stalePost = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => today()->subDays(181),
    ]);

    expect($currentPost->isReviewDue())->toBeFalse()
        ->and($stalePost->isReviewDue())->toBeTrue()
        ->and(Post::reviewDue()->pluck('id')->all())->toBe([$stalePost->id]);
});

test('editorial scopes separate the content work queue', function (): void {
    $draft = Post::factory()->draft()->create();
    $scheduled = Post::factory()->scheduled()->create();
    $published = Post::factory()->create([
        'cover_image' => 'posts/complete.jpg',
        'meta_title' => 'Complete title',
        'meta_description' => 'Complete description',
    ]);
    $needsAttention = Post::factory()->create(['cover_image' => null]);

    expect(Post::drafts()->pluck('id'))->toContain($draft->id)
        ->and(Post::scheduled()->pluck('id'))->toContain($scheduled->id)
        ->and(Post::published()->pluck('id'))->toContain($published->id, $needsAttention->id)
        ->and(Post::needsAttention()->pluck('id'))->toContain($draft->id, $scheduled->id, $needsAttention->id)
        ->and(Post::needsAttention()->pluck('id'))->not->toContain($published->id);
});

test('content enums round trip through their existing database strings', function (): void {
    $record = Post::factory()->create([
        'author' => 'cassie',
        'category' => 'park-accessibility',
    ]);

    $record->refresh();

    expect($record->author)->toBe(ContentAuthor::Cassie)
        ->and($record->category)->toBe(PostCategory::ParkAccessibility)
        ->and($record->author_name)->toBe('Cassie Davidson');

    $record->update(['author' => ContentAuthor::Both]);
    $record->refresh();

    expect($record->getRawOriginal('author'))->toBe('both')
        ->and($record->getRawOriginal('category'))->toBe('park-accessibility')
        ->and($record->toArray()['author'])->toBe('both')
        ->and($record->toArray()['category'])->toBe('park-accessibility')
        ->and($record->author_name)->toBe('Jeffrey & Cassie');
});
