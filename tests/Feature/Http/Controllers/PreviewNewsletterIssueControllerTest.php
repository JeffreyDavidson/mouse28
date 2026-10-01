<?php

use App\Http\Controllers\PreviewNewsletterIssueController;
use App\Models\NewsletterIssue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

covers(PreviewNewsletterIssueController::class);

pest()->use(RefreshDatabase::class);

test('a signed preview link shows the draft without exposing it to search', function (): void {
    $issue = NewsletterIssue::factory()->draft()->create(['title' => 'Unfinished issue']);

    get(URL::temporarySignedRoute('preview.newsletter-issue', Date::now()->addHour(), ['newsletterIssue' => $issue]))
        ->assertOk()
        ->assertViewIs('pages.newsletter.issue')
        ->assertViewHas('isPreview', true)
        ->assertSee('Unfinished issue')
        ->assertSee('Preview mode')
        ->assertSeeHtml('noindex,nofollow')
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});

test('preview links must be signed, untampered and unexpired', function (string $case): void {
    $issue = NewsletterIssue::factory()->draft()->create();
    $signed = URL::temporarySignedRoute('preview.newsletter-issue', Date::now()->addHour(), ['newsletterIssue' => $issue]);
    $url = match ($case) {
        'unsigned' => route('preview.newsletter-issue', $issue),
        'tampered' => "{$signed}0",
        default => $signed,
    };

    if ($case === 'expired') {
        Date::setTestNow(Date::now()->addHours(2));
    }

    get($url)->assertForbidden();
})->with(['unsigned', 'tampered', 'expired']);
