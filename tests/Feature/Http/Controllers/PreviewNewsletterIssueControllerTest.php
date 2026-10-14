<?php

use App\Http\Controllers\PreviewNewsletterIssueController;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;
use function Pest\Laravel\travel;

covers(PreviewNewsletterIssueController::class);

pest()->use(RefreshDatabase::class);

test('a signed preview link shows the draft without exposing it to search', function (): void {
    $issue = NewsletterIssue::factory()
        ->draft()
        ->create(['title' => 'Unfinished issue']);

    get(URL::temporarySignedRoute('preview.newsletter-issue', Date::now()->addHour(), ['newsletterIssue' => $issue]))
        ->assertOk()
        ->assertViewIs('pages.newsletter.issue')
        ->assertViewHas('isPreview', true)
        ->assertSee('Unfinished issue')
        ->assertSee('Preview mode')
        ->assertSeeHtml('noindex,nofollow')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

test('expired preview links return forbidden', function (): void {
    // Arrange
    $issue = NewsletterIssue::factory()
        ->draft()
        ->create();
    $signed = URL::temporarySignedRoute('preview.newsletter-issue', Date::now()->addHour(), ['newsletterIssue' => $issue]);

    travel(2)->hours();

    // Act
    $response = get($signed);

    // Assert
    $response->assertForbidden();
});

test('tampered temporary preview links return forbidden', function (): void {
    // Arrange
    $issue = NewsletterIssue::factory()
        ->draft()
        ->create();
    $signed = URL::temporarySignedRoute('preview.newsletter-issue', Date::now()->addHour(), ['newsletterIssue' => $issue]);

    // Act
    $response = get("{$signed}0");

    // Assert
    $response->assertForbidden();
});

test('an invalid signature is refused before the draft is looked up, whether or not it exists', function (string $signature, bool $draftExists): void {
    $newsletterIssue = NewsletterIssue::factory()
        ->draft()
        ->create();
    $slug = $draftExists ? $newsletterIssue->slug : 'missing-draft';
    DB::enableQueryLog();

    get(invalidlySignedRoute('preview.newsletter-issue', ['newsletterIssue' => $slug], $signature))
        ->assertForbidden();

    expect(DB::getQueryLog())->toBeEmpty();
})
    ->with('invalid signatures')
    ->with('existing and missing records');
