<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('previously published URLs stay locked after clearing the date and unpublishing', function (): void {
    actingAs(User::factory()->admin()->create());
    $record = Guide::factory()->create(['slug' => 'original-public-url']);

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => null])
        ->call('save')
        ->assertHasNoFormErrors();
    $record->refresh()->update(['is_published' => false]);

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->slug)->toBe('original-public-url');
});

test('authenticated user can render the edit form', function (): void {
    $guide = Guide::factory()->draft()->create();

    actingAs(User::factory()->admin()->create());

    get(GuideResource::getUrl('edit', ['record' => $guide]))
        ->assertOk()
        ->assertSee($guide->title)
        ->assertSee('Save changes');
});

test('published URLs cannot be changed by submitted editor state', function (): void {
    actingAs(User::factory()->admin()->create());
    $published = Guide::factory()->create(['slug' => 'permanent-url']);

    livewire(EditGuide::class, ['record' => $published->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($published->refresh()->slug)->toBe('permanent-url');

});

test('draft slugs reject characters that cannot form public routes', function (): void {
    actingAs(User::factory()->admin()->create());
    $draft = Guide::factory()->draft()->create();

    livewire(EditGuide::class, ['record' => $draft->getRouteKey()])
        ->fillForm(['slug' => 'Invalid/URL'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);
});

test('edit page offers a draft preview', function (): void {
    $admin = User::factory()->admin()->create();
    $guide = Guide::factory()->draft()->create();

    actingAs($admin);

    livewire(EditGuide::class, ['record' => $guide->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', route('preview.guides', $guide))
        ->assertActionShouldOpenUrlInNewTab('preview');
});

test('ready drafts can be explicitly published', function (): void {
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

});

test('published content can be explicitly unpublished', function (): void {
    actingAs(User::factory()->admin()->create());
    $record = Guide::factory()->create();

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

test('publishing actions require permission to update the guide', function (bool $isDraft, string $action): void {
    actingAs(User::factory()->admin()->create());
    $record = $isDraft ? Guide::factory()->draft()->create() : Guide::factory()->create();
    $page = livewire(EditGuide::class, ['record' => $record->getRouteKey()]);

    Gate::before(fn (User $user, string $ability): ?bool => $ability === 'update' ? false : null);

    $page->assertActionHidden($action);
})->with([
    'publish' => [true, 'publish'],
    'unpublish' => [false, 'unpublish'],
]);
