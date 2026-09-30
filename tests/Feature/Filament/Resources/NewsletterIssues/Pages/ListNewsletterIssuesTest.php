<?php

use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Filament\Resources\NewsletterIssues\Pages\ListNewsletterIssues;
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

test('administrators can render the issue list', function (): void {
    get(NewsletterIssueResource::getUrl())
        ->assertOk()
        ->assertSee('Newsletter Issues')
        ->assertSee('New Issue');
});

test('the list shows each issue with a readable status', function (): void {
    $live = NewsletterIssue::factory()->create();
    $scheduled = NewsletterIssue::factory()->scheduled()->create();
    $draft = NewsletterIssue::factory()->draft()->create();

    livewire(ListNewsletterIssues::class)
        ->assertCanSeeTableRecords([$live, $scheduled, $draft])
        ->assertSee(['Published', 'Scheduled', 'Draft']);
});

test('the tabs narrow the list to drafts, scheduled or published issues', function (string $tab, string $visible): void {
    $issues = [
        'drafts' => NewsletterIssue::factory()->draft()->create(),
        'scheduled' => NewsletterIssue::factory()->scheduled()->create(),
        'published' => NewsletterIssue::factory()->create(),
    ];

    livewire(ListNewsletterIssues::class)
        ->set('activeTab', $tab)
        ->assertCanSeeTableRecords([$issues[$visible]])
        ->assertCountTableRecords(1);
})->with([
    'drafts' => ['drafts', 'drafts'],
    'scheduled' => ['scheduled', 'scheduled'],
    'published' => ['published', 'published'],
]);

test('the header does not count scheduled issues as published', function (): void {
    NewsletterIssue::factory()->create();
    NewsletterIssue::factory()->scheduled()->create();
    NewsletterIssue::factory()->draft()->create();

    $header = livewire(ListNewsletterIssues::class)->instance()->getHeader();

    expect($header?->getData())->toMatchArray(['published' => 1, 'drafts' => 1]);
});

test('issues can be found by title', function (): void {
    $match = NewsletterIssue::factory()->create(['title' => 'Sensory friendly planning']);
    $other = NewsletterIssue::factory()->create(['title' => 'Packing list']);

    livewire(ListNewsletterIssues::class)
        ->searchTable('Sensory')
        ->assertCanSeeTableRecords([$match])
        ->assertCanNotSeeTableRecords([$other]);
});

test('other users cannot reach the issue list', function (): void {
    actingAs(User::factory()->create());

    get(NewsletterIssueResource::getUrl())->assertForbidden();
});
