<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_unknown_urls_render_the_branded_recovery_page(): void
    {
        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('<title>Page Not Found — Mouse28</title>', false)
            ->assertSee('<meta name="robots" content="noindex,nofollow">', false)
            ->assertDontSee('<link rel="canonical"', false)
            ->assertSee('That page wandered off')
            ->assertSee(route('home'), false)
            ->assertSee(route('search'), false)
            ->assertSee(route('blog.index'), false)
            ->assertSee(route('guides.index'), false)
            ->assertSee(route('episodes.index'), false);
    }

    public function test_hidden_content_uses_the_same_recovery_page_without_revealing_its_title(): void
    {
        $draftPost = Post::factory()->draft()->create([
            'title' => 'Unannounced family update',
        ]);

        $this->get(route('blog.show', $draftPost))
            ->assertNotFound()
            ->assertSee('That page wandered off')
            ->assertDontSee($draftPost->title);
    }
}
