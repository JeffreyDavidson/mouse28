<?php

namespace Tests\Feature;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use App\Support\EditorialReadiness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditorialReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_status_distinguishes_drafts_schedules_and_missing_dates(): void
    {
        $draft = Post::factory()->draft()->create();
        $missingDate = Post::factory()->create(['published_at' => null]);
        $scheduled = Post::factory()->scheduled()->create();
        $published = Post::factory()->create();

        $this->assertSame('Draft', EditorialReadiness::status($draft));
        $this->assertSame('Needs publish date', EditorialReadiness::status($missingDate));
        $this->assertSame('Scheduled', EditorialReadiness::status($scheduled));
        $this->assertSame('Published', EditorialReadiness::status($published));
    }

    public function test_readiness_reports_actionable_issues_for_each_content_type(): void
    {
        $post = Post::factory()->create([
            'cover_image' => null,
            'meta_title' => null,
            'meta_description' => null,
        ]);
        $guide = Guide::factory()->create([
            'source_url' => null,
            'last_reviewed_at' => null,
        ]);
        $episode = Episode::factory()->create([
            'audio_url' => null,
            'transcript' => null,
        ]);

        $this->assertSame('3 missing', EditorialReadiness::label($post));
        $this->assertContains('Add a cover image', EditorialReadiness::issues($post));
        $this->assertContains('Add an official source', EditorialReadiness::issues($guide));
        $this->assertContains('Set the review date', EditorialReadiness::issues($guide));
        $this->assertContains('Add the audio URL', EditorialReadiness::issues($episode));
        $this->assertContains('Add a transcript', EditorialReadiness::issues($episode));
    }

    public function test_complete_content_is_marked_ready(): void
    {
        $post = Post::factory()->create([
            'cover_image' => 'posts/complete.jpg',
            'meta_title' => 'A complete park-planning post',
            'meta_description' => 'A complete description for search and social sharing.',
        ]);

        $this->assertSame([], EditorialReadiness::issues($post));
        $this->assertSame('Ready', EditorialReadiness::label($post));
        $this->assertSame('Ready to publish.', EditorialReadiness::summary($post));
    }

    public function test_administrators_can_preview_draft_content_without_exposing_structured_data(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->draft()->create();
        $guide = Guide::factory()->draft()->create();
        $episode = Episode::factory()->draft()->create();

        $this->actingAs($admin);

        foreach ([
            route('preview.posts', $post),
            route('preview.guides', $guide),
            route('preview.episodes', $episode),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Preview mode')
                ->assertSee('noindex,nofollow', false)
                ->assertDontSee('application/ld+json', false);
        }
    }

    public function test_preview_routes_reject_guests_and_non_admin_users(): void
    {
        $post = Post::factory()->draft()->create();

        $this->get(route('preview.posts', $post))->assertForbidden();

        $this->actingAs(User::factory()->create())
            ->get(route('preview.posts', $post))
            ->assertForbidden();
    }

    public function test_filament_content_tables_show_readiness_and_missing_publish_dates(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->create(['published_at' => null]);
        Guide::factory()->create(['published_at' => null]);
        Episode::factory()->create(['published_at' => null]);

        $this->actingAs($admin);

        foreach ([PostResource::getUrl(), GuideResource::getUrl(), EpisodeResource::getUrl()] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Readiness')
                ->assertSee('Needs publish date');
        }
    }

    public function test_filament_forms_explain_publish_requirements_and_edit_pages_offer_previews(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->draft()->create();
        $guide = Guide::factory()->draft()->create();
        $episode = Episode::factory()->draft()->create();

        $this->actingAs($admin);

        $this->get(PostResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Published posts require');
        $this->get(GuideResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Published guides require');
        $this->get(EpisodeResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Published episodes require');

        foreach ([
            PostResource::getUrl('edit', ['record' => $post]),
            GuideResource::getUrl('edit', ['record' => $guide]),
            EpisodeResource::getUrl('edit', ['record' => $episode]),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('Preview');
        }
    }
}
