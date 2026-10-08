<?php

use App\Http\Controllers\NewsletterRssController;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;
use function Pest\Laravel\travelTo;

covers(NewsletterRssController::class);

pest()->use(RefreshDatabase::class);

test('the newsletter feed is valid XML and lists only live issues', function (): void {
    $live = NewsletterIssue::factory()->create();
    $draft = NewsletterIssue::factory()
        ->draft()
        ->create();
    $scheduled = NewsletterIssue::factory()
        ->scheduled()
        ->create();

    $response = get(route('newsletter.rss'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8')
        ->assertSee($live->title)
        ->assertDontSee($draft->title)
        ->assertDontSee($scheduled->title);

    expect(simplexml_load_string($this->responseContent($response)))->not->toBeFalse();
});

test('the newsletter feed is served to feed readers without starting a session or setting cookies', function (): void {
    config()->set('session.driver', 'database');

    $response = get(route('newsletter.rss'))
        ->assertOk()
        ->assertHeaderMissing('Set-Cookie');

    expect($response->headers->getCookies())->toBeEmpty();
    assertDatabaseCount('sessions', 0);
});

test('the newsletter feed renders this exact xml for a fixed set of issues', function (): void {
    travelTo(Date::parse('2026-10-07 12:00:00'));
    $tips = NewsletterIssue::factory()->create([
        'title' => 'Tips & "tricks" for \'parks\'',
        'excerpt' => 'Quiet <spots> & shade.',
        'published_at' => Date::parse('2026-10-05 08:30:00'),
    ]);
    $arrival = NewsletterIssue::factory()->create([
        'title' => 'Arrival day',
        'excerpt' => null,
        'published_at' => Date::parse('2026-10-01 09:00:00'),
    ]);

    $response = get(route('newsletter.rss'))->assertOk();

    expect($this->responseContent($response))->toBe(implode("\n", [
        '<?xml version="1.0" encoding="UTF-8"?>',
        '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">',
        '<channel>',
        '<title>Mouse28 Newsletter</title>',
        '<link>'.route('newsletter.index').'</link>',
        '<description>Disney parks through the eyes of a family raising a daughter with autism. Notes and updates from Jeffrey and Cassie.</description>',
        '<language>en-us</language>',
        '<lastBuildDate>Mon, 05 Oct 2026 08:30:00 +0000</lastBuildDate>',
        '<atom:link href="'.route('newsletter.rss').'" rel="self" type="application/rss+xml" />',
        '<item>',
        '<title>Tips &amp; "tricks" for \'parks\'</title>',
        '<link>'.route('newsletter.issue', $tips).'</link>',
        '<guid isPermaLink="true">'.route('newsletter.issue', $tips).'</guid>',
        '<description>Quiet &lt;spots&gt; &amp; shade.</description>',
        '<pubDate>Mon, 05 Oct 2026 08:30:00 +0000</pubDate>',
        '</item>',
        '<item>',
        '<title>Arrival day</title>',
        '<link>'.route('newsletter.issue', $arrival).'</link>',
        '<guid isPermaLink="true">'.route('newsletter.issue', $arrival).'</guid>',
        '<description></description>',
        '<pubDate>Thu, 01 Oct 2026 09:00:00 +0000</pubDate>',
        '</item>',
        '</channel>',
        '</rss>',
    ]));
});
