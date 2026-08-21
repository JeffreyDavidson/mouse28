<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_currently_published_content_is_publicly_visible(): void
    {
        $publishedPost = Post::factory()->create(['title' => 'Published park post']);
        $draftPost = Post::factory()->draft()->create(['title' => 'Draft park post']);
        $scheduledPost = Post::factory()->scheduled()->create(['title' => 'Scheduled park post']);

        $publishedEpisode = Episode::factory()->create(['title' => 'Published park episode']);
        $scheduledEpisode = Episode::factory()->scheduled()->create(['title' => 'Scheduled park episode']);

        $publishedGuide = Guide::factory()->create(['title' => 'Published park guide']);
        $draftGuide = Guide::factory()->draft()->create(['title' => 'Draft park guide']);
        $scheduledGuide = Guide::factory()->scheduled()->create(['title' => 'Scheduled park guide']);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee($publishedPost->title)
            ->assertDontSee($draftPost->title)
            ->assertDontSee($scheduledPost->title);

        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee($publishedGuide->title)
            ->assertDontSee($draftGuide->title)
            ->assertDontSee($scheduledGuide->title);

        $this->get(route('blog.show', $scheduledPost))->assertNotFound();
        $this->get(route('episodes.show', $scheduledEpisode))->assertNotFound();
        $this->get(route('guides.show', $scheduledGuide))->assertNotFound();

        $this->get(route('episodes.show', $publishedEpisode))->assertOk();
        $this->get(route('guides.show', $publishedGuide))
            ->assertOk()
            ->assertSee($publishedGuide->title)
            ->assertSee('Last reviewed');
    }

    public function test_blog_search_category_sorting_and_pagination_preserve_filters(): void
    {
        Post::factory()->create([
            'title' => 'Newest accessible plan',
            'category' => 'park-accessibility',
            'published_at' => now()->subDay(),
        ]);
        Post::factory()->create([
            'title' => 'Oldest accessible plan',
            'category' => 'park-accessibility',
            'published_at' => now()->subWeek(),
        ]);
        Post::factory()->create([
            'title' => 'Unrelated dining review',
            'category' => 'food-reviews',
        ]);

        $this->get(route('blog.index', [
            'category' => 'park-accessibility',
            'q' => 'accessible',
            'sort' => 'oldest',
        ]))
            ->assertOk()
            ->assertSeeInOrder(['Oldest accessible plan', 'Newest accessible plan'])
            ->assertDontSee('Unrelated dining review');
    }

    public function test_homepage_uses_the_documented_content_order_without_community_stories(): void
    {
        Post::factory()->count(2)->create();
        Guide::factory()->create(['title' => 'Sensory planning guide']);
        Episode::factory()->create();

        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder([
                'Latest from the Blog',
                'Latest Stories',
                'Your Guide to the Parks',
                'From the Podcast',
                'Meet Jeffrey & Cassie',
                'Stay in the Loop',
            ])
            ->assertDontSee('Community Stories');
    }

    public function test_sitemap_and_feeds_are_valid_and_exclude_unpublished_content(): void
    {
        config()->set('podcast.owner_name', 'Mouse28 Owner');
        config()->set('podcast.owner_email', 'podcast@example.com');

        $post = Post::factory()->create();
        $draftPost = Post::factory()->draft()->create();
        $episode = Episode::factory()->create(['audio_url' => 'https://cdn.example.com/episode.mp3']);
        $guide = Guide::factory()->create();
        Guide::factory()->draft()->create();

        $sitemap = $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee(route('guides.show', $guide), false)
            ->assertDontSee($draftPost->slug);

        $this->assertNotFalse(simplexml_load_string($sitemap->getContent()));

        $blogFeed = $this->get(route('rss.blog'))
            ->assertOk()
            ->assertSee($post->title)
            ->assertDontSee($draftPost->title);
        $this->assertNotFalse(simplexml_load_string($blogFeed->getContent()));

        $podcastFeed = $this->get(route('rss.podcast'))
            ->assertOk()
            ->assertSee($episode->title)
            ->assertSee('podcast@example.com');
        $this->assertNotFalse(simplexml_load_string($podcastFeed->getContent()));
        $this->assertDatabaseCount('podcasts', 0);
    }

    public function test_invalid_guide_category_falls_back_to_all_guides(): void
    {
        $guide = Guide::factory()->create();

        $this->get(route('guides.index', ['category' => 'not-a-category']))
            ->assertOk()
            ->assertSee($guide->title);
    }

    public function test_guides_are_flagged_when_their_editorial_review_is_due(): void
    {
        config()->set('mouse28.guide_review_interval_days', 180);

        $currentGuide = Guide::factory()->create([
            'last_reviewed_at' => now()->subDays(30),
        ]);
        $staleGuide = Guide::factory()->create([
            'last_reviewed_at' => now()->subDays(181),
        ]);
        $unreviewedGuide = Guide::factory()->create([
            'last_reviewed_at' => null,
        ]);

        $this->assertFalse($currentGuide->isReviewDue());
        $this->assertTrue($staleGuide->isReviewDue());
        $this->assertTrue($unreviewedGuide->isReviewDue());
        $this->assertEqualsCanonicalizing(
            [$staleGuide->id, $unreviewedGuide->id],
            Guide::reviewDue()->pluck('id')->all(),
        );

        $this->get(route('guides.show', $currentGuide))
            ->assertOk()
            ->assertDontSee('due for editorial review');

        $this->get(route('guides.show', $staleGuide))
            ->assertOk()
            ->assertSee('due for editorial review');
    }
}
