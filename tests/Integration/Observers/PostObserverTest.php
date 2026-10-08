<?php

use App\Models\Post;
use App\Observers\PostObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

covers(PostObserver::class);

beforeEach(function (): void {
    Storage::fake('public');
    $image = UploadedFile::fake()
        ->image('post.png', 1280, 8)
        ->getContent();
    Storage::disk('public')->put('posts/old.png', $image);
    Storage::disk('public')->put('posts/new.png', $image);
});

/** @return list<string> */
function postVariantPaths(string $name): array
{
    return array_map(fn (int $width): string => "posts/responsive/{$name}-{$width}.webp", [480, 640, 768, 1280]);
}

test('creating a post with an image generates its variants', function (): void {
    Post::factory()->create(['featured_image_path' => 'posts/old.png']);

    Storage::disk('public')->assertExists(postVariantPaths('old'));
});

test('replacing the image generates new variants and removes the previous ones after commit', function (): void {
    $post = Post::factory()->create(['featured_image_path' => 'posts/old.png']);

    DB::transaction(fn () => $post->update(['featured_image_path' => 'posts/new.png']));

    Storage::disk('public')->assertExists(postVariantPaths('new'));
    Storage::disk('public')->assertMissing([...postVariantPaths('old'), 'posts/old.png']);
});

test('a rolled back replacement keeps the previous variants', function (): void {
    $post = Post::factory()->create(['featured_image_path' => 'posts/old.png']);

    DB::beginTransaction();
    $post->update(['featured_image_path' => 'posts/new.png']);
    DB::rollBack();

    Storage::disk('public')->assertExists(postVariantPaths('old'));
});

test('soft deleting keeps the variants and force deleting removes them', function (): void {
    $post = Post::factory()->create(['featured_image_path' => 'posts/old.png']);

    $post->delete();

    Storage::disk('public')->assertExists(postVariantPaths('old'));

    $post->forceDelete();

    Storage::disk('public')->assertMissing(postVariantPaths('old'));
});

test('a failed generation logs a warning without the path and still saves the post', function (): void {
    Storage::disk('public')->put('posts/private-name.png', 'not an image');
    Log::shouldReceive('warning')
        ->once()
        ->with('Responsive post image generation failed. Run posts:generate-image-variants to retry.');

    $post = Post::factory()->create(['featured_image_path' => 'posts/private-name.png']);

    expect($post->exists)->toBeTrue();
});
