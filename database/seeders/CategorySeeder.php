<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * The standard blog categories (slug => name). Production receives them from
     * the `copy_post_categories_to_categories` migration; this seeder restores them
     * in a development database without changing categories that already exist.
     *
     * @var array<string, string>
     */
    private const array CATEGORIES = [
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
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $slug => $name) {
            Category::query()->firstOrCreate(['slug' => $slug], ['name' => $name]);
        }
    }
}
