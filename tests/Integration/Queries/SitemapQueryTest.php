<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Queries\SitemapQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(SitemapQuery::class);

test('sitemap entries break publish-time ties by id so their order is stable on MySQL', function (): void {
    Post::factory()->create();
    Episode::factory()->create();
    NewsletterIssue::factory()->create();
    Guide::factory()->create();
    $query = app(SitemapQuery::class);

    $orderings = publishTimeOrderings(function () use ($query): void {
        $query->posts();
        $query->episodes();
        $query->newsletterIssues();
        $query->guides();
    });

    expect($orderings)->toHaveCount(4)
        ->each->toMatch(STABLE_PUBLISH_TIME_ORDER);
});

test('sitemap queries select only the slug and modification date', function (): void {
    Post::factory()->create();
    Episode::factory()->create();
    NewsletterIssue::factory()->create();
    Guide::factory()->create();
    $query = app(SitemapQuery::class);

    $records = [
        $query->posts()
            ->first(),
        $query->episodes()
            ->first(),
        $query->newsletterIssues()
            ->first(),
        $query->guides()
            ->first(),
    ];

    expect(array_map(fn (?Model $record): array => array_keys($record?->getAttributes() ?? []), $records))
        ->each->toBe(['slug', 'updated_at']);
});
