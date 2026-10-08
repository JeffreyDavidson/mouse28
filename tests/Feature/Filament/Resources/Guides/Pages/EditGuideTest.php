<?php

use App\Enums\GuideCategory;
use App\Enums\PublishStatus;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\EditGuide;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('previously published URLs stay locked after clearing the date and unpublishing', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Guide::factory()
        ->credited()
        ->create(['slug' => 'original-public-url']);

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => null])
        ->call('save')
        ->assertHasNoFormErrors();
    $record->refresh()
        ->update(['status' => PublishStatus::Draft]);

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()
        ->slug)->toBe('original-public-url');
});

test('authenticated user can render the edit form', function (): void {
    $guide = Guide::factory()
        ->draft()
        ->create();

    actingAs(User::factory()
        ->admin()
        ->create());

    get(GuideResource::getUrl('edit', ['record' => $guide]))
        ->assertOk()
        ->assertSee($guide->title)
        ->assertSee('Save changes');
});

test('published URLs cannot be changed by submitted editor state', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $published = Guide::factory()
        ->credited()
        ->create(['slug' => 'permanent-url']);

    livewire(EditGuide::class, ['record' => $published->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($published->refresh()
        ->slug)->toBe('permanent-url');

});

test('draft slugs reject characters that cannot form public routes', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $draft = Guide::factory()
        ->draft()
        ->create();

    livewire(EditGuide::class, ['record' => $draft->getRouteKey()])
        ->fillForm(['slug' => 'Invalid/URL'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);
});

test('edit page offers a draft preview', function (): void {
    Date::setTestNow('2026-09-27 12:00:00');
    $admin = User::factory()
        ->admin()
        ->create();
    $guide = Guide::factory()
        ->draft()
        ->create();

    actingAs($admin);

    livewire(EditGuide::class, ['record' => $guide->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', URL::temporarySignedRoute('preview.guide', Date::now()->addHours(24), ['guide' => $guide]))
        ->assertActionShouldOpenUrlInNewTab('preview');
});

test('drafts with their required details can be published while advisory details are missing', function (): void {
    $admin = User::factory()
        ->admin()
        ->create();
    $record = Guide::factory()
        ->draft()
        ->credited()
        ->create([
            'featured_image_path' => null,
        ]);

    actingAs($admin);

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Guide published');

    expect($record->refresh()
        ->status)->toBe(PublishStatus::Published)
        ->and($record->published_at)
        ->not->toBeNull();

});

test('published content can be explicitly unpublished', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Guide::factory()->create();

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Guide unpublished');

    expect($record->refresh()
        ->status)->toBe(PublishStatus::Draft);
});

test('deleted content leaves the public site and can be restored by an administrator', function (): void {
    // Arrange
    $admin = User::factory()
        ->admin()
        ->create();
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

test('editor loads the guide authors in byline order', function (): void {
    [$jeffrey, $cassie] = User::authors()
        ->get()
        ->all();
    $record = Guide::factory()
        ->draft()
        ->withAuthors($cassie, $jeffrey)
        ->create();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->assertSchemaStateSet(['authors' => [$cassie->id, $jeffrey->id]]);
});

test('editor saves the author selection and the category as an enum', function (): void {
    [$jeffrey, $cassie] = User::authors()
        ->get()
        ->all();
    $record = Guide::factory()
        ->draft()
        ->withAuthors($jeffrey, $cassie)
        ->create();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->fillForm(['authors' => [$cassie->id], 'category' => 'family-planning'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()
        ->authors->modelKeys())->toBe([$cassie->id])
        ->and($record->category)
        ->toBe(GuideCategory::FamilyPlanning);
});

test('publishing actions disappear when admin access is revoked for the guide', function (bool $isDraft, string $action): void {
    $admin = User::factory()
        ->admin()
        ->create();
    actingAs($admin);
    $record = $isDraft ? Guide::factory()
        ->draft()
        ->create() : Guide::factory()->create();
    $page = livewire(EditGuide::class, ['record' => $record->getRouteKey()]);

    $admin->is_admin = false;

    $page->assertActionHidden($action);
})->with([
    'publish' => [true, 'publish'],
    'unpublish' => [false, 'unpublish'],
]);

test('the edit form loads and saves the guide content', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Guide::factory()
        ->draft()
        ->credited()
        ->create(['content' => 'Original guide content.']);

    $page = livewire(EditGuide::class, ['record' => $record->getRouteKey()]);
    $page->assertSchemaStateSet(['content' => 'Original guide content.']);
    $page->fillForm(['content' => "## Updated\n\nNew guide content."])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()
        ->content)->toBe("## Updated\n\nNew guide content.");
});

test('a replaced cover is stored under guides with variants and the previous file is removed', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('guides/previous.png', UploadedFile::fake()
        ->image('previous.png', 1000, 525)
        ->getContent());
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Guide::factory()
        ->credited()
        ->create(['featured_image_path' => 'guides/previous.png']);

    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->fillForm(['featured_image_path' => [UploadedFile::fake()->image('cover.png', 1000, 525)]])
        ->call('save')
        ->assertHasNoFormErrors();

    $path = (string) $record->refresh()
        ->featured_image_path;
    expect($path)->toStartWith('guides/')
        ->not->toBe('guides/previous.png');
    Storage::disk('public')->assertExists([$path, 'guides/responsive/'.pathinfo($path, PATHINFO_FILENAME).'-480.webp']);
    Storage::disk('public')->assertMissing(['guides/previous.png', 'guides/responsive/previous-480.webp']);
});

test('the SEO section saves its title and description to the SEO row', function (): void {
    // Arrange
    actingAs(User::factory()
        ->admin()
        ->create());
    $record = Guide::factory()
        ->credited()
        ->create();

    // Act
    livewire(EditGuide::class, ['record' => $record->getRouteKey()])
        ->fillForm([
            'seo.title' => 'A saved SEO title',
            'seo.description' => 'A saved SEO description.',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    // Assert
    expect($record->refresh()
        ->seo->title)->toBe('A saved SEO title')
        ->and($record->seo->description)
        ->toBe('A saved SEO description.');
});
