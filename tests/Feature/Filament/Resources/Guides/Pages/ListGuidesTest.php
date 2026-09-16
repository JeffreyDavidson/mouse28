<?php

use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\ListGuides;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the resource listing', function (): void {
    actingAs(User::factory()->admin()->create());

    get(GuideResource::getUrl())
        ->assertOk()
        ->assertSee('Guides');
});

test('content table shows readiness and missing publish dates', function (): void {
    $admin = User::factory()->admin()->create();
    Guide::factory()->create(['published_at' => null]);

    actingAs($admin);

    get(GuideResource::getUrl())
        ->assertOk()
        ->assertSee('Readiness')
        ->assertSee('Needs publish date');
});

test('draft and scheduled tabs filter guides', function (): void {
    $draft = Guide::factory()->draft()->create();
    $scheduled = Guide::factory()->scheduled()->create();
    actingAs(User::factory()->admin()->create());

    livewire(ListGuides::class)
        ->set('activeTab', 'drafts')
        ->assertCanSeeTableRecords([$draft])
        ->assertCanNotSeeTableRecords([$scheduled])
        ->set('activeTab', 'scheduled')
        ->assertCanSeeTableRecords([$scheduled])
        ->assertCanNotSeeTableRecords([$draft]);
});
