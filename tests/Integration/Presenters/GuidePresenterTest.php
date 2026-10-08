<?php

use App\Enums\GuideCategory;
use App\Models\Guide;
use App\Presenters\GuidePresenter;
use Illuminate\Support\Facades\Storage;

covers(GuidePresenter::class);

test('the artwork is the uploaded cover', function (): void {
    Storage::fake('public');
    $guide = new Guide(['category' => GuideCategory::FoodReviews, 'featured_image_path' => 'guides/cover.png']);

    expect(GuidePresenter::from($guide)->artworkUrl())->toBe(Storage::disk('public')->url('guides/cover.png'));
});

test('a guide without a cover uses its category artwork', function (): void {
    $guide = new Guide(['category' => GuideCategory::FoodReviews]);

    expect(GuidePresenter::from($guide)->artworkUrl())->toBe('/images/guides/food-reviews.webp');
});

test('the uploaded cover lists the variants that exist', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('guides/responsive/cover-480.webp', 'variant');
    $guide = new Guide(['category' => GuideCategory::FoodReviews, 'featured_image_path' => 'guides/cover.png']);

    expect(GuidePresenter::from($guide)->artworkSrcset())->toBe("{$disk->url('guides/responsive/cover-480.webp')} 480w");
});

test('the category artwork has no srcset', function (): void {
    Storage::fake('public');
    $guide = new Guide(['category' => GuideCategory::FoodReviews]);

    expect(GuidePresenter::from($guide)->artworkSrcset())->toBeNull();
});
