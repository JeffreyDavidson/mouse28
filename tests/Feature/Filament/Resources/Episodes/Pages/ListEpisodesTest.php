<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

test('draft and scheduled tabs filter episodes', function (): void {
    $draft = Episode::factory()
        ->draft()
        ->create();
    $inReview = Episode::factory()->create(['status' => PublishStatus::InReview]);
    $scheduled = Episode::factory()
        ->scheduled()
        ->create();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(ListEpisodes::class)
        ->set('activeTab', 'drafts')
        ->assertCanSeeTableRecords([$draft, $inReview])
        ->assertCanNotSeeTableRecords([$scheduled])
        ->set('activeTab', 'scheduled')
        ->assertCanSeeTableRecords([$scheduled])
        ->assertCanNotSeeTableRecords([$draft]);
});

test('header does not count scheduled episodes as published', function (): void {
    // Arrange
    Episode::factory()->create();
    Episode::factory()
        ->scheduled()
        ->create();
    Episode::factory()
        ->draft()
        ->create();
    Episode::factory()->create(['status' => PublishStatus::InReview]);
    actingAs(User::factory()
        ->admin()
        ->create());

    // Act
    $page = livewire(ListEpisodes::class);
    $component = $page->instance();
    $header = $component->getHeader();

    // Assert
    expect($header?->getData())
        ->toMatchArray([
            'published' => 1,
            'drafts' => 2,
        ]);
});
