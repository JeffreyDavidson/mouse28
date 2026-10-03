<?php

use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\EditorialReadiness;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(EditorialReadiness::class);

test('live or scheduled content without a publish date is asked for one', function (PostFactory|GuideFactory|EpisodeFactory $factory, PublishStatus $status, bool $dated, bool $needsDate): void {
    $content = $factory->makeOne([
        'status' => $status,
        'published_at' => $dated ? now() : null,
    ]);

    $issues = EditorialReadiness::issues($content);

    expect(in_array('Set a publish date', $issues, true))->toBe($needsDate);
})->with([
    'post' => fn () => Post::factory(),
    'guide' => fn () => Guide::factory(),
    'episode' => fn () => Episode::factory(),
])->with([
    'undated draft' => [PublishStatus::Draft, false, false],
    'undated in review' => [PublishStatus::InReview, false, false],
    'undated published' => [PublishStatus::Published, false, true],
    'undated scheduled' => [PublishStatus::Scheduled, false, true],
    'dated published' => [PublishStatus::Published, true, false],
]);

test('readiness reports actionable issues for each content type', function (): void {
    $post = Post::factory()->make([
        'featured_image_path' => null,
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
        'featured_image_path' => 'episodes/complete.jpg',
        'meta_title' => 'A complete episode title',
        'meta_description' => 'A complete episode description for search and social sharing.',
    ]);

    $issues = EditorialReadiness::issues($episode);

    expect($issues)->toBeEmpty();
});

test('complete content is marked ready', function (): void {
    $post = Post::factory()->make([
        'featured_image_path' => 'posts/complete.jpg',
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
        'source_url' => 'https://source.example/accessibility-guide',
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

test('readiness asks for content until the content is written', function (PostFactory|GuideFactory $factory, string $issue, ?string $content, bool $missing): void {
    $record = $factory->makeOne(['content' => $content]);

    expect(in_array($issue, EditorialReadiness::issues($record), true))->toBe($missing);
})->with([
    'post' => [fn () => Post::factory(), 'Add post content'],
    'guide' => [fn () => Guide::factory(), 'Add guide content'],
])->with([
    'missing content' => [null, true],
    'empty content' => ['', true],
    'written content' => ['Plan a flexible arrival.', false],
]);
