<?php

use App\Filament\Tables\Filters\MissingSeoFilter;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(MissingSeoFilter::class);

dataset('models with SEO', [
    'posts' => [fn () => Post::factory()],
    'guides' => [fn () => Guide::factory()],
    'episodes' => [fn () => Episode::factory()],
]);

test('the missing SEO filter is named missing_seo', function (): void {
    expect(MissingSeoFilter::make()->getName())->toBe('missing_seo');
});

test('the missing SEO filter lists content lacking a saved SEO title or description', function (PostFactory|GuideFactory|EpisodeFactory $factory): void {
    // Arrange
    $complete = $factory->withSeo()
        ->createOne();
    $incomplete = $factory->createOne();
    $incomplete->seo->update(['title' => 'A title', 'description' => '']);

    // Act
    $ids = MissingSeoFilter::make()
        ->apply($incomplete::query(), ['isActive' => true])
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->toBe([$incomplete->id])
        ->and($ids)
        ->not->toContain($complete->id);
})->with('models with SEO');
