<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Episodes\Pages\ListEpisodes;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

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

test('header does not count scheduled episodes as published', function (): void {
    Episode::factory()->create();
    Episode::factory()->scheduled()->create();
    Episode::factory()->draft()->create();
    actingAs(User::factory()->admin()->create());

    $header = Livewire::test(ListEpisodes::class)->instance()->getHeader();

    expect($header?->getData())
        ->toMatchArray([
            'total' => 3,
            'published' => 1,
            'drafts' => 1,
        ]);
});
