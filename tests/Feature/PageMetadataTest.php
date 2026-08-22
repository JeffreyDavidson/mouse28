<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_pages_have_specific_search_and_social_metadata(): void
    {
        Podcast::query()->create([
            'name' => 'Mouse28 Weekly',
            'description' => 'A weekly Disney parks podcast for accessibility-minded families.',
            'cover_image' => 'podcasts/show-cover.jpg',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="description" content="Accessibility tips, sensory-friendly park planning, family experiences, and the Mouse28 podcast from Jeffrey and Cassie Davidson.">', false)
            ->assertSee('<meta property="og:image" content="'.url('/images/hero-family.jpg').'">', false);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Disney Parks Blog — Mouse28">', false)
            ->assertSee('<meta property="og:url" content="'.route('blog.index').'">', false);

        $this->get(route('guides.index'))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Disney Parks Guides — Mouse28">', false);

        $this->get(route('episodes.index'))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Mouse28 Weekly Podcast">', false)
            ->assertSee('<meta property="og:description" content="A weekly Disney parks podcast for accessibility-minded families.">', false)
            ->assertSee('<meta property="og:image" content="'.url('/storage/podcasts/show-cover.jpg').'">', false);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('<meta property="og:title" content="About the Davidson Family — Mouse28">', false);

        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('<meta name="description" content="Contact Jeffrey and Cassie about Mouse28, Disney park accessibility, family travel, collaborations, or the podcast.">', false);
    }

    public function test_archive_canonicals_preserve_meaningful_filters_and_pagination(): void
    {
        Post::factory()->count(13)->create(['category' => 'park-accessibility']);
        Guide::factory()->count(13)->create(['category' => 'family-planning']);
        Episode::factory()->count(13)->create();

        $blogCanonical = route('blog.index', [
            'category' => 'park-accessibility',
            'page' => 2,
        ]);
        $guideCanonical = route('guides.index', [
            'category' => 'family-planning',
            'page' => 2,
        ]);
        $episodeCanonical = route('episodes.index', ['page' => 2]);

        $this->get($blogCanonical)
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.e($blogCanonical).'">', false);

        $this->get($guideCanonical)
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.e($guideCanonical).'">', false);

        $this->get($episodeCanonical)
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.e($episodeCanonical).'">', false);
    }

    public function test_search_and_blog_text_filters_are_not_indexed(): void
    {
        $this->get(route('search', ['q' => 'sensory']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('search').'">', false);

        $this->get(route('blog.index', ['q' => 'sensory']))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex,follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('blog.index').'">', false);
    }

    public function test_invalid_blog_filters_do_not_create_indexable_archive_variants(): void
    {
        $this->get(route('blog.index', [
            'category' => 'not-a-category',
            'sort' => 'not-a-sort',
        ]))
            ->assertOk()
            ->assertSee('<title>Disney Parks Blog — Mouse28</title>', false)
            ->assertSee('<meta name="robots" content="index,follow">', false)
            ->assertSee('<link rel="canonical" href="'.route('blog.index').'">', false);
    }

    public function test_relative_content_images_become_absolute_social_image_urls(): void
    {
        $post = Post::factory()->create([
            'og_image' => 'posts/social-card.jpg',
        ]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee('<meta property="og:image" content="'.url('/storage/posts/social-card.jpg').'">', false)
            ->assertSee('<meta name="twitter:image" content="'.url('/storage/posts/social-card.jpg').'">', false);
    }
}
