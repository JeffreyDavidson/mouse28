<?php

use App\Actions\GenerateNewsletterRssFeed;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

covers(GenerateNewsletterRssFeed::class);

pest()->use(RefreshDatabase::class);

test('the feed describes the newsletter and links each live issue', function (): void {
    $issue = NewsletterIssue::factory()->create([
        'title' => 'Tips & tricks',
        'excerpt' => 'Quiet spots <and> shade.',
        'published_at' => Date::parse('2026-09-01 10:00:00'),
    ]);

    $xml = new SimpleXMLElement(app(GenerateNewsletterRssFeed::class)->handle());

    expect((string) $xml->channel->title)->toBe('Mouse28 Newsletter')
        ->and((string) $xml->channel->link)->toBe(route('newsletter.index'))
        ->and((string) $xml->channel->item[0]->title)->toBe('Tips & tricks')
        ->and((string) $xml->channel->item[0]->link)->toBe(route('newsletter.issue', $issue))
        ->and((string) $xml->channel->item[0]->description)->toBe('Quiet spots <and> shade.')
        ->and((string) $xml->channel->item[0]->pubDate)->toBe('Tue, 01 Sep 2026 10:00:00 +0000');
});

test('an empty feed is still valid', function (): void {
    $xml = new SimpleXMLElement(app(GenerateNewsletterRssFeed::class)->handle());

    expect($xml)->not->toBeFalse()
        ->and($xml->channel->item)->toBeEmpty();
});

test('the feed holds the configured number of newest issues', function (): void {
    config()->set('mouse28.newsletter_feed_items', 2);
    NewsletterIssue::factory()->create(['title' => 'Oldest issue', 'published_at' => Date::now()->subDays(9)]);
    NewsletterIssue::factory()->count(2)->create(['published_at' => Date::now()->subDay()]);

    $content = app(GenerateNewsletterRssFeed::class)->handle();

    expect(substr_count($content, '<item>'))->toBe(2)
        ->and($content)->not->toContain('Oldest issue');
});
