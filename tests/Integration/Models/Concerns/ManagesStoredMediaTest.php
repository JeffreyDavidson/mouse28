<?php

use App\Models\Concerns\ManagesStoredMedia;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

covers(ManagesStoredMedia::class);

beforeEach(function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('covers/old.webp', 'old');
    Storage::disk('public')->put('covers/new.webp', 'new');
});

dataset('stored media models', [
    'posts' => [fn () => Post::factory()],
    'episodes' => [fn () => Episode::factory()],
    'guides' => [fn () => Guide::factory()],
]);

test('replacing the image deletes the previous original after commit', function (PostFactory|EpisodeFactory|GuideFactory $factory): void {
    $record = $factory->createOne(['featured_image_path' => 'covers/old.webp']);

    DB::transaction(fn () => $record->update(['featured_image_path' => 'covers/new.webp']));

    Storage::disk('public')->assertMissing('covers/old.webp');
    Storage::disk('public')->assertExists('covers/new.webp');
})->with('stored media models');

test('a rolled back replacement keeps the previous original', function (PostFactory|EpisodeFactory|GuideFactory $factory): void {
    $record = $factory->createOne(['featured_image_path' => 'covers/old.webp']);

    DB::beginTransaction();
    $record->update(['featured_image_path' => 'covers/new.webp']);
    DB::rollBack();

    Storage::disk('public')->assertExists(['covers/old.webp', 'covers/new.webp']);
})->with('stored media models');

test('changing other attributes keeps the original', function (PostFactory|EpisodeFactory|GuideFactory $factory): void {
    $record = $factory->createOne(['featured_image_path' => 'covers/old.webp']);

    $record->update(['title' => 'Renamed record']);

    Storage::disk('public')->assertExists('covers/old.webp');
})->with('stored media models');

test('soft deleting keeps the original and force deleting removes it', function (PostFactory|EpisodeFactory|GuideFactory $factory): void {
    $record = $factory->createOne(['featured_image_path' => 'covers/old.webp']);

    $record->delete();

    Storage::disk('public')->assertExists('covers/old.webp');

    $record->forceDelete();

    Storage::disk('public')->assertMissing('covers/old.webp');
})->with('stored media models');

test('it lists only filled stored media paths', function (?string $path, array $expected): void {
    expect(new Post(['featured_image_path' => $path])->storedMediaPaths())->toBe($expected);
})->with([
    'a stored image' => ['covers/old.webp', ['covers/old.webp']],
    'an empty path' => ['', []],
    'no path' => [null, []],
]);
