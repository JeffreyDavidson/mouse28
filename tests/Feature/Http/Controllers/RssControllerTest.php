<?php

use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('blog feed is valid and excludes unpublished content', function (): void {
    $post = Post::factory()->create();
    $draftPost = Post::factory()->draft()->create();
    $guide = Guide::factory()->create();
    Guide::factory()->draft()->create();

    $blogFeed = get(route('rss.blog'))
        ->assertOk()
        ->assertSee($post->title)
        ->assertDontSee($draftPost->title);
    expect(simplexml_load_string($blogFeed->getContent()))->not->toBeFalse();
});
