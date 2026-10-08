<?php

use App\Http\Controllers\NewsletterRssController;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;

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
