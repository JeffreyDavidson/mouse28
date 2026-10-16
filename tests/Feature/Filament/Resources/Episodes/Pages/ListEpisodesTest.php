<?php

use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Contracts\PublishingSuggestions;
use JeffreyDavidson\CreatorKit\Enums\PublishStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\EpisodeResource;
use JeffreyDavidson\CreatorKit\Filament\Resources\Episodes\Pages\ListEpisodes;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the resource listing', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    get(EpisodeResource::getUrl())
        ->assertOk()
        ->assertSee('Episodes');
});

test('content table shows readiness and the persisted publish status', function (): void {
    $admin = User::factory()
        ->admin()
        ->create();
    Episode::factory()->create(['status' => PublishStatus::InReview]);

    actingAs($admin);

    get(EpisodeResource::getUrl())
        ->assertOk()
        ->assertSee('Readiness')
        ->assertSee('In Review');
});

test('the status filter narrows episodes to one status', function (): void {
    // Arrange
    $draft = Episode::factory()
        ->draft()
        ->create();
    $inReview = Episode::factory()->create(['status' => PublishStatus::InReview]);
    actingAs(User::factory()
        ->admin()
        ->create());

    // Act
    $page = livewire(ListEpisodes::class)->filterTable('status', PublishStatus::InReview->value);

    // Assert
    $page->assertCanSeeTableRecords([$inReview])
        ->assertCanNotSeeTableRecords([$draft]);
});

test('the publication filter keeps scheduled episodes out of live ones', function (): void {
    // Arrange
    $live = Episode::factory()->create();
    $scheduled = Episode::factory()
        ->scheduled()
        ->create();
    $draft = Episode::factory()
        ->draft()
        ->create();
    actingAs(User::factory()
        ->admin()
        ->create());

    // Act
    $livePage = livewire(ListEpisodes::class)->filterTable('publication', 'published');
    $scheduledPage = livewire(ListEpisodes::class)->filterTable('publication', 'scheduled');

    // Assert
    $livePage->assertCanSeeTableRecords([$live])
        ->assertCanNotSeeTableRecords([$scheduled, $draft]);
    $scheduledPage->assertCanSeeTableRecords([$scheduled])
        ->assertCanNotSeeTableRecords([$live, $draft]);
});

test('an episode that can be published reads Ready with what it could still improve', function (): void {
    // Arrange
    $episode = Episode::factory()->create(['featured_image_path' => null, 'transistor_url' => 'https://share.transistor.fm/s/428d650c']);
    actingAs(User::factory()
        ->admin()
        ->create());

    // Act
    $response = get(EpisodeResource::getUrl());

    // Assert
    $response->assertOk()
        ->assertSee('Ready')
        ->assertSee('Could improve: '.implode(', ', app(PublishingSuggestions::class)->suggestions($episode)));
    expect($episode->publishingIssues())->toBeEmpty()
        ->and(app(PublishingSuggestions::class)->suggestions($episode))
        ->toContain('Add a cover image');
});
