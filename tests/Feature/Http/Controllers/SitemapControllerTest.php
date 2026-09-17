<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('sitemap is valid and excludes unpublished content', function (): void {
    $post = Post::factory()->create();
    $draftPost = Post::factory()->draft()->create();
    $guide = Guide::factory()->create();
    $episode = Episode::factory()->create();
    Guide::factory()->draft()->create();

    $sitemap = get(route('sitemap'))
        ->assertOk()->assertHeader('Content-Type', 'application/xml')->assertSeeHtml(route('blog.show', $post))->assertSeeHtml(route('guides.show', $guide))->assertSeeHtml(route('episodes.show', $episode))
        ->assertDontSee($draftPost->slug);

    expect(simplexml_load_string($this->responseContent($sitemap)))->not->toBeFalse();
});
