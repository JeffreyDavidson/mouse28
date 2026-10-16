<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Services\ContentUrls;
use App\Support\Content\PreviewUrlGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use JeffreyDavidson\CreatorKit\Contracts\ContentUrls as ContentUrlsContract;

covers(ContentUrls::class);

pest()->use(RefreshDatabase::class);

test('creator-kit gets Mouse28 content URLs', function (): void {
    expect(app(ContentUrlsContract::class)::class)->toBe(ContentUrls::class);
});

test('live content links to its public page', function (Post|Episode|NewsletterIssue $content, string $route): void {
    // Act
    $url = app(ContentUrls::class)->publicOrPreviewUrl($content);

    // Assert
    expect($url)->toBe(route($route, $content));
})->with([
    'post' => [fn (): Post => Post::factory()->create(), 'blog.show'],
    'episode' => [fn (): Episode => Episode::factory()->create(), 'episodes.show'],
    'newsletter issue' => [fn (): NewsletterIssue => NewsletterIssue::factory()->create(), 'newsletter.issue'],
]);

test('content that is not live yet links to its signed preview', function (Post|Episode|NewsletterIssue $content): void {
    // Arrange
    Date::setTestNow('2026-09-27 12:00:00');

    // Act
    $url = app(ContentUrls::class)->publicOrPreviewUrl($content);

    // Assert
    expect($url)->toBe(app(PreviewUrlGenerator::class)->for($content));
})->with([
    'draft post' => [fn (): Post => Post::factory()
        ->draft()
        ->create()],
    'scheduled episode' => [fn (): Episode => Episode::factory()
        ->scheduled()
        ->create()],
    'draft issue' => [fn (): NewsletterIssue => NewsletterIssue::factory()
        ->draft()
        ->create()],
    'scheduled issue' => [fn (): NewsletterIssue => NewsletterIssue::factory()
        ->scheduled()
        ->create()],
]);

test('other content has no view on site link', function (): void {
    expect(fn () => app(ContentUrls::class)->publicOrPreviewUrl(new Guide))
        ->toThrow(LogicException::class, 'Mouse28 has no "View on site" link for '.Guide::class.'.');
});
