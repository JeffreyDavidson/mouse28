<?php

use App\Filament\Resources\Guides\GuideResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the create form', function (): void {
    actingAs(User::factory()->admin()->create());

    get(GuideResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Guide');
});

test('create form explains editorial requirements', function (): void {
    actingAs(User::factory()->admin()->create());

    get(GuideResource::getUrl('create'))
        ->assertOk()
        ->assertSee('use the Publish action')
        ->assertSee('Landscape image (1.91:1)');
});
