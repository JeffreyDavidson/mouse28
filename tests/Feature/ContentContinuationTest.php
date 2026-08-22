<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentContinuationTest extends TestCase
{
    use RefreshDatabase;

    public function test_episode_pages_link_to_the_adjacent_published_episodes(): void
    {
        $olderEpisode = Episode::factory()->create([
            'title' => 'Older published episode',
            'published_at' => now()->subDays(3),
        ]);
        $episode = Episode::factory()->create([
            'title' => 'Current episode',
            'published_at' => now()->subDays(2),
        ]);
        $newerEpisode = Episode::factory()->create([
            'title' => 'Newer published episode',
            'published_at' => now()->subDay(),
        ]);
        $draftEpisode = Episode::factory()->draft()->create([
            'title' => 'Private draft episode',
        ]);
        $scheduledEpisode = Episode::factory()->scheduled()->create([
            'title' => 'Future scheduled episode',
        ]);

        $this->get(route('episodes.show', $episode))
            ->assertOk()
            ->assertSee('Previous episode')
            ->assertSee($olderEpisode->title)
            ->assertSee(route('episodes.show', $olderEpisode), false)
            ->assertSee('Next episode')
            ->assertSee($newerEpisode->title)
            ->assertSee(route('episodes.show', $newerEpisode), false)
            ->assertDontSee($draftEpisode->title)
            ->assertDontSee($scheduledEpisode->title);
    }

    public function test_related_guides_prioritize_the_category_and_fill_open_slots(): void
    {
        $guide = Guide::factory()->create([
            'title' => 'Current accessibility guide',
            'category' => 'accessibility',
        ]);
        $sameCategory = Guide::factory()->create([
            'title' => 'Related accessibility guide',
            'category' => 'accessibility',
        ]);
        $fallbackGuide = Guide::factory()->create([
            'title' => 'Useful planning guide',
            'category' => 'family-planning',
        ]);
        $draftGuide = Guide::factory()->draft()->create([
            'title' => 'Private draft guide',
            'category' => 'accessibility',
        ]);

        $this->get(route('guides.show', $guide))
            ->assertOk()
            ->assertSeeInOrder([$sameCategory->title, $fallbackGuide->title])
            ->assertDontSee($draftGuide->title);
    }

    public function test_content_category_labels_link_to_their_filtered_indexes(): void
    {
        $post = Post::factory()->create(['category' => 'park-accessibility']);
        $guide = Guide::factory()->create(['category' => 'family-planning']);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee(route('blog.index', ['category' => $post->category]), false);

        $this->get(route('guides.show', $guide))
            ->assertOk()
            ->assertSee(route('guides.index', ['category' => $guide->category]), false);
    }

    public function test_posts_do_not_reveal_an_unpublished_related_episode(): void
    {
        $draftEpisode = Episode::factory()->draft()->create([
            'title' => 'Unannounced podcast episode',
        ]);
        $post = Post::factory()->create([
            'episode_id' => $draftEpisode->getKey(),
        ]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertDontSee($draftEpisode->title)
            ->assertDontSee(route('episodes.show', $draftEpisode), false);
    }
}
