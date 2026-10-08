<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\User;
use App\Support\DisplayTimezone;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\freezeSecond;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
});

dataset('publishable edit pages', [
    'post' => [EditPost::class],
    'guide' => [EditGuide::class],
    'episode' => [EditEpisode::class],
    'newsletter issue' => [EditNewsletterIssue::class],
]);

/**
 * A draft that is ready to publish, of the content type the edit page manages.
 *
 * @param  array<string, mixed>  $attributes
 */
function readyDraftFor(string $editPage, array $attributes = []): Post|Guide|Episode|NewsletterIssue
{
    return match ($editPage) {
        EditPost::class => Post::factory()
            ->draft()
            ->credited()
            ->create($attributes),
        EditGuide::class => Guide::factory()
            ->draft()
            ->credited()
            ->create($attributes),
        EditEpisode::class => Episode::factory()
            ->draft()
            ->create(['transistor_url' => 'https://share.transistor.fm/s/428d650c', ...$attributes]),
        EditNewsletterIssue::class => NewsletterIssue::factory()
            ->draft()
            ->create($attributes),
        default => throw new InvalidArgumentException("No ready draft for {$editPage}."),
    };
}

it('schedules content for a future publish date entered but not yet saved', function (string $editPage): void {
    freezeSecond();
    $record = readyDraftFor($editPage);
    $publishAt = now()->addDays(3);

    livewire($editPage, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => $publishAt->copy()
            ->setTimezone(DisplayTimezone::name())
            ->format('Y-m-d H:i:s')])
        ->callAction('publish')
        ->assertHasNoFormErrors()
        ->assertSchemaStateSet(['status' => PublishStatus::Scheduled->value]);

    $record->refresh();
    $publishedAt = $record->publishedAt();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Scheduled)
        ->and($publishedAt?->equalTo($publishAt))
        ->toBeTrue();
})->with('publishable edit pages');

it('publishes content immediately when the unsaved publish date is past or empty', function (string $editPage, ?int $daysAgo): void {
    freezeSecond();
    $record = readyDraftFor($editPage, ['published_at' => now()->addWeek()]);
    $expectedPublishedAt = $daysAgo === null
        ? now()
        : now()->subDays($daysAgo);

    livewire($editPage, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => $daysAgo === null
            ? null
            : $expectedPublishedAt->copy()
                ->setTimezone(DisplayTimezone::name())
                ->format('Y-m-d H:i:s')])
        ->callAction('publish')
        ->assertHasNoFormErrors()
        ->assertSchemaStateSet(['status' => PublishStatus::Published->value]);

    $record->refresh();
    $publishedAt = $record->publishedAt();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Published)
        ->and($publishedAt?->equalTo($expectedPublishedAt))
        ->toBeTrue();
})
    ->with('publishable edit pages')
    ->with([
        'past date' => [2],
        'empty date' => [null],
    ]);

it('checks readiness against details entered but not yet saved', function (): void {
    $record = Post::factory()
        ->draft()
        ->credited()
        ->create(['excerpt' => null]);

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->fillForm(['excerpt' => 'A summary typed before publishing.'])
        ->callAction('publish')
        ->assertNotified('Post published');

    $record->refresh();

    expect($record->isPublished())
        ->toBeTrue()
        ->and($record->excerpt)
        ->toBe('A summary typed before publishing.');
});

it('publishes nothing when the form has validation errors', function (string $editPage): void {
    $record = readyDraftFor($editPage);
    $title = $record->getAttribute('title');

    livewire($editPage, ['record' => $record->getRouteKey()])
        ->fillForm(['title' => ''])
        ->callAction('publish')
        ->assertHasErrors(['data.title' => 'required']);

    $record->refresh();

    expect($record->getAttribute('status'))
        ->toBe(PublishStatus::Draft)
        ->and($record->getAttribute('title'))
        ->toBe($title);
})->with('publishable edit pages');
