<?php

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(CategorySeeder::class);

pest()->use(RefreshDatabase::class);

test('the category seeder adds the standard blog categories', function (): void {
    Category::query()->delete();

    $this->seed(CategorySeeder::class);

    expect(Category::query()
        ->orderBy('id')
        ->pluck('name', 'slug')
        ->all())->toBe([
            'disney-tips' => 'Disney Tips',
            'park-accessibility' => 'Park Accessibility',
            'episode-recap' => 'Episode Recap',
            'family-life' => 'Family Life',
            'autism-awareness' => 'Autism Awareness',
            'disney-news' => 'Disney News',
            'food-reviews' => 'Food Reviews',
            'resort-reviews' => 'Resort Reviews',
            'disney-plus' => 'Disney+',
            'merchandise' => 'Merchandise',
            'general' => 'General',
        ]);
});

test('running the category seeder again keeps existing categories and their names', function (): void {
    Category::query()
        ->where('slug', 'general')
        ->update(['name' => 'Local General']);

    $this->seed(CategorySeeder::class);
    $this->seed(CategorySeeder::class);

    expect(Category::query()->count())->toBe(11)
        ->and(Category::query()
            ->where('slug', 'general')
            ->value('name'))
        ->toBe('Local General');
});
