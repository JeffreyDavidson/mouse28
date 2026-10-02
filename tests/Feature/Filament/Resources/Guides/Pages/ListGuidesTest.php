<?php

use App\Enums\PublishStatus;
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
        ->assertSee('Guides')
        ->assertSee('Manage your accessibility guides')
        ->assertSee('New Guide');
});

test('content table shows readiness and the persisted publish status', function (): void {
    $admin = User::factory()->admin()->create();
    Guide::factory()->create(['status' => PublishStatus::InReview]);

    actingAs($admin);

    get(GuideResource::getUrl())
        ->assertOk()
        ->assertSee('Readiness')
        ->assertSee('In Review');
});

test('draft and scheduled tabs filter guides', function (): void {
    $draft = Guide::factory()->draft()->create();
    $inReview = Guide::factory()->create(['status' => PublishStatus::InReview]);
    $scheduled = Guide::factory()->scheduled()->create();
    actingAs(User::factory()->admin()->create());

    livewire(ListGuides::class)
        ->set('activeTab', 'drafts')
        ->assertCanSeeTableRecords([$draft, $inReview])
        ->assertCanNotSeeTableRecords([$scheduled])
        ->set('activeTab', 'scheduled')
        ->assertCanSeeTableRecords([$scheduled])
        ->assertCanNotSeeTableRecords([$draft]);
});

test('header does not count scheduled guides as published', function (): void {
    Guide::factory()->create();
    Guide::factory()->scheduled()->create();
    Guide::factory()->draft()->create();
    Guide::factory()->create(['status' => PublishStatus::InReview]);
    actingAs(User::factory()->admin()->create());

    $page = livewire(ListGuides::class);
    $component = $page->instance();

    expect($component->getHeader()?->getData())
        ->toMatchArray([
            'published' => 1,
            'drafts' => 2,
        ]);
});

test('the review due filter and source review column show guides that need review', function (): void {
    actingAs(User::factory()->admin()->create());
    $due = Guide::factory()->create(['last_reviewed_at' => null]);
    $current = Guide::factory()->create(['last_reviewed_at' => today()]);

    livewire(ListGuides::class)
        ->filterTable('review_due')
        ->assertCanSeeTableRecords([$due])
        ->assertCanNotSeeTableRecords([$current])
        ->assertTableColumnExists('source_review_status');
});
