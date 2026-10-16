<?php

use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Services\ContentUrls;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use JeffreyDavidson\CreatorKit\Contracts\ContentUrls as ContentUrlsContract;

covers(ContentUrls::class);

pest()->use(RefreshDatabase::class);

test('creator-kit gets Mouse28 content URLs', function (): void {
    expect(app(ContentUrlsContract::class)::class)->toBe(ContentUrls::class);
});

test('a live issue links to its public page', function (): void {
    // Arrange
    $issue = NewsletterIssue::factory()->create();

    // Act
    $url = app(ContentUrls::class)->publicOrPreviewUrl($issue);

    // Assert
    expect($url)->toBe(route('newsletter.issue', $issue));
});

test('an issue that is not live yet links to its signed preview', function (NewsletterIssue $issue): void {
    // Arrange
    Date::setTestNow('2026-09-27 12:00:00');

    // Act
    $url = app(ContentUrls::class)->publicOrPreviewUrl($issue);

    // Assert
    expect($url)->toBe(URL::temporarySignedRoute('preview.newsletter-issue', Date::now()->addHours(24), ['newsletterIssue' => $issue]));
})->with([
    'draft' => [fn (): NewsletterIssue => NewsletterIssue::factory()
        ->draft()
        ->create()],
    'scheduled' => [fn (): NewsletterIssue => NewsletterIssue::factory()
        ->scheduled()
        ->create()],
]);

test('other content has no view on site link', function (): void {
    expect(fn () => app(ContentUrls::class)->publicOrPreviewUrl(new Post))
        ->toThrow(LogicException::class, 'Mouse28 has no "View on site" link for '.Post::class.'.');
});
