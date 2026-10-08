<?php

use App\Models\Podcast;
use App\Presenters\PodcastPresenter;
use Illuminate\Support\Facades\Storage;

covers(PodcastPresenter::class);

test('the cover is the uploaded image, with or without a leading slash', function (string $path): void {
    $presenter = PodcastPresenter::from(new Podcast(['cover_image_path' => $path]));

    expect($presenter->coverImageUrl())->toBe('/storage/podcast/cover.png')
        ->and($presenter->shareImageUrl())
        ->toBe('/storage/podcast/cover.png');
})->with([
    'stored path' => ['podcast/cover.png'],
    'leading slash' => ['/podcast/cover.png'],
]);

test('a show without an uploaded cover uses the bundled artwork', function (): void {
    $presenter = PodcastPresenter::from(new Podcast);

    expect($presenter->coverImageUrl())->toBe('/images/podcast/mouse28-cover.webp')
        ->and($presenter->shareImageUrl())
        ->toBe('/images/podcast/mouse28-cover.jpg')
        ->and($presenter->coverSrcset())
        ->toBe('/images/podcast/mouse28-cover-640.webp 640w, /images/podcast/mouse28-cover-768.webp 768w, /images/podcast/mouse28-cover.webp 1200w');
});

test('the uploaded cover lists the variants that exist', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('podcast/responsive/cover-640.webp', 'variant');
    $presenter = PodcastPresenter::from(new Podcast(['cover_image_path' => 'podcast/cover.png']));

    expect($presenter->coverSrcset())->toBe("{$disk->url('podcast/responsive/cover-640.webp')} 640w");
});

test('the uploaded cover has no srcset until variants exist', function (): void {
    Storage::fake('public');

    expect(PodcastPresenter::from(new Podcast(['cover_image_path' => 'podcast/cover.png']))->coverSrcset())->toBeNull();
});
