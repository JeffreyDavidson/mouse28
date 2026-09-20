<?php

use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('administrators can preview draft content without exposing structured data', function (): void {
    $admin = User::factory()->admin()->create();
    $guide = Guide::factory()->draft()->create();

    actingAs($admin);

    get(route('preview.guides', $guide))
        ->assertOk()
        ->assertViewIs('pages.guides.show')
        ->assertViewHas('guide', fn (Guide $viewGuide): bool => $viewGuide->is($guide))
        ->assertViewHas('isPreview', true)
        ->assertSee('Preview mode')
        ->assertSeeHtml('noindex,nofollow')
        ->assertDontSeeHtml('application/ld+json');
});
