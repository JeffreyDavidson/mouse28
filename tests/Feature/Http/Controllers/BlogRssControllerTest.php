<?php

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('blog feed is valid and excludes unpublished content', function (): void {
    $post = Post::factory()->create();
    $draftPost = Post::factory()->draft()->create();
    $scheduledPost = Post::factory()->scheduled()->create();

    $blogFeed = get(route('rss.blog'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml')
        ->assertSee($post->title)
        ->assertDontSee($draftPost->title)
        ->assertDontSee($scheduledPost->title);

    expect(simplexml_load_string($this->responseContent($blogFeed)))->not->toBeFalse();
});
