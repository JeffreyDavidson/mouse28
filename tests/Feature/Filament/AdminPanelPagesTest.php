<?php

use App\Filament\Pages\NewsletterSubscribers;
use App\Filament\Pages\PodcastSettings;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('guest can render the admin login', function (): void {
    get('/admin/login')
        ->assertOk()
        ->assertSee('Mouse28')
        ->assertSee('Welcome Back');
});

test('authenticated user can render the custom admin pages', function (): void {
    Http::fake([
        'https://api.resend.com/*' => Http::response(['data' => []]),
    ]);

    $user = User::factory()->admin()->create();

    actingAs($user)
        ->get(NewsletterSubscribers::getUrl())
        ->assertOk()
        ->assertSee('Newsletter Subscribers')
        ->assertSee('No subscribers yet');

    get(PodcastSettings::getUrl())
        ->assertOk()
        ->assertSee('Podcast Settings')
        ->assertSee('Distribution Links');
});

test('authenticated user can render the resource headers and forms', function (): void {
    $user = User::factory()->admin()->create();

    actingAs($user)
        ->get(PostResource::getUrl())
        ->assertOk()
        ->assertSee('Blog Posts');

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Post');

    get(GuideResource::getUrl())
        ->assertOk()
        ->assertSee('Guides');

    get(GuideResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Guide');

    get(EpisodeResource::getUrl())
        ->assertOk()
        ->assertSee('Episodes');

    get(EpisodeResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Episode');

    get(ContactMessageResource::getUrl())
        ->assertOk()
        ->assertSee('Contact Messages');
});

test('non admin user cannot access the admin panel', function (): void {
    $user = User::factory()->create();

    actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});
