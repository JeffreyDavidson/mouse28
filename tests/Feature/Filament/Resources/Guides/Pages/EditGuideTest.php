<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the edit form', function (): void {
    $guide = Guide::factory()->draft()->create();

    actingAs(User::factory()->admin()->create());

    get(GuideResource::getUrl('edit', ['record' => $guide]))
        ->assertOk()
        ->assertSee($guide->title)
        ->assertSee('Save changes');
});

test('published URLs are preserved and draft slugs are validated', function (): void {
    actingAs(User::factory()->admin()->create());
    $published = Guide::factory()->create(['slug' => 'permanent-url']);
    $draft = Guide::factory()->draft()->create();

    livewire(EditGuide::class, ['record' => $published->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($published->refresh()->slug)->toBe('permanent-url');

    livewire(EditGuide::class, ['record' => $draft->getRouteKey()])
        ->fillForm(['slug' => 'Invalid/URL'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);
});

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

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeTrue()
        ->and($record->published_at)->not->toBeNull();

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified();

    expect($record->refresh()->is_published)->toBeFalse();
});

test('deleted content leaves the public site and can be restored by an administrator', function (): void {
    // Arrange
    $admin = User::factory()->admin()->create();
    $record = Guide::factory()->create();

    $record->delete();

    get(route('guides.show', $record))->assertNotFound();

    actingAs($admin);

    $page = livewire(EditGuide::class, ['record' => $record->getRouteKey()]);

    // Act
    $page->callAction('restore');
    $response = get(route('guides.show', $record));

    // Assert
    $page->assertNotified();
    $this->assertNotSoftDeleted($record);
    $response->assertOk();
});

test('editor saves author and category selections as enums', function (): void {
    $record = Guide::factory()->draft()->create();
    actingAs(User::factory()->admin()->create());

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->fillForm(['author' => 'jeffrey', 'category' => 'family-planning'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->author)->toBe(ContentAuthor::Jeffrey)
        ->and($record->category)->toBe(GuideCategory::FamilyPlanning);
});
