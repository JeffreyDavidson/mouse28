<?php

use App\Models\Podcast;
use App\Observers\PodcastObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

covers(PodcastObserver::class);

beforeEach(function (): void {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('podcast.png', 1280, 1280)->getContent();
    Storage::disk('public')->put('podcast/old.png', $image);
    Storage::disk('public')->put('podcast/new.png', $image);
});

test('saving a podcast cover generates its variants', function (): void {
    Podcast::settings()->update(['cover_image_path' => 'podcast/old.png']);

    Storage::disk('public')->assertExists(['podcast/responsive/old-480.webp', 'podcast/responsive/old-1280.webp']);
});

test('replacing the podcast cover removes the previous original and variants after commit', function (): void {
    $podcast = Podcast::settings();
    $podcast->update(['cover_image_path' => 'podcast/old.png']);

    DB::transaction(fn () => $podcast->update(['cover_image_path' => 'podcast/new.png']));

    Storage::disk('public')->assertMissing(['podcast/old.png', 'podcast/responsive/old-480.webp']);
    Storage::disk('public')->assertExists(['podcast/new.png', 'podcast/responsive/new-480.webp']);
});

test('a rolled back podcast cover replacement keeps the previous files', function (): void {
    $podcast = Podcast::settings();
    $podcast->update(['cover_image_path' => 'podcast/old.png']);

    DB::beginTransaction();
    $podcast->update(['cover_image_path' => 'podcast/new.png']);
    DB::rollBack();

    Storage::disk('public')->assertExists(['podcast/old.png', 'podcast/responsive/old-480.webp']);
});

test('trashing the podcast keeps its cover so it can be restored', function (): void {
    $podcast = Podcast::settings();
    $podcast->update(['cover_image_path' => 'podcast/old.png']);

    $podcast->delete();

    Storage::disk('public')->assertExists(['podcast/old.png', 'podcast/responsive/old-480.webp']);
});

test('permanently deleting the podcast removes its cover and variants', function (): void {
    $podcast = Podcast::settings();
    $podcast->update(['cover_image_path' => 'podcast/old.png']);

    $podcast->forceDelete();

    Storage::disk('public')->assertMissing(['podcast/old.png', 'podcast/responsive/old-480.webp']);
});
