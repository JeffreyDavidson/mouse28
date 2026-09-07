<?php

use App\Enums\PublicationStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\EditorialReadiness;

/** @param class-string<Post|Guide|Episode> $modelClass */
test('publication status respects publication flags and dates', function (string $modelClass, bool $isPublished, ?int $offset, PublicationStatus $expected): void {
    $this->freezeSecond();
    $content = $modelClass::factory()->make([
        'is_published' => $isPublished,
        'published_at' => $offset === null ? null : now()->addSeconds($offset),
    ]);

    $status = EditorialReadiness::status($content);

    expect($status)->toBe($expected);
})->with([
    'post' => [Post::class],
    'guide' => [Guide::class],
    'episode' => [Episode::class],
])->with([
    'draft' => [false, null, PublicationStatus::Draft],
    'draft with a past date' => [false, -1, PublicationStatus::Draft],
    'missing date' => [true, null, PublicationStatus::NeedsPublishDate],
    'scheduled' => [true, 1, PublicationStatus::Scheduled],
    'published now' => [true, 0, PublicationStatus::Published],
    'published earlier' => [true, -1, PublicationStatus::Published],
]);

test('readiness reports actionable issues for each content type', function (): void {
    $post = Post::factory()->make([
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);
    $guide = Guide::factory()->make([
        'source_url' => null,
        'last_reviewed_at' => null,
    ]);
    $episode = Episode::factory()->make([
        'audio_url' => null,
        'transcript' => null,
    ]);

    $label = EditorialReadiness::label($post);
    $postIssues = EditorialReadiness::issues($post);
    $guideIssues = EditorialReadiness::issues($guide);
    $episodeIssues = EditorialReadiness::issues($episode);

    expect($label)->toBe('3 missing')
        ->and($postIssues)->toContain('Add a cover image')
        ->and($guideIssues)->toContain('Add an official source', 'Set the review date')
        ->and($episodeIssues)->not->toContain('Add audio', 'Add a transcript');
});

test('episode readiness does not require deferred audio or transcripts', function (): void {
    $episode = Episode::factory()->make([
        'audio_path' => null,
        'audio_url' => null,
        'transcript' => null,
        'cover_image' => 'episodes/complete.jpg',
        'meta_title' => 'A complete episode title',
        'meta_description' => 'A complete episode description for search and social sharing.',
    ]);

    $issues = EditorialReadiness::issues($episode);

    expect($issues)->toBeEmpty();
});

test('complete content is marked ready', function (): void {
    $post = Post::factory()->make([
        'cover_image' => 'posts/complete.jpg',
        'meta_title' => 'A complete park-planning post',
        'meta_description' => 'A complete description for search and social sharing.',
    ]);

    $issues = EditorialReadiness::issues($post);
    $label = EditorialReadiness::label($post);
    $summary = EditorialReadiness::summary($post);

    expect($issues)->toBeEmpty()
        ->and($label)->toBe('Ready')
        ->and($summary)->toBe('Ready to publish.');
});

test('sourced posts require a matching official source and review date', function (): void {
    $missingReviewDate = Post::factory()->make([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => null,
    ]);
    $missingSource = Post::factory()->make([
        'source_url' => null,
        'last_reviewed_at' => today(),
    ]);

    $missingReviewDateIssues = EditorialReadiness::issues($missingReviewDate);
    $missingSourceIssues = EditorialReadiness::issues($missingSource);

    expect($missingReviewDateIssues)->toContain('Set the review date')
        ->and($missingSourceIssues)->toContain('Add an official source');
});
