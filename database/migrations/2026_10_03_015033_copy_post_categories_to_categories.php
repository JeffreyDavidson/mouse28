<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The legacy `PostCategory` values (slug => label), written out here so this
     * migration keeps working after the enum is removed.
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

    /**
     * Create one category per legacy post category (the label as the name and the
     * stored value as the slug, so `/blog?category=` URLs keep working), then link
     * every post, soft-deleted posts included, to the category whose slug matches
     * its legacy `category` string. Existing categories and links are kept, so the
     * migration can safely run again. `posts.category` stays until a later release
     * drops it.
     */
    public function up(): void
    {
        $this->insertMissingCategories();
        $this->linkPostsToCategories();
        $this->assertEveryCategoryWasLinked();
    }

    private function insertMissingCategories(): void
    {
        $existing = DB::table('categories')->pluck('slug')->all();
        $now = Date::now();

        foreach (self::CATEGORIES as $slug => $name) {
            if (in_array($slug, $existing, true)) {
                continue;
            }

            DB::table('categories')->insert([
                'name' => $name,
                'slug' => $slug,
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function linkPostsToCategories(): void
    {
        DB::table('posts')
            ->whereNull('category_id')
            ->whereNotNull('category')
            ->update([
                'category_id' => DB::raw('(select categories.id from categories where categories.slug = posts.category)'),
            ]);
    }

    /**
     * Refuse to finish while any post still holds a category only in the legacy
     * string, so `posts.category` can never be dropped while it holds a category
     * `category_id` lacks.
     */
    private function assertEveryCategoryWasLinked(): void
    {
        $unlinked = DB::table('posts')
            ->whereNotNull('category')
            ->whereNull('category_id')
            ->count();

        if ($unlinked > 0) {
            throw new RuntimeException("{$unlinked} post(s) still have a category with no category_id after the backfill.");
        }
    }
};
