<?php

use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

/** Creates a post in the category and gives it a legacy category string as well. */
function postWithLegacyCategory(Category $category, string $legacyCategory): Post
{
    $post = Post::factory()->for($category)->create();
    DB::table('posts')->where('id', $post->id)->update(['category' => $legacyCategory]);

    return $post;
}

test('a loaded post reads its category through the relation, not the legacy column', function (): void {
    $category = Category::factory()->create(['name' => 'Sample Topic']);
    $post = postWithLegacyCategory($category, 'disney-tips');

    $loaded = Post::query()->findOrFail($post->id);

    expect($loaded->category)->toBeInstanceOf(Category::class)
        ->and($loaded->category?->is($category))->toBeTrue()
        ->and($loaded->category_label)->toBe('Sample Topic')
        ->and($loaded->getAttributes())->not->toHaveKey('category');
});

test('a refreshed post reads its category through the relation', function (): void {
    $category = Category::factory()->create();
    $post = postWithLegacyCategory($category, 'disney-tips');

    expect($post->refresh()->category?->is($category))->toBeTrue();
});

test('saving a loaded post leaves the legacy category column unchanged', function (): void {
    $post = postWithLegacyCategory(Category::factory()->create(), 'disney-tips');
    $loaded = Post::query()->findOrFail($post->id);

    $loaded->update(['title' => 'Updated title', 'category_id' => Category::factory()->create()->id]);

    expect(DB::table('posts')->where('id', $post->id)->value('category'))->toBe('disney-tips');
});
