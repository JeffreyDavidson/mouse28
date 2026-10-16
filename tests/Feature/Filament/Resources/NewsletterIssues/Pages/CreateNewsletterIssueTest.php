<?php

use App\Models\NewsletterIssue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\NewsletterIssues\Pages\CreateNewsletterIssue;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
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
    'slug reserved for the feed' => [['slug' => 'rss'], 'slug', 'This slug is already used by another newsletter page. Choose a different slug.'],
    'slug reserved for the confirmed page' => [['slug' => 'confirmed'], 'slug', 'This slug is already used by another newsletter page. Choose a different slug.'],
]);

test('the create form has the issue, publishing and SEO sections', function (): void {
    // Act
    $response = get(NewsletterIssueResource::getUrl('create'));

    // Assert
    $response->assertOk()
        ->assertSee('Create Newsletter Issue')
        ->assertSeeInOrder(['Issue Content', 'Publishing', 'SEO']);
});
