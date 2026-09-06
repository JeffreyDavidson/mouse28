<?php

use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('administrators can preview draft content without exposing structured data', function (): void {
    $admin = User::factory()->admin()->create();
    $episode = Episode::factory()->draft()->create();

    actingAs($admin);

    get(route('preview.episodes', $episode))
        ->assertOk()
        ->assertSee('Preview mode')
        ->assertSee('noindex,nofollow', false)
        ->assertDontSee('application/ld+json', false);
});
