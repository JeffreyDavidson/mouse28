<?php

use App\Filament\Forms\Components\PublishDatePicker;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Filament\Resources\NewsletterIssues\Pages\EditNewsletterIssue;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

covers(PublishDatePicker::class);

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAs(User::factory()
    ->admin()
    ->create()));

dataset('publish date edit pages', [
    'post' => [fn (): Model => Post::factory()
        ->credited()
        ->draft()
        ->create(), EditPost::class],
    'guide' => [fn (): Model => Guide::factory()
        ->credited()
        ->draft()
        ->create(), EditGuide::class],
    'episode' => [fn (): Model => Episode::factory()
        ->draft()
        ->create(), EditEpisode::class],
    'newsletter issue' => [fn (): Model => NewsletterIssue::factory()
        ->draft()
        ->create(), EditNewsletterIssue::class],
]);

dataset('eastern publish dates', [
    'daylight saving time' => ['2026-10-05 21:00:00', '2026-10-06 01:00:00'],
    'standard time' => ['2027-01-15 21:00:00', '2027-01-16 02:00:00'],
]);

test('a publish date entered in Eastern time is stored in UTC', function (Model $record, string $page, string $entered, string $stored): void {
    livewire($page, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => $entered])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas($record->getTable(), [
        'id' => $record->getKey(),
        'published_at' => $stored,
    ]);
})->with('publish date edit pages')
    ->with('eastern publish dates');

test('a stored UTC publish date is shown in Eastern time with a timezone hint', function (Model $record, string $page): void {
    $record->forceFill(['published_at' => '2026-10-06 01:00:00'])
        ->save();

    livewire($page, ['record' => $record->getRouteKey()])
        ->assertSet('data.published_at', '2026-10-05 21:00:00')
        ->assertSee('Eastern Time (America/New_York)');
})->with('publish date edit pages');
