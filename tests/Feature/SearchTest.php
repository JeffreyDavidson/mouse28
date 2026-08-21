<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_page_is_available_from_public_navigation(): void
    {
        $this->get(route('search'))
            ->assertOk()
            ->assertSee('Search posts, guides, and podcast episodes')
            ->assertSee('noindex,follow', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('search'), false)
            ->assertSee('Search Mouse28');
    }

    public function test_search_groups_matching_published_content(): void
    {
        $post = Post::factory()->create(['title' => 'Sensory breaks in Magic Kingdom']);
        $guide = Guide::factory()->create(['title' => 'Sensory break planning guide']);
        $episode = Episode::factory()->create(['title' => 'How we plan sensory breaks']);
        $unrelatedPost = Post::factory()->create(['title' => 'Favorite resort meals']);

        $this->get(route('search', ['q' => 'sensory']))
            ->assertOk()
            ->assertSee('Showing 3 results for “sensory”')
            ->assertSeeInOrder(['Blog posts', $post->title, 'Guides', $guide->title, 'Podcast episodes', $episode->title])
            ->assertDontSee($unrelatedPost->title);
    }

    public function test_search_excludes_drafts_and_scheduled_content(): void
    {
        $publishedPost = Post::factory()->create(['title' => 'Accessible park planning']);
        $draftGuide = Guide::factory()->draft()->create(['title' => 'Accessible draft guide']);
        $scheduledEpisode = Episode::factory()->scheduled()->create(['title' => 'Accessible scheduled episode']);

        $this->get(route('search', ['q' => 'accessible']))
            ->assertOk()
            ->assertSee($publishedPost->title)
            ->assertDontSee($draftGuide->title)
            ->assertDontSee($scheduledEpisode->title);
    }

    public function test_search_query_is_limited_to_one_hundred_characters(): void
    {
        $this->from(route('search'))
            ->get(route('search', ['q' => str_repeat('a', 101)]))
            ->assertRedirect(route('search'))
            ->assertSessionHasErrors('q');
    }
}
