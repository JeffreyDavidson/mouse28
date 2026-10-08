<?php

use App\Models\Concerns\ScopesMissingSeo;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(ScopesMissingSeo::class);

dataset('models with SEO', [
    'posts' => [fn () => Post::factory()],
    'episodes' => [fn () => Episode::factory()],
    'guides' => [fn () => Guide::factory()],
]);

dataset('SEO values', [
    'title and description saved' => ['A title', 'A description', false],
    'no title' => [null, 'A description', true],
    'empty title' => ['', 'A description', true],
    'no description' => ['A title', null, true],
    'empty description' => ['A title', '', true],
    'nothing saved' => [null, null, true],
]);

test('the missing SEO scope finds records lacking a saved title or description', function (PostFactory|EpisodeFactory|GuideFactory $factory, ?string $title, ?string $description, bool $listed): void {
    // Arrange
    $record = $factory->createOne();
    $record->seo->update(['title' => $title, 'description' => $description]);

    // Act
    $ids = $record::query()->missingSeo()
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->toBe($listed ? [$record->id] : []);
})->with('models with SEO')
    ->with('SEO values');

test('the missing SEO scope reads each record\'s own SEO row', function (PostFactory|EpisodeFactory|GuideFactory $factory): void {
    // Arrange
    $complete = $factory->withSeo()
        ->createOne();
    $incomplete = $factory->createOne();

    // Act
    $ids = $incomplete::query()->missingSeo()
        ->pluck('id')
        ->all();

    // Assert
    expect($ids)->toBe([$incomplete->id])
        ->and($ids)
        ->not->toContain($complete->id);
})->with('models with SEO');
