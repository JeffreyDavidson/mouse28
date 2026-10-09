<?php

use App\Models\Category;
use App\Models\Post;
use App\Presenters\PostPresenter;
use Illuminate\Support\Facades\Storage;

covers(PostPresenter::class);

function postInCategory(?string $slug): Post
{
    $post = new Post;
    $post->setRelation('category', $slug === null ? null : new Category(['slug' => $slug]));

    return $post;
}

test('a post without a cover uses its category artwork style', function (): void {
    expect(PostPresenter::from(postInCategory('park-accessibility'))->artworkStyle())->toBe([
        'wash' => 'from-purple/30 via-cream to-gold/20',
        'ink' => 'text-purple-dark',
        'stamp' => 'Access field note',
    ]);
});

test('a post in an unmapped or missing category uses the general artwork style', function (?string $slug): void {
    expect(PostPresenter::from(postInCategory($slug))->artworkStyle())->toBe([
        'wash' => 'from-cyan-900/20 via-cream to-gold/20',
        'ink' => 'text-navy',
        'stamp' => 'Mouse28 dispatch',
    ]);
})->with([
    'unmapped category' => ['a-new-topic'],
    'no category' => [null],
]);

test('the cover lists the variants that exist', function (): void {
    Storage::fake('public');
    $disk = Storage::disk('public');
    $disk->put('posts/responsive/cover-480.webp', 'variant');
    $post = new Post(['featured_image_path' => 'posts/cover.png']);

    expect(PostPresenter::from($post)->featuredImageSrcset())->toBe("{$disk->url('posts/responsive/cover-480.webp')} 480w");
});

test('the cover has no srcset without a cover or variants', function (?string $path): void {
    Storage::fake('public');
    $post = new Post(['featured_image_path' => $path]);

    expect(PostPresenter::from($post)->featuredImageSrcset())->toBeNull();
})->with([
    'no cover' => [null],
    'no variants' => ['posts/cover.png'],
]);
