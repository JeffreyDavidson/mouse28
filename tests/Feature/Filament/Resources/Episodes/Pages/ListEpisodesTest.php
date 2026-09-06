<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('authenticated user can render the resource listing', function (): void {
    actingAs(User::factory()->admin()->create());

    get(EpisodeResource::getUrl())
        ->assertOk()
        ->assertSee('Episodes');
});

test('content table shows readiness and missing publish dates', function (): void {
    $admin = User::factory()->admin()->create();
    Episode::factory()->create(['published_at' => null]);

    actingAs($admin);

    get(EpisodeResource::getUrl())
        ->assertOk()
        ->assertSee('Readiness')
        ->assertSee('Needs publish date');
});
