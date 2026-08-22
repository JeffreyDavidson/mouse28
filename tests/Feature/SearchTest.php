<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\from;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('search page is available from public navigation', function (): void {
    get(route('search'))
        ->assertOk()
        ->assertSee('Search posts, guides, and podcast episodes')
        ->assertSee('noindex,follow', false);

    get(route('home'))
        ->assertOk()
        ->assertSee(route('search'), false)
        ->assertSee('Search Mouse28');
});

test('search groups matching published content', function (): void {
    $post = Post::factory()->create(['title' => 'Sensory breaks in Magic Kingdom']);
    $guide = Guide::factory()->create(['title' => 'Sensory break planning guide']);
    $episode = Episode::factory()->create(['title' => 'How we plan sensory breaks']);
    $unrelatedPost = Post::factory()->create(['title' => 'Favorite resort meals']);

    get(route('search', ['q' => 'sensory']))
        ->assertOk()
        ->assertSee('Showing 3 results for “sensory”')
        ->assertSeeInOrder(['Blog posts', $post->title, 'Guides', $guide->title, 'Podcast episodes', $episode->title])
        ->assertDontSee($unrelatedPost->title);
});

test('search excludes drafts and scheduled content', function (): void {
    $publishedPost = Post::factory()->create(['title' => 'Accessible park planning']);
    $draftGuide = Guide::factory()->draft()->create(['title' => 'Accessible draft guide']);
    $scheduledEpisode = Episode::factory()->scheduled()->create(['title' => 'Accessible scheduled episode']);

    get(route('search', ['q' => 'accessible']))
        ->assertOk()
        ->assertSee($publishedPost->title)
        ->assertDontSee($draftGuide->title)
        ->assertDontSee($scheduledEpisode->title);
});

test('search query is limited to one hundred characters', function (): void {
    from(route('search'))
        ->get(route('search', ['q' => str_repeat('a', 101)]))
        ->assertRedirect(route('search'))
        ->assertSessionHasErrors('q');
});
