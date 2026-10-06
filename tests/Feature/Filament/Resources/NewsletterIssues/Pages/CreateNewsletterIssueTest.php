<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;
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

test('an issue can be saved as a draft', function (): void {
    livewire(CreateNewsletterIssue::class)
        ->fillForm(['title' => 'Sample issue', 'slug' => 'sample-issue', 'excerpt' => 'A short summary.', 'content' => 'Draft text'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(NewsletterIssue::query()->sole())
        ->content->toBe('Draft text')
        ->status->toBe(PublishStatus::Draft)
        ->sent_at->toBeNull();
});

test('issue creation validates the fields the server requires', function (array $overrides, string $field, string $rule): void {
    NewsletterIssue::factory()->create(['slug' => 'existing-issue']);
    $valid = ['title' => 'Sample issue', 'slug' => 'sample-issue', 'content' => 'Draft text'];

    livewire(CreateNewsletterIssue::class)
        ->fillForm([...$valid, ...$overrides])
        ->call('create')
        ->assertHasFormErrors([$field => $rule]);
})->with([
    'missing title' => [['title' => null], 'title', 'required'],
    'missing content' => [['content' => null], 'content', 'required'],
    'duplicate slug' => [['slug' => 'existing-issue'], 'slug', 'unique'],
    'slug that cannot form a route' => [['slug' => 'Invalid/URL'], 'slug', 'regex'],
    'slug reserved for the feed' => [['slug' => 'rss'], 'slug', 'not_in'],
    'slug reserved for the confirmed page' => [['slug' => 'confirmed'], 'slug', 'not_in'],
    'excerpt over the limit' => [['excerpt' => str_repeat('a', 301)], 'excerpt', 'max'],
]);

test('the create form explains how issues are published and sent', function (): void {
    get(NewsletterIssueResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Issue')
        ->assertSee('use the Publish action');
});
