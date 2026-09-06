<?php

use App\Enums\PublicationStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\EditorialReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('publication status distinguishes drafts schedules and missing dates', function (): void {
    $draft = Post::factory()->draft()->create();
    $missingDate = Post::factory()->create(['published_at' => null]);
    $scheduled = Post::factory()->scheduled()->create();
    $published = Post::factory()->create();

    expect(EditorialReadiness::status($draft))->toBe(PublicationStatus::Draft)
        ->and(EditorialReadiness::status($missingDate))->toBe(PublicationStatus::NeedsPublishDate)
        ->and(EditorialReadiness::status($scheduled))->toBe(PublicationStatus::Scheduled)
        ->and(EditorialReadiness::status($published))->toBe(PublicationStatus::Published);
});

test('readiness reports actionable issues for each content type', function (): void {
    $post = Post::factory()->create([
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);
    $guide = Guide::factory()->create([
        'source_url' => null,
        'last_reviewed_at' => null,
    ]);
    $episode = Episode::factory()->create([
        'audio_url' => null,
        'transcript' => null,
    ]);

    expect(EditorialReadiness::label($post))->toBe('3 missing')
        ->and(EditorialReadiness::issues($post))->toContain('Add a cover image')
        ->and(EditorialReadiness::issues($guide))->toContain('Add an official source', 'Set the review date')
        ->and(EditorialReadiness::issues($episode))->not->toContain('Add audio', 'Add a transcript');
});

test('episode readiness does not require deferred audio or transcripts', function (): void {
    $episode = Episode::factory()->create([
        'audio_path' => null,
        'audio_url' => null,
        'transcript' => null,
        'cover_image' => 'episodes/complete.jpg',
        'meta_title' => 'A complete episode title',
        'meta_description' => 'A complete episode description for search and social sharing.',
    ]);

    expect(EditorialReadiness::issues($episode))->toBeEmpty();
});

test('complete content is marked ready', function (): void {
    $post = Post::factory()->create([
        'cover_image' => 'posts/complete.jpg',
        'meta_title' => 'A complete park-planning post',
        'meta_description' => 'A complete description for search and social sharing.',
    ]);

    expect(EditorialReadiness::issues($post))->toBeEmpty()
        ->and(EditorialReadiness::label($post))->toBe('Ready')
        ->and(EditorialReadiness::summary($post))->toBe('Ready to publish.');
});

test('sourced posts require a matching official source and review date', function (): void {
    $missingReviewDate = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => null,
    ]);
    $missingSource = Post::factory()->create([
        'source_url' => null,
        'last_reviewed_at' => today(),
    ]);

    expect(EditorialReadiness::issues($missingReviewDate))->toContain('Set the review date')
        ->and(EditorialReadiness::issues($missingSource))->toContain('Add an official source');
});
