<?php

use App\Filament\Pages\PodcastSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('authenticated user can render podcast settings', function (): void {
    actingAs(User::factory()->admin()->create());

    get(PodcastSettings::getUrl())
        ->assertOk()
        ->assertSee('Podcast Settings')
        ->assertSee('Distribution Links');
});
