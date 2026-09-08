<?php

use App\Filament\Pages\PodcastSettings;
use App\Models\Podcast;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

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
})->with(['apple_url', 'spotify_url', 'youtube_url', 'instagram_url', 'tiktok_url']);

test('podcast links accept the full supported storage length', function (): void {
    // Arrange
    actingAs(User::factory()->admin()->create());
    $url = 'https://example.com/'.str_repeat('a', 255 - strlen('https://example.com/'));
    $links = array_fill_keys(['apple_url', 'spotify_url', 'youtube_url', 'instagram_url', 'tiktok_url'], $url);
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
        ->assertSee('Distribution Links');
});

test('podcast cover uploads enforce the five megabyte limit', function (int $size, bool $valid): void {
    Storage::fake('public');
    actingAs(User::factory()->admin()->create());
    $cover = UploadedFile::fake()->image('cover.jpg')->size($size);

    $page = livewire(PodcastSettings::class)
        ->fillForm(['cover_image' => $cover])
        ->call('save');

    if ($valid) {
        $page->assertHasNoFormErrors();

        return;
    }

    $page->assertHasFormErrors(['cover_image']);
})->with([
    'at the limit' => [5120, true],
    'over the limit' => [5121, false],
]);
