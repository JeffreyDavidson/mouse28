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
    $scheduledPost = Post::factory()->scheduled()->create();
    $guide = Guide::factory()->create();
    $draftGuide = Guide::factory()->draft()->create();
    $scheduledGuide = Guide::factory()->scheduled()->create();
    $episode = Episode::factory()->create();
    $draftEpisode = Episode::factory()->draft()->create();
    $scheduledEpisode = Episode::factory()->scheduled()->create();

    $sitemap = get(route('sitemap'))
        ->assertOk()->assertHeader('Content-Type', 'application/xml')->assertSeeHtml(route('blog.show', $post))->assertSeeHtml(route('guides.show', $guide))->assertSeeHtml(route('episodes.show', $episode))
        ->assertDontSee($draftPost->slug)
        ->assertDontSee($scheduledPost->slug)
        ->assertDontSee($draftGuide->slug)
        ->assertDontSee($scheduledGuide->slug)
        ->assertDontSee($draftEpisode->slug)
        ->assertDontSee($scheduledEpisode->slug);

    expect(simplexml_load_string($this->responseContent($sitemap)))->not->toBeFalse();
});
