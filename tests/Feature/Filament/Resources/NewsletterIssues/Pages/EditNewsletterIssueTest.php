<?php

use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Models\NewsletterIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
    $issue->refresh()->update(['is_published' => false]);

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

    expect($issue->refresh()->is_published)->toBeTrue()
        ->and($issue->published_at)->not->toBeNull();
});

test('a draft without content is not published', function (): void {
    $issue = NewsletterIssue::factory()->draft()->create(['content' => '']);

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Newsletter Issue is not ready to publish');

    expect($issue->refresh()->is_published)->toBeFalse();
});

test('a live issue can be unpublished', function (): void {
    $issue = NewsletterIssue::factory()->create();

    livewire(EditNewsletterIssue::class, ['record' => $issue->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Newsletter Issue unpublished');

    expect($issue->refresh()->is_published)->toBeFalse();
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
