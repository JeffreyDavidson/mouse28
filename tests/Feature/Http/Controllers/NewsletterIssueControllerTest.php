<?php

use App\Http\Controllers\NewsletterIssueController;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\get;

covers(NewsletterIssueController::class);

pest()->use(RefreshDatabase::class);

test('the archive lists live issues newest first and hides the rest', function (): void {
    $older = NewsletterIssue::factory()->create(['title' => 'Older issue', 'published_at' => Date::now()->subDays(3)]);
    $newer = NewsletterIssue::factory()->create(['title' => 'Newer issue', 'excerpt' => 'Newer summary', 'published_at' => Date::now()->subDay()]);
    $draft = NewsletterIssue::factory()->draft()->create(['title' => 'Draft issue']);
    $scheduled = NewsletterIssue::factory()->scheduled()->create(['title' => 'Scheduled issue']);
    $trashed = NewsletterIssue::factory()->create(['title' => 'Trashed issue']);
    $trashed->delete();

    get(route('newsletter.index'))
        ->assertOk()
        ->assertViewIs('pages.newsletter.index')
        ->assertSeeInOrder([$newer->title, $older->title])
        ->assertSee('Newer summary')
        ->assertSeeHtml('href="'.route('newsletter.issue', $newer).'"')
        ->assertDontSee([$draft->title, $scheduled->title, $trashed->title]);
});

test('the archive explains when there are no issues yet', function (): void {
    get(route('newsletter.index'))
        ->assertOk()
        ->assertSee('No issues yet');
});

test('the archive offers email and feed subscriptions and announces its feed', function (): void {
    get(route('newsletter.index'))
        ->assertOk()
        ->assertSeeHtml('href="#newsletter"')
        ->assertSeeHtml('href="'.route('newsletter.rss').'"')
        ->assertSeeHtml('type="application/rss+xml"');
});

test('archive pages hold the configured number of issues and each has its own canonical URL', function (): void {
    config()->set('mouse28.newsletter_issues_per_page', 2);
    NewsletterIssue::factory()->count(3)->create();

    get(route('newsletter.index'))
        ->assertOk()
        ->assertSeeHtml('<link rel="canonical" href="'.route('newsletter.index').'">');

    get(route('newsletter.index', ['page' => 2]))
        ->assertOk()
        ->assertSeeHtml('<link rel="canonical" href="'.route('newsletter.index', ['page' => 2]).'">')
        ->assertSee('Page 2');
});

test('an archive page beyond the last one does not exist', function (): void {
    NewsletterIssue::factory()->create();

    get(route('newsletter.index', ['page' => 9]))->assertNotFound();
});

test('a live issue renders its title, date, summary and Markdown safely', function (): void {
    $issue = NewsletterIssue::factory()->create([
        'title' => 'Planning a sensory friendly day',
        'excerpt' => 'A short summary.',
        'content' => "## Packing list\n\n<script>alert(1)</script>\n\n[Unsafe](javascript:alert(1)) and [safe](https://example.test/page)",
        'published_at' => Date::parse('2026-09-01 10:00:00'),
    ]);

    get(route('newsletter.issue', $issue))
        ->assertOk()
        ->assertViewIs('pages.newsletter.issue')
        ->assertSee('Planning a sensory friendly day')
        ->assertSee('September 1, 2026')
        ->assertSee('A short summary.')
        ->assertSeeHtml('<h2>Packing list</h2>')
        ->assertSeeHtml('href="https://example.test/page"')
        ->assertDontSeeHtml('<script>alert(1)</script>')
        ->assertDontSeeHtml('javascript:alert(1)')
        ->assertSeeHtml('<meta name="robots" content="index,follow">')
        ->assertSeeHtml('<link rel="canonical" href="'.route('newsletter.issue', $issue).'">')
        ->assertSeeHtml('href="'.route('newsletter.index').'"');
});

test('issues that are not live return not found', function (NewsletterIssue $issue): void {
    get(route('newsletter.issue', $issue))->assertNotFound();
})->with([
    'draft' => fn (): NewsletterIssue => NewsletterIssue::factory()->draft()->create(),
    'scheduled' => fn (): NewsletterIssue => NewsletterIssue::factory()->scheduled()->create(),
    'trashed' => fn (): NewsletterIssue => tap(NewsletterIssue::factory()->create())->delete(),
]);

test('the feed address is never mistaken for an issue', function (): void {
    get(route('newsletter.rss'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('the footer sign-up offers the newsletter archive', function (): void {
    get(route('home'))
        ->assertOk()
        ->assertSeeHtml('href="'.route('newsletter.index').'"')
        ->assertSee('Read past issues');
});

test('newsletter pages show an evening publish date on its Eastern day', function (): void {
    $this->travelTo('2026-10-10 12:00:00');
    $issue = NewsletterIssue::factory()->create(['published_at' => '2026-10-06 01:00:00']);

    get(route('newsletter.index'))
        ->assertOk()
        ->assertSeeHtml('datetime="2026-10-05"')
        ->assertSee('October 5, 2026')
        ->assertDontSee('October 6, 2026');

    get(route('newsletter.issue', $issue))
        ->assertOk()
        ->assertSeeHtml('datetime="2026-10-05"')
        ->assertSee('October 5, 2026')
        ->assertDontSee('October 6, 2026');
});
