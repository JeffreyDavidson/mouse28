<?php

use App\Filament\Pages\PodcastSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

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
