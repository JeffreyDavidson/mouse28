<?php

namespace Tests\Feature\Filament;

use App\Filament\Pages\NewsletterSubscribers;
use App\Filament\Pages\PodcastSettings;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminPanelPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_render_the_admin_login(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Mouse28')
            ->assertSee('Welcome Back');
    }

    public function test_authenticated_user_can_render_the_custom_admin_pages(): void
    {
        Http::fake([
            'https://api.resend.com/*' => Http::response(['data' => []]),
        ]);

        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(NewsletterSubscribers::getUrl())
            ->assertOk()
            ->assertSee('Newsletter Subscribers')
            ->assertSee('No subscribers yet');

        $this->get(PodcastSettings::getUrl())
            ->assertOk()
            ->assertSee('Podcast Settings')
            ->assertSee('Distribution Links');
    }

    public function test_authenticated_user_can_render_the_resource_headers_and_forms(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->get(PostResource::getUrl())
            ->assertOk()
            ->assertSee('Blog Posts');

        $this->get(PostResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Create Post');

        $this->get(GuideResource::getUrl())
            ->assertOk()
            ->assertSee('Guides');

        $this->get(GuideResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Create Guide');

        $this->get(EpisodeResource::getUrl())
            ->assertOk()
            ->assertSee('Episodes');

        $this->get(EpisodeResource::getUrl('create'))
            ->assertOk()
            ->assertSee('Create Episode');

        $this->get(ContactMessageResource::getUrl())
            ->assertOk()
            ->assertSee('Contact Messages');
    }

    public function test_non_admin_user_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }
}
