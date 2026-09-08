<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

test('editing an episode retains its number without a uniqueness error', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    $episode = Episode::factory()->draft()->create(['episode_number' => 42]);
    $page = livewire(EditEpisode::class, ['record' => $episode->getRouteKey()]);

    // Act
    $page->fillForm(['title' => 'Updated title']);
    $page->call('save');

    // Assert
    $page->assertHasNoFormErrors();
    expect($episode->refresh()->title)->toBe('Updated title')
        ->and($episode->episode_number)->toBe(42);
});

test('editing cannot take another episode number', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    Episode::factory()->create(['episode_number' => 42]);
    $episode = Episode::factory()->draft()->create(['episode_number' => 43]);
    $page = livewire(EditEpisode::class, ['record' => $episode->getRouteKey()]);

    // Act
    $page->fillForm(['episode_number' => 42]);
    $page->call('save');

    // Assert
    $page->assertHasFormErrors(['episode_number' => 'unique']);
    expect($episode->refresh()->episode_number)->toBe(43);
});

test('edit page offers a draft preview', function (): void {
    $admin = User::factory()->admin()->create();
    $episode = Episode::factory()->draft()->create();

    actingAs($admin);

    get(EpisodeResource::getUrl('edit', ['record' => $episode]))
        ->assertOk()
        ->assertSee('Preview');
});

test('ready drafts can be explicitly published and unpublished', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Episode::factory()->draft()->create([
        'cover_image' => 'episodes/complete.jpg',
        'meta_title' => 'Complete episode title',
        'meta_description' => 'Complete episode description',
    ]);

    actingAs($admin);

    Livewire::test(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeTrue()
        ->and($record->published_at)->not->toBeNull();

    Livewire::test(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeFalse();
});

test('deleted content leaves the public site and can be restored by an administrator', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Episode::factory()->create();

    $record->delete();

    get(route('episodes.show', $record))->assertNotFound();

    actingAs($admin);

    Livewire::test(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('restore')
        ->assertNotified();

    expect($record->refresh()->deleted_at)->toBeNull();
    get(route('episodes.show', $record))->assertOk();
});
