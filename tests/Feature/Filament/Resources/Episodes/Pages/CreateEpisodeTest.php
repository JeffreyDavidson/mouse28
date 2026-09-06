<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('authenticated user can render the create form', function (): void {
    actingAs(User::factory()->admin()->create());

    get(EpisodeResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Episode');
});

test('create form explains editorial requirements', function (): void {
    actingAs(User::factory()->admin()->create());

    get(EpisodeResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Transistor Episode URL')
        ->assertSee('share.transistor.fm/s/')
        ->assertDontSee('Hosted MP3')
        ->assertDontSee('External Audio URL')
        ->assertSee('Landscape image (1.91:1)');
});
