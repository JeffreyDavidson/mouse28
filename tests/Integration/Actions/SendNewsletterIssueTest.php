<?php

use App\Actions\SendNewsletterIssue;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

covers(SendNewsletterIssue::class);

pest()->use(RefreshDatabase::class);

test('sending queues one delivery for each active reader and marks the issue sent', function (): void {
    $active = Subscriber::factory()->create();
    Subscriber::factory()->pending()->create();
    Subscriber::factory()->unsubscribed()->create();
    $issue = NewsletterIssue::factory()->create();

    $queued = app(SendNewsletterIssue::class)->handle($issue);

    expect($queued)->toBe(1)
        ->and($issue->refresh()->wasSent())->toBeTrue();
    assertDatabaseHas('newsletter_deliveries', [
        'newsletter_issue_id' => $issue->id,
        'subscriber_id' => $active->id,
        'sent_at' => null,
    ]);
    assertDatabaseCount('newsletter_deliveries', 1);
    assertDatabaseCount('jobs', 1);
});

test('issues that cannot be sent are refused without queueing anything', function (NewsletterIssue $issue, string $message): void {
    Subscriber::factory()->create();

    expect(fn () => app(SendNewsletterIssue::class)->handle($issue))
        ->toThrow(LogicException::class, $message);

    assertDatabaseCount('newsletter_deliveries', 0);
    assertDatabaseCount('jobs', 0);
})->with([
    'draft' => [fn (): NewsletterIssue => NewsletterIssue::factory()->draft()->create(), 'Only published newsletter issues can be sent.'],
    'scheduled for later' => [fn (): NewsletterIssue => NewsletterIssue::factory()->scheduled()->create(), 'Only published newsletter issues can be sent.'],
    'already sent' => [fn (): NewsletterIssue => NewsletterIssue::factory()->sent()->create(), 'This newsletter issue has already been sent.'],
]);

test('a failure while queueing leaves nothing half sent and allows a clean second try', function (): void {
    Subscriber::factory()->count(2)->create();
    $issue = NewsletterIssue::factory()->create();
    $inserts = 0;
    DB::connection()->beforeExecuting(function (string $query) use (&$inserts): void {
        if (str_starts_with($query, 'insert into "jobs"') && ++$inserts === 2) {
            throw new RuntimeException('Synthetic queue failure.');
        }
    });

    expect(fn () => app(SendNewsletterIssue::class)->handle($issue))
        ->toThrow(RuntimeException::class, 'Synthetic queue failure.')
        ->and($issue->refresh()->wasSent())->toBeFalse();
    assertDatabaseCount('newsletter_deliveries', 0);
    assertDatabaseCount('jobs', 0);

    app(SendNewsletterIssue::class)->handle($issue);

    assertDatabaseCount('newsletter_deliveries', 2);
    assertDatabaseCount('jobs', 2);
});

test('a queue kept in a separate database is rejected before any delivery exists', function (): void {
    config()->set([
        'queue.connections.database.connection' => 'separate',
        'database.connections.separate' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
    ]);
    Subscriber::factory()->create();
    $issue = NewsletterIssue::factory()->create();

    expect(fn () => app(SendNewsletterIssue::class)->handle($issue))
        ->toThrow(LogicException::class, 'Newsletter deliveries must share the application database.');

    assertDatabaseCount('newsletter_deliveries', 0);
});
