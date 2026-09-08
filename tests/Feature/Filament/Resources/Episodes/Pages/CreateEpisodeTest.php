<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Episodes\Pages\CreateEpisode;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('episode creation validates numeric and URL storage constraints', function (string $field, mixed $value, string $rule): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    $page = livewire(CreateEpisode::class);

    // Act
    $page->fillForm(['title' => 'New episode', 'slug' => 'new-episode', 'episode_number' => 42, $field => $value]);
    $page->call('create');

    // Assert
    $page->assertHasFormErrors([$field => $rule]);
    expect(Episode::query()->count())->toBe(0);
})->with([
    'fractional episode' => ['episode_number', 1.5, 'integer'],
    'negative episode' => ['episode_number', -1, 'min'],
    'overflow episode' => ['episode_number', 2147483648, 'max'],
    'fractional season' => ['season_number', 1.5, 'integer'],
    'negative season' => ['season_number', -1, 'min'],
    'overflow season' => ['season_number', 4294967296, 'max'],
    'fractional duration' => ['duration_seconds', 1.5, 'integer'],
    'negative duration' => ['duration_seconds', -1, 'min'],
    'overflow duration' => ['duration_seconds', 2147483648, 'max'],
    'long Transistor URL' => ['transistor_url', 'https://share.transistor.fm/s/'.str_repeat('a', 256), 'max'],
    'long Apple URL' => ['apple_url', 'https://example.com/'.str_repeat('a', 256), 'max'],
    'long Spotify URL' => ['spotify_url', 'https://example.com/'.str_repeat('a', 256), 'max'],
    'long YouTube URL' => ['youtube_url', 'https://example.com/'.str_repeat('a', 256), 'max'],
]);

test('episode numbers remain reserved by existing and deleted episodes', function (bool $deleted): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    $episode = Episode::factory()->create(['episode_number' => 42]);
    if ($deleted) {
        $episode->delete();
    }
    $page = livewire(CreateEpisode::class);

    // Act
    $page->fillForm(['title' => 'Duplicate', 'slug' => 'duplicate', 'episode_number' => 42]);
    $page->call('create');

    // Assert
    $page->assertHasFormErrors(['episode_number' => 'unique']);
    expect(Episode::withTrashed()->count())->toBe(1);
})->with([false, true]);

test('a trailer can use episode zero with optional season and duration', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    $page = livewire(CreateEpisode::class);

    // Act
    $page->fillForm(['title' => 'Trailer', 'slug' => 'trailer', 'episode_number' => 0, 'season_number' => null, 'duration_seconds' => null]);
    $page->call('create');

    // Assert
    $page->assertHasNoFormErrors();
    $episode = Episode::query()->sole();
    expect($episode->episode_number)->toBe(0)
        ->and($episode->season_number)->toBeNull()
        ->and($episode->duration_seconds)->toBeNull();
});

test('episode storage boundaries are accepted', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    $url = 'https://example.com/'.str_repeat('a', 255 - strlen('https://example.com/'));
    $transistorUrl = 'https://share.transistor.fm/s/'.str_repeat('a', 255 - strlen('https://share.transistor.fm/s/'));
    $page = livewire(CreateEpisode::class);

    // Act
    $page->fillForm([
        'title' => 'Boundary episode',
        'slug' => 'boundary-episode',
        'episode_number' => 2147483647,
        'season_number' => 4294967295,
        'duration_seconds' => 2147483647,
        'transistor_url' => $transistorUrl,
        'apple_url' => $url,
        'spotify_url' => $url,
        'youtube_url' => $url,
    ]);
    $page->call('create');

    // Assert
    $page->assertHasNoFormErrors();
    $episode = Episode::query()->sole();
    expect($episode->episode_number)->toBe(2147483647)
        ->and($episode->season_number)->toBe(4294967295)
        ->and($episode->duration_seconds)->toBe(2147483647)
        ->and($episode->transistor_url)->toBe($transistorUrl)
        ->and($episode->apple_url)->toBe($url)
        ->and($episode->spotify_url)->toBe($url)
        ->and($episode->youtube_url)->toBe($url);
});

test('authenticated user can render the create form', function (): void {
    actingAs(User::factory()->admin()->create());

    get(EpisodeResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Episode');
});

test('create form explains editorial requirements', function (): void {
    actingAs(User::factory()->admin()->create());

    get(EpisodeResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Transistor Episode URL')
        ->assertSee('share.transistor.fm/s/')
        ->assertDontSee('Hosted MP3')
        ->assertDontSee('External Audio URL')
        ->assertSee('Landscape image (1.91:1)');
});
