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
use RalphJSmit\Laravel\SEO\Models\SEO;

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
    ]);
    $guide = Guide::factory()->make([
        'source_url' => null,
        'last_reviewed_at' => null,
    ]);
    $episode = Episode::factory()->make([
        'transcript' => null,
    ]);

    $label = EditorialReadiness::label($post);
    $postIssues = EditorialReadiness::issues($post);
    $guideIssues = EditorialReadiness::issues($guide);
    $episodeIssues = EditorialReadiness::issues($episode);

    expect($label)->toBe('3 missing')
        ->and($postIssues)
        ->toContain('Add a cover image')
        ->and($guideIssues)
        ->toContain('Add an official source', 'Set the review date')
        ->and($episodeIssues)
        ->not->toContain('Add audio', 'Add a transcript');
});

test('episode readiness does not require deferred audio or transcripts', function (): void {
    $episode = Episode::factory()->make([
        'transcript' => null,
        'featured_image_path' => 'episodes/complete.jpg',
        'transistor_url' => 'https://share.transistor.fm/s/428d650c',
    ]);
    $episode->setRelation('seo', new SEO(['title' => 'A complete episode title', 'description' => 'A complete episode description for search and social sharing.']));

    $issues = EditorialReadiness::issues($episode);

    expect($issues)->toBeEmpty();
});

test('complete content is marked ready', function (): void {
    $post = Post::factory()->make([
        'featured_image_path' => 'posts/complete.jpg',
    ]);
    $post->setRelation('seo', new SEO(['title' => 'A complete park-planning post', 'description' => 'A complete description for search and social sharing.']));

    $issues = EditorialReadiness::issues($post);
    $label = EditorialReadiness::label($post);
    $summary = EditorialReadiness::summary($post);

    expect($issues)->toBeEmpty()
        ->and($label)
        ->toBe('Ready')
        ->and($summary)
        ->toBe('Ready to publish.');
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
        ->and($missingSourceIssues)
        ->toContain('Add an official source');
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

test('episode readiness asks for the same playable media that publishing requires', function (?string $transistorUrl, ?string $youtubeUrl, bool $asked): void {
    $episode = Episode::factory()->make([
        'featured_image_path' => 'episodes/complete.jpg',
        'transistor_url' => $transistorUrl,
        'youtube_url' => $youtubeUrl,
    ]);
    $episode->setRelation('seo', new SEO(['title' => 'A complete episode title', 'description' => 'A complete episode description.']));

    $issues = EditorialReadiness::issues($episode);

    expect(in_array('Add a Transistor share link or a YouTube video', $issues, true))->toBe($asked)
        ->and(in_array('Add a Transistor share link or a YouTube video', $episode->publishingIssues(), true))
        ->toBe($asked);
})->with([
    'Transistor share link' => ['https://share.transistor.fm/s/428d650c', null, false],
    'YouTube video' => [null, 'https://www.youtube.com/watch?v=abc', false],
    'Transistor link that is not a share link' => ['https://example.com/episode', null, true],
    'neither' => [null, null, true],
]);

test('episodes without playable media need attention', function (?string $transistorUrl, ?string $youtubeUrl, bool $needsAttention): void {
    $episode = Episode::factory()
        ->withSeo('A complete episode title', 'A complete episode description.')
        ->create([
            'featured_image_path' => 'episodes/complete.jpg',
            'transistor_url' => $transistorUrl,
            'youtube_url' => $youtubeUrl,
        ]);

    $listed = Episode::query()
        ->needsAttention()
        ->whereKey($episode->id)
        ->exists();

    expect($listed)->toBe($needsAttention)
        ->and(EditorialReadiness::issues($episode->load('seo')) !== [])
        ->toBe($needsAttention);
})->with([
    'Transistor share link' => ['https://share.transistor.fm/s/428d650c', null, false],
    'YouTube video' => [null, 'https://www.youtube.com/watch?v=abc', false],
    'Transistor link that is not a share link' => ['https://example.com/episode', null, true],
    'blank links' => ['', '', true],
    'neither' => [null, null, true],
]);
