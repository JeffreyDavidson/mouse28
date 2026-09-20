<?php

use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('guests and non administrators cannot preview episodes', function (): void {
    $episode = Episode::factory()->draft()->create();

    get(route('preview.episodes', $episode))->assertForbidden();

    actingAs(User::factory()->create());

    get(route('preview.episodes', $episode))->assertForbidden();
});

test('administrators can preview draft content without exposing structured data', function (): void {
    $admin = User::factory()->admin()->create();
    $episode = Episode::factory()->draft()->create();

    actingAs($admin);

    get(route('preview.episodes', $episode))
        ->assertOk()
        ->assertViewIs('pages.episodes.show')
        ->assertViewHas('episode', fn (Episode $viewEpisode): bool => $viewEpisode->is($episode))
        ->assertViewHas('isPreview', true)
        ->assertSee('Preview mode')
        ->assertSeeHtml('noindex,nofollow')
        ->assertDontSeeHtml('application/ld+json');
});
