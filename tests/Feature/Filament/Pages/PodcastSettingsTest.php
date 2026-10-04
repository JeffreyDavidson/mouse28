<?php

use App\Filament\Pages\PodcastSettings;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('podcast links enforce their storage length without changing saved settings', function (string $field): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    $podcast = Podcast::settings();
    $original = $podcast->getAttribute($field);
    $page = livewire(PodcastSettings::class);

    // Act
    $page->fillForm([$field => 'https://example.com/'.str_repeat('a', 256)]);
    $page->call('save');

    // Assert
    $page->assertHasFormErrors([$field => 'max']);
    expect($podcast->refresh()->getAttribute($field))->toBe($original);
})->with(['apple_url', 'spotify_url', 'youtube_url']);

test('podcast links accept the full supported storage length', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    $url = 'https://example.com/'.str_repeat('a', 255 - strlen('https://example.com/'));
    $links = array_fill_keys(['apple_url', 'spotify_url', 'youtube_url'], $url);
    $page = livewire(PodcastSettings::class);

    // Act
    $page->fillForm($links);
    $page->call('save');

    // Assert
    $page->assertHasNoFormErrors();
    expect(Podcast::settings()->only(array_keys($links)))->toBe($links);
});

test('authenticated user can render podcast settings', function (): void {
    actingAs(User::factory()->admin()->create());

    get(PodcastSettings::getUrl())
        ->assertOk()
        ->assertSee('Podcast Settings')
        ->assertDontSeeHtml('fi-header-heading')
        ->assertSee('Distribution Links');
});

test('podcast cover uploads enforce the five megabyte limit', function (int $size, bool $valid): void {
    Storage::fake('public');
    actingAs(User::factory()->admin()->create());
    $cover = UploadedFile::fake()->image('cover.jpg')->size($size);

    $page = livewire(PodcastSettings::class)
        ->fillForm(['cover_image_path' => $cover])
        ->call('save');

    if ($valid) {
        $page->assertHasNoFormErrors();

        return;
    }

    $page->assertHasFormErrors(['cover_image_path']);
})->with([
    'at the limit' => [5120, true],
    'over the limit' => [5121, false],
]);

test('podcast settings cannot be saved once admin access is revoked', function (): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);
    $podcast = Podcast::settings();
    $page = livewire(PodcastSettings::class)->fillForm(['name' => 'Changed name']);
    $admin->is_admin = false;

    expect(fn () => $page->instance()->save())->toThrow(AuthorizationException::class)
        ->and($podcast->refresh()->name)->not->toBe('Changed name');
});

test('a replaced podcast cover is stored under podcast with variants and the previous file is removed', function (): void {
    Storage::fake('public');
    actingAs(User::factory()->admin()->create());
    Storage::disk('public')->put('podcast/previous.png', UploadedFile::fake()->image('previous.png', 600, 600)->getContent());
    Podcast::settings()->update(['cover_image_path' => 'podcast/previous.png']);

    livewire(PodcastSettings::class)
        ->fillForm(['cover_image_path' => [UploadedFile::fake()->image('cover.png', 600, 600)]])
        ->call('save')
        ->assertHasNoFormErrors();

    $path = Podcast::settings()->cover_image_path;
    expect($path)->toStartWith('podcast/')
        ->not->toBe('podcast/previous.png');
    Storage::disk('public')->assertExists([$path, 'podcast/responsive/'.pathinfo((string) $path, PATHINFO_FILENAME).'-480.webp']);
    Storage::disk('public')->assertMissing(['podcast/previous.png', 'podcast/responsive/previous-480.webp']);
});
