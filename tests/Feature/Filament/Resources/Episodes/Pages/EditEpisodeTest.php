<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\User;
use App\Support\ResponsiveArtwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the edit form', function (): void {
    $episode = Episode::factory()->draft()->create();

    actingAs(User::factory()->admin()->create());

    get(EpisodeResource::getUrl('edit', ['record' => $episode]))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSee('Save changes');
});

test('explicit artwork action generates only the saved record cover', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('episodes/cover.png', UploadedFile::fake()->image('cover.png', 1000, 800)->getContent());
    Storage::disk('public')->put('episodes/other.png', UploadedFile::fake()->image('other.png', 1200, 800)->getContent());
    actingAs(User::factory()->admin()->create());
    $record = Episode::factory()->create(['cover_image' => 'episodes/cover.png']);
    $other = Episode::factory()->create(['cover_image' => 'episodes/other.png']);

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('generateArtwork')
        ->assertNotified('Responsive artwork prepared');

    expect(ResponsiveArtwork::srcset($record->cover_image, square: true))->not->toBeNull()
        ->and(ResponsiveArtwork::srcset($other->cover_image, square: true))->toBeNull();
});

test('artwork generation reports an unavailable source', function (): void {
    Storage::fake('public');
    actingAs(User::factory()->admin()->create());
    $record = Episode::factory()->create(['cover_image' => 'episodes/missing.png']);

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('generateArtwork')
        ->assertNotified('Artwork generation failed');
});

test('published URLs are preserved and draft slugs are validated', function (): void {
    actingAs(User::factory()->admin()->create());
    $published = Episode::factory()->create(['slug' => 'permanent-url']);
    $draft = Episode::factory()->draft()->create();

    livewire(EditEpisode::class, ['record' => $published->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($published->refresh()->slug)->toBe('permanent-url');

    livewire(EditEpisode::class, ['record' => $draft->getRouteKey()])
        ->fillForm(['slug' => 'Invalid/URL'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);
});

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

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', route('preview.episodes', $episode))
        ->assertActionShouldOpenUrlInNewTab('preview');
});

test('ready drafts can be explicitly published and unpublished', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Episode::factory()->draft()->create([
        'cover_image' => 'episodes/complete.jpg',
        'meta_title' => 'Complete episode title',
        'meta_description' => 'Complete episode description',
    ]);

    actingAs($admin);

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeTrue()
        ->and($record->published_at)->not->toBeNull();

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeFalse();
});

test('deleted content leaves the public site and can be restored by an administrator', function (): void {
    // Arrange
    $admin = User::factory()->admin()->create();
    $record = Episode::factory()->create();

    $record->delete();

    get(route('episodes.show', $record))->assertNotFound();

    actingAs($admin);

    $page = livewire(EditEpisode::class, ['record' => $record->getRouteKey()]);

    // Act
    $page->callAction('restore');
    $response = get(route('episodes.show', $record));

    // Assert
    $page->assertNotified();
    $this->assertNotSoftDeleted($record);
    $response->assertOk();
});
