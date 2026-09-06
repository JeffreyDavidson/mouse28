<?php

use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('edit page offers a draft preview', function (): void {
    $admin = User::factory()->admin()->create();
    $guide = Guide::factory()->draft()->create();

    actingAs($admin);

    get(GuideResource::getUrl('edit', ['record' => $guide]))
        ->assertOk()
        ->assertSee('Preview');
});

test('ready drafts can be explicitly published and unpublished', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Guide::factory()->draft()->create([
        'cover_image' => 'guides/complete.jpg',
        'meta_title' => 'Complete guide title',
        'meta_description' => 'Complete guide description',
    ]);

    actingAs($admin);

    Livewire::test(EditGuide::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeTrue()
        ->and($record->published_at)->not->toBeNull();

    Livewire::test(EditGuide::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeFalse();
});

test('deleted content leaves the public site and can be restored by an administrator', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Guide::factory()->create();

    $record->delete();

    get(route('guides.show', $record))->assertNotFound();

    actingAs($admin);

    Livewire::test(EditGuide::class, ['record' => $record->getRouteKey()])
        ->callAction('restore')
        ->assertNotified();

    expect($record->refresh()->deleted_at)->toBeNull();
    get(route('guides.show', $record))->assertOk();
});
