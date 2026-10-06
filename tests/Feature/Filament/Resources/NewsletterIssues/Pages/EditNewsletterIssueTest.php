<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Jobs\DeliverNewsletterIssue;
use App\Mail\NewsletterIssueMail;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    actingAs(User::factory()->admin()->create());
});

test('administrators can render the edit form', function (): void {
    $issue = NewsletterIssue::factory()->draft()->create();

    get(NewsletterIssueResource::getUrl('edit', ['record' => $issue]))
        ->assertOk()
        ->assertSee($issue->title)
        ->assertSee('Save changes');
});

test('an issue can be edited', function (): void {
    $issue = NewsletterIssue::factory()->draft()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['title' => 'Updated title', 'content' => 'Updated text'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($issue->refresh())->title->toBe('Updated title')->content->toBe('Updated text');
});

test('published URLs stay locked even when the editor submits a new slug', function (): void {
    $issue = NewsletterIssue::factory()->create(['slug' => 'permanent-url']);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($issue->refresh()->slug)->toBe('permanent-url');
});

test('a previously published URL stays locked after unpublishing', function (): void {
    $issue = NewsletterIssue::factory()->create(['slug' => 'original-url']);
    $issue->refresh()->update(['status' => PublishStatus::Draft]);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($issue->refresh()->slug)->toBe('original-url');
});

test('a draft with content can be published', function (): void {
    $issue = NewsletterIssue::factory()->draft()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Newsletter Issue published');

    expect($issue->refresh()->status)->toBe(PublishStatus::Published)
        ->and($issue->published_at)->not->toBeNull();
});

test('a draft without content is not published', function (): void {
    $issue = NewsletterIssue::factory()->draft()->create(['content' => '']);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Newsletter Issue is not ready to publish');

    expect($issue->refresh()->status)->toBe(PublishStatus::Draft);
});

test('a live issue can be unpublished', function (): void {
    $issue = NewsletterIssue::factory()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Newsletter Issue unpublished');

    expect($issue->refresh()->status)->toBe(PublishStatus::Draft);
});

test('a deleted issue can be restored', function (): void {
    $issue = NewsletterIssue::factory()->create();
    $issue->delete();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('restore')
        ->assertNotified();

    $this->assertNotSoftDeleted($issue);
});

test('publishing actions disappear when admin access is revoked', function (bool $isDraft, string $action): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);
    $issue = $isDraft ? NewsletterIssue::factory()->draft()->create() : NewsletterIssue::factory()->create();
    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()]);

    $admin->is_admin = false;

    $page->assertActionHidden($action);
})->with([
    'publish' => [true, 'publish'],
    'unpublish' => [false, 'unpublish'],
]);

test('the edit page offers a preview link that opens in a new tab', function (): void {
    Date::setTestNow('2026-09-27 12:00:00');
    $issue = NewsletterIssue::factory()->draft()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', URL::temporarySignedRoute('preview.newsletter-issue', Date::now()->addHours(24), ['newsletterIssue' => $issue]))
        ->assertActionShouldOpenUrlInNewTab('preview');
});

test('sending is offered only for live issues that have not been sent', function (NewsletterIssue $issue, bool $visible): void {
    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()]);

    $visible
        ? $page->assertActionVisible('sendToSubscribers')
        : $page->assertActionHidden('sendToSubscribers');
})->with([
    'live' => [fn (): NewsletterIssue => NewsletterIssue::factory()->create(), true],
    'draft' => [fn (): NewsletterIssue => NewsletterIssue::factory()->draft()->create(), false],
    'scheduled' => [fn (): NewsletterIssue => NewsletterIssue::factory()->scheduled()->create(), false],
    'already sent' => [fn (): NewsletterIssue => NewsletterIssue::factory()->sent()->create(), false],
]);

test('sending queues the issue for active readers only', function (): void {
    Subscriber::factory()->create();
    Subscriber::factory()->pending()->create();
    $issue = NewsletterIssue::factory()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('sendToSubscribers')
        ->assertNotified('Queued for 1 subscriber');

    expect($issue->refresh()->wasSent())->toBeTrue();
    $this->assertDatabaseCount('newsletter_deliveries', 1);
});

test('sending explains when nobody is subscribed and keeps the issue unsent', function (): void {
    Subscriber::factory()->pending()->create();
    $issue = NewsletterIssue::factory()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('sendToSubscribers')
        ->assertNotified('There are no active subscribers to send to');

    expect($issue->refresh()->wasSent())->toBeFalse();
    $this->assertDatabaseCount('newsletter_deliveries', 0);
});

test('a test email goes to the admin addresses even for a draft', function (): void {
    Mail::fake();
    config()->set('mail.admin_address', 'owner@example.test');
    $issue = NewsletterIssue::factory()->draft()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('sendTestEmail')
        ->assertNotified('Test email sent to owner@example.test');

    Mail::assertSent(NewsletterIssueMail::class, fn (NewsletterIssueMail $mail): bool => $mail->hasTo('owner@example.test'));
    expect($issue->refresh()->wasSent())->toBeFalse();
});

test('a sent issue shows how many deliveries have gone out', function (): void {
    $issue = NewsletterIssue::factory()->sent()->create();
    NewsletterDelivery::factory()->for($issue)->sent()->create();
    NewsletterDelivery::factory()->for($issue)->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->assertSee('Delivered to 1 of 2 subscribers');
});

test('sending actions disappear when admin access is revoked', function (string $action): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);
    $issue = NewsletterIssue::factory()->create();
    $page = livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()]);

    $admin->is_admin = false;

    $page->assertActionHidden($action);
})->with(['sendToSubscribers', 'sendTestEmail']);

test('a draft slug cannot be changed to one used by a fixed newsletter page', function (string $slug): void {
    $issue = NewsletterIssue::factory()->draft()->create(['slug' => 'original-issue']);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['slug' => $slug])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'not_in']);

    expect($issue->refresh()->slug)->toBe('original-issue');
})->with(['rss', 'confirmed']);

test('a test email sends the unsaved edits on screen and saves them', function (): void {
    Mail::fake();
    config()->set('mail.admin_address', 'owner@example.test');
    $issue = NewsletterIssue::factory()->draft()->create(['title' => 'Saved title', 'content' => 'Saved content.']);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['title' => 'Edited title', 'content' => 'Edited content.'])
        ->callAction('sendTestEmail')
        ->assertHasNoFormErrors()
        ->assertNotified('Test email sent to owner@example.test');

    Mail::assertSent(NewsletterIssueMail::class, function (NewsletterIssueMail $mail): bool {
        $mail->assertHasSubject('Edited title')
            ->assertSeeInHtml('Edited content.');

        return true;
    });
    expect($issue->refresh())
        ->title->toBe('Edited title')
        ->content->toBe('Edited content.');
});

test('sending to subscribers sends the unsaved edits on screen', function (): void {
    Mail::fake();
    $reader = Subscriber::factory()->create();
    $issue = NewsletterIssue::factory()->create(['title' => 'Saved title', 'content' => 'Saved content.']);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['title' => 'Edited title', 'content' => 'Edited content.'])
        ->callAction('sendToSubscribers')
        ->assertHasNoFormErrors()
        ->assertNotified('Queued for 1 subscriber');

    DeliverNewsletterIssue::dispatchSync(NewsletterDelivery::query()->sole());

    Mail::assertSent(NewsletterIssueMail::class, function (NewsletterIssueMail $mail) use ($reader): bool {
        $mail->assertHasSubject('Edited title')
            ->assertSeeInHtml('Edited content.');

        return $mail->hasTo($reader->email);
    });
});

test('nothing is sent when the unsaved edits are invalid', function (string $action): void {
    Mail::fake();
    Subscriber::factory()->create();
    $issue = NewsletterIssue::factory()->create(['title' => 'Saved title']);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->fillForm(['title' => ''])
        ->callAction($action)
        ->assertHasErrors(['data.title' => 'required']);

    Mail::assertNothingSent();
    expect($issue->refresh())
        ->title->toBe('Saved title')
        ->wasSent()->toBeFalse();
    $this->assertDatabaseCount('newsletter_deliveries', 0);
})->with(['sendTestEmail', 'sendToSubscribers']);
