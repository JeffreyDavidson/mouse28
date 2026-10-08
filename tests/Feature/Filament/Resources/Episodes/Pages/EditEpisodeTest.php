<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Episodes\Pages\EditEpisode;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('previously published URLs stay locked after unpublishing and clearing the date', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Episode::factory()->create(['slug' => 'original-public-url']);

    $record->update(['status' => PublishStatus::Draft]);

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'published_at' => null,
            'slug' => 'replacement-url',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()
        ->slug)->toBe('original-public-url');
});

test('authenticated user can render the edit form', function (): void {
    $episode = Episode::factory()
        ->draft()
        ->create();

    actingAs(User::factory()
        ->admin()
        ->create());

    get(EpisodeResource::getUrl('edit', ['record' => $episode]))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSee('Save changes');
});

test('a replaced cover is stored under episodes with variants and the previous file is removed', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('episodes/previous.png', UploadedFile::fake()
        ->image('previous.png', 1000, 525)
        ->getContent());
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Episode::factory()->create(['featured_image_path' => 'episodes/previous.png']);

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->fillForm(['featured_image_path' => [UploadedFile::fake()->image('cover.png', 1000, 525)]])
        ->call('save')
        ->assertHasNoFormErrors();

    $path = (string) $record->refresh()
        ->featured_image_path;
    expect($path)->toStartWith('episodes/')
        ->not->toBe('episodes/previous.png');
    Storage::disk('public')->assertExists([$path, 'episodes/responsive/'.pathinfo($path, PATHINFO_FILENAME).'-480.webp']);
    Storage::disk('public')->assertMissing(['episodes/previous.png', 'episodes/responsive/previous-480.webp']);
});

test('the edit page offers no manual artwork generation action', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Episode::factory()->create(['featured_image_path' => 'episodes/cover.png']);

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->assertActionDoesNotExist('generateArtwork');
});

test('published URLs cannot be changed by submitted editor state', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $published = Episode::factory()->create(['slug' => 'permanent-url']);

    livewire(EditEpisode::class, ['record' => $published->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($published->refresh()
        ->slug)->toBe('permanent-url');

});

test('draft slugs reject characters that cannot form public routes', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $draft = Episode::factory()
        ->draft()
        ->create();

    livewire(EditEpisode::class, ['record' => $draft->getRouteKey()])
        ->fillForm(['slug' => 'Invalid/URL'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);
});

test('editing an episode retains its number without a uniqueness error', function (): void {
    // Arrange
    actingAs(User::factory()
        ->admin()
        ->create());
    $episode = Episode::factory()
        ->draft()
        ->create(['episode_number' => 42]);
    $page = livewire(EditEpisode::class, ['record' => $episode->getRouteKey()]);

    // Act
    $page->fillForm(['title' => 'Updated title']);
    $page->call('save');

    // Assert
    $page->assertHasNoFormErrors();
    expect($episode->refresh()
        ->title)->toBe('Updated title')
        ->and($episode->episode_number)
        ->toBe(42);
});

test('editing cannot take another episode number', function (): void {
    // Arrange
    actingAs(User::factory()
        ->admin()
        ->create());
    Episode::factory()->create(['episode_number' => 42]);
    $episode = Episode::factory()
        ->draft()
        ->create(['episode_number' => 43]);
    $page = livewire(EditEpisode::class, ['record' => $episode->getRouteKey()]);

    // Act
    $page->fillForm(['episode_number' => 42]);
    $page->call('save');

    // Assert
    $page->assertHasFormErrors(['episode_number' => 'unique']);
    expect($episode->refresh()
        ->episode_number)->toBe(43);
});

test('edit page offers a draft preview', function (): void {
    Date::setTestNow('2026-09-27 12:00:00');
    $admin = User::factory()
        ->admin()
        ->create();
    $episode = Episode::factory()
        ->draft()
        ->create();

    actingAs($admin);

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', URL::temporarySignedRoute('preview.episode', Date::now()->addHours(24), ['episode' => $episode]))
        ->assertActionShouldOpenUrlInNewTab('preview');
});

test('drafts with their required details can be published while advisory details are missing', function (): void {
    $admin = User::factory()
        ->admin()
        ->create();
    $record = Episode::factory()
        ->draft()
        ->create([
            'transistor_url' => 'https://share.transistor.fm/s/428d650c',
            'featured_image_path' => null,
        ]);

    actingAs($admin);

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Episode published');

    expect($record->refresh()
        ->status)->toBe(PublishStatus::Published)
        ->and($record->published_at)
        ->not->toBeNull();

});

test('published content can be explicitly unpublished', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Episode::factory()->create();

    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Episode unpublished');

    expect($record->refresh()
        ->status)->toBe(PublishStatus::Draft);
});

test('deleted content leaves the public site and can be restored by an administrator', function (): void {
    // Arrange
    $admin = User::factory()
        ->admin()
        ->create();
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

test('publishing actions disappear when admin access is revoked for the episode', function (bool $isDraft, string $action): void {
    $admin = User::factory()
        ->admin()
        ->create();
    actingAs($admin);
    $record = $isDraft ? Episode::factory()
        ->draft()
        ->create() : Episode::factory()->create();
    $page = livewire(EditEpisode::class, ['record' => $record->getRouteKey()]);

    $admin->is_admin = false;

    $page->assertActionHidden($action);
})->with([
    'publish' => [true, 'publish'],
    'unpublish' => [false, 'unpublish'],
]);

test('episodes cannot be published without a Transistor episode URL', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $episode = Episode::factory()
        ->draft()
        ->create(['transistor_url' => null]);

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Episode is not ready to publish');

    expect($episode->refresh()
        ->status)->toBe(PublishStatus::Draft);
});

test('the SEO section saves its title and description to the SEO row', function (): void {
    // Arrange
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Episode::factory()->create();

    // Act
    livewire(EditEpisode::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'seo.title' => 'A saved SEO title',
            'seo.description' => 'A saved SEO description.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Assert
    expect($record->refresh()
        ->seo->title)->toBe('A saved SEO title')
        ->and($record->seo->description)
        ->toBe('A saved SEO description.');
});

test('an episode guest is saved from the edit form', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $episode = Episode::factory()
        ->draft()
        ->create();

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['guest_name' => 'Guest Name', 'guest_title' => 'Guest title', 'guest_url' => 'https://example.test/guest'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($episode->refresh())
        ->guest_name->toBe('Guest Name')
        ->guest_title->toBe('Guest title')
        ->guest_url->toBe('https://example.test/guest');
});

test('an episode guest link must be a web address', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $episode = Episode::factory()
        ->draft()
        ->create();

    livewire(EditEpisode::class, ['record' => $episode->getRouteKey()])
        ->fillForm(['guest_url' => 'not a link'])
        ->call('save')
        ->assertHasFormErrors(['guest_url' => 'url']);

    expect($episode->refresh()
        ->guest_url)->toBeNull();
});
