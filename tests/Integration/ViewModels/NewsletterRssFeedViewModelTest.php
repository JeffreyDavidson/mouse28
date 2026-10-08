<?php

use App\Models\NewsletterIssue;
use App\ViewModels\NewsletterRssFeedViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

covers(NewsletterRssFeedViewModel::class);

pest()->use(RefreshDatabase::class);

test('the newsletter feed describes the newsletter and links each live issue', function (): void {
    $issue = NewsletterIssue::factory()->create([
        'title' => 'Tips & tricks',
        'excerpt' => 'Quiet spots <and> shade.',
        'published_at' => Date::parse('2026-09-01 10:00:00'),
    ]);

    $channel = app(NewsletterRssFeedViewModel::class)->data();

    expect($channel)->toMatchArray([
        'title' => 'Mouse28 Newsletter',
        'link' => route('newsletter.index'),
        'description' => 'Disney parks through the eyes of a family raising a daughter with autism. Notes and updates from Jeffrey and Cassie.',
        'feedUrl' => route('newsletter.rss'),
    ])
        ->and($channel['items'])
        ->toHaveCount(1)
        ->and($channel['items'][0])
        ->toMatchArray([
            'title' => 'Tips & tricks',
            'link' => route('newsletter.issue', $issue),
            'description' => 'Quiet spots <and> shade.',
        ])
        ->and($channel['items'][0]['publishedAt']?->toRssString())
        ->toBe('Tue, 01 Sep 2026 10:00:00 +0000');
});

test('the newsletter feed has no items when no issue is live', function (): void {
    NewsletterIssue::factory()
        ->draft()
        ->create();

    expect(app(NewsletterRssFeedViewModel::class)->data()['items'])->toBeEmpty();
});
