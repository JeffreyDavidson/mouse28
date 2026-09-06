<?php

use App\Filament\Resources\Posts\PostResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('authenticated user can render the create form', function (): void {
    actingAs(User::factory()->admin()->create());

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Post');
});

test('create form explains editorial requirements', function (): void {
    actingAs(User::factory()->admin()->create());

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('use the Publish action')
        ->assertSee('Optional for evergreen posts')
        ->assertSee('Landscape image (1.91:1)');
});
