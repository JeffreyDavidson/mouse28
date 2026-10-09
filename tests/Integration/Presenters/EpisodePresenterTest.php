<?php

use App\Models\Episode;
use App\Models\Podcast;
use App\Presenters\EpisodePresenter;
use App\Presenters\PodcastPresenter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

covers(EpisodePresenter::class);

test('the episode cover is its own artwork', function (): void {
    Storage::fake('public');
    $episode = new Episode(['featured_image_path' => 'episodes/cover.png']);

    expect(EpisodePresenter::from($episode)->coverImageUrl(PodcastPresenter::from(new Podcast)))
        ->toBe(Storage::disk('public')->url('episodes/cover.png'));
});

test('an episode without artwork uses the show cover', function (): void {
    $podcast = new Podcast(['cover_image_path' => 'podcast/cover.png']);

    expect(EpisodePresenter::from(new Episode)->coverImageUrl(PodcastPresenter::from($podcast)))
        ->toBe('/storage/podcast/cover.png');
});

test('the cover lists the square variants that exist', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('episodes/cover.png', UploadedFile::fake()
        ->image('cover.png', 700, 700)
        ->getContent());
    $disk->put('episodes/responsive/cover-480.webp', 'variant');
    $episode = new Episode(['featured_image_path' => 'episodes/cover.png']);

    expect(EpisodePresenter::from($episode)->coverSrcset())->toBe("{$disk->url('episodes/responsive/cover-480.webp')} 480w");
});

test('the cover has no srcset without artwork or variants', function (?string $path): void {
    Storage::fake('public');

    expect(EpisodePresenter::from(new Episode(['featured_image_path' => $path]))->coverSrcset())->toBeNull();
})->with([
    'no artwork' => [null],
    'no variants' => ['episodes/cover.png'],
]);
