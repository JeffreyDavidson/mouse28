<?php

use App\Models\NewsletterIssue;
use App\Queries\NewsletterRssFeedQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

covers(NewsletterRssFeedQuery::class);

pest()->use(RefreshDatabase::class);

test('the newsletter feed query returns only live issues', function (): void {
    $live = NewsletterIssue::factory()->create();
    NewsletterIssue::factory()
        ->draft()
        ->create();
    NewsletterIssue::factory()
        ->scheduled()
        ->create();

    $issues = app(NewsletterRssFeedQuery::class)->get();

    expect($issues->modelKeys())->toBe([$live->id]);
});

test('the newsletter feed query holds the configured number of newest issues', function (): void {
    config()->set('mouse28.newsletter_feed_items', 2);
    $oldest = NewsletterIssue::factory()->create(['title' => 'Oldest issue', 'published_at' => Date::now()->subDays(9)]);
    $newer = NewsletterIssue::factory()->create(['published_at' => Date::now()->subDays(2)]);
    $newest = NewsletterIssue::factory()->create(['published_at' => Date::now()->subDay()]);

    $issues = app(NewsletterRssFeedQuery::class)->get();

    expect($issues->modelKeys())->toBe([$newest->id, $newer->id])
        ->not->toContain($oldest->id);
});

test('the newsletter feed query breaks publish-time ties by id so their order is stable on MySQL', function (): void {
    NewsletterIssue::factory()
        ->count(2)
        ->create();

    $orderings = publishTimeOrderings(fn () => app(NewsletterRssFeedQuery::class)->get());

    expect($orderings)->not->toBeEmpty()
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});
