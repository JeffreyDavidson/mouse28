<?php

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Services\ContentPublishingSuggestions;
use App\Support\EditorialReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Contracts\PublishingSuggestions;

covers(ContentPublishingSuggestions::class);

pest()->use(RefreshDatabase::class);

test('creator-kit gets Mouse28 publishing suggestions', function (): void {
    expect(app(PublishingSuggestions::class)::class)->toBe(ContentPublishingSuggestions::class);
});

test('a post that can be published is still told about its optional gaps', function (): void {
    // Arrange
    $post = Post::factory()->create(['featured_image_path' => null]);

    // Act
    $suggestions = new ContentPublishingSuggestions()->suggestions($post);

    // Assert
    expect($post->publishingIssues())->toBeEmpty()
        ->and($suggestions)
        ->toBe(['Add a cover image', 'Add an SEO title', 'Add an SEO description']);
});

test('suggestions leave out what already blocks publishing', function (Post|Episode $content): void {
    // Act
    $suggestions = new ContentPublishingSuggestions()->suggestions($content);

    // Assert
    expect($content->publishingIssues())->not->toBeEmpty()
        ->and($suggestions)
        ->toBe(array_values(array_diff(EditorialReadiness::issues($content), $content->publishingIssues())))
        ->not->toContain(...$content->publishingIssues());
})->with([
    'post without content' => [fn (): Post => Post::factory()->create(['content' => null])],
    'episode without a description' => [fn (): Episode => Episode::factory()->create(['description' => null])],
]);

test('content without an editorial checklist gets no suggestions', function (): void {
    // Arrange
    $issue = NewsletterIssue::factory()->create();

    // Act
    $suggestions = new ContentPublishingSuggestions()->suggestions($issue);

    // Assert
    expect($suggestions)->toBeEmpty();
});
