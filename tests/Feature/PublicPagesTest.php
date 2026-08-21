<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_pages_render(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mouse28');

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Our Story');

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('Blog');

        $this->get(route('episodes.index'))
            ->assertOk()
            ->assertSee('Listen Along');

        $this->get(route('contact.show'))
            ->assertOk()
            ->assertSee('Get in Touch');
    }

    public function test_published_post_detail_page_renders(): void
    {
        $post = Post::query()->create([
            'title' => 'An Accessible Day at the Parks',
            'slug' => 'accessible-day-at-the-parks',
            'excerpt' => 'A practical guide for planning a comfortable park day.',
            'body' => 'Start with a flexible plan and make room for sensory breaks.',
            'category' => 'park-accessibility',
            'author' => 'jeffrey',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('blog.show', $post))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee('Start with a flexible plan', false);
    }

    public function test_published_episode_detail_page_renders(): void
    {
        $episode = Episode::query()->create([
            'title' => 'Planning a Sensory-Friendly Visit',
            'slug' => 'planning-a-sensory-friendly-visit',
            'description' => 'How our family prepares for a day in the parks.',
            'show_notes' => '<p>Our favorite planning strategies.</p>',
            'transcript' => '<p><strong>Jeffrey:</strong> Welcome to the show.</p>',
            'episode_number' => 1,
            'season_number' => 1,
            'duration_seconds' => 1800,
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('episodes.show', $episode))
            ->assertOk()
            ->assertSee($episode->title)
            ->assertSee('Our favorite planning strategies', false);
    }
}
