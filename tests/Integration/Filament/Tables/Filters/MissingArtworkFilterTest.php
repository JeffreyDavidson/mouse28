<?php

use App\Filament\Tables\Filters\MissingArtworkFilter;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(MissingArtworkFilter::class);

dataset('models with artwork', [
    'posts' => [fn () => Post::factory()],
    'guides' => [fn () => Guide::factory()],
    'episodes' => [fn () => Episode::factory()],
]);

test('the missing artwork filter is named missing_artwork', function (): void {
    expect(MissingArtworkFilter::make()->getName())->toBe('missing_artwork');
});

test('the missing artwork filter lists content without a featured image path', function (PostFactory|GuideFactory|EpisodeFactory $factory): void {
    // Arrange
    $withImage = $factory->createOne(['featured_image_path' => 'artwork/cover.jpg']);
    $withoutImage = $factory->createOne(['featured_image_path' => null]);
    $emptyImage = $factory->createOne(['featured_image_path' => '']);

    // Act
    $ids = MissingArtworkFilter::make()
        ->apply($withImage::query(), ['isActive' => true])
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->toEqualCanonicalizing([$withoutImage->id, $emptyImage->id])
        ->and($ids)
        ->not->toContain($withImage->id);
})->with('models with artwork');
