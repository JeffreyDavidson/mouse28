<?php

use App\Enums\GuideCategory;
use App\Enums\PublishStatus;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Models\Guide;
use App\Models\User;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('a guide can be saved as a draft before it is ready for publication', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreateGuide::class)
        ->fillForm(['title' => 'Sample guide', 'slug' => 'sample-guide', 'category' => GuideCategory::cases()[0], 'content' => 'Draft text'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Guide::query()->sole())->content->toBe('Draft text')
        ->status->toBe(PublishStatus::Draft);
});

test('guide creation validates required content on the server', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreateGuide::class)
        ->fillForm(['title' => 'Sample guide', 'slug' => 'sample-guide', 'category' => GuideCategory::cases()[0], 'content' => null])
        ->call('create')
        ->assertHasFormErrors(['content' => 'required']);
});

test('guide creation rejects a duplicate slug', function (): void {
    Guide::factory()->create(['slug' => 'existing-guide']);
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreateGuide::class)
        ->fillForm(['title' => 'Sample guide', 'slug' => 'existing-guide', 'category' => GuideCategory::cases()[0], 'content' => 'Draft text'])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('authenticated user can render the create form', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    get(GuideResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Guide')
        ->assertSee('Add a new accessibility guide');
});

test('create form explains editorial requirements', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    get(GuideResource::getUrl('create'))
        ->assertOk()
        ->assertSee('use the Publish action')
        ->assertSee('Landscape image (1.91:1)');
});

test('the guide form credits every author by default, in order', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreateGuide::class)
        ->assertSchemaStateSet(['authors' => User::authors()
            ->pluck('id')
            ->all()]);
});

test('the guide form offers only authors in its author select', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create(['name' => 'Sample Admin']));

    livewire(CreateGuide::class)
        ->assertFormFieldExists('authors', fn (Select $field): bool => $field->isMultiple()
            && collect($field->getOptions())->sort()
                ->values()
                ->all() === ['Cassie Davidson', 'Jeffrey Davidson']);
});

test('guide creation requires at least one author', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreateGuide::class)
        ->fillForm(['title' => 'Sample guide', 'slug' => 'sample-guide', 'category' => GuideCategory::cases()[0], 'content' => 'Draft text', 'authors' => []])
        ->call('create')
        ->assertHasFormErrors(['authors' => 'required']);

    expect(Guide::query()->count())->toBe(0);
});

test('a new guide credits the selected authors in the order they were chosen', function (): void {
    [$jeffreyId, $cassieId] = User::authorIds();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreateGuide::class)
        ->fillForm(['title' => 'Sample guide', 'slug' => 'sample-guide', 'category' => GuideCategory::cases()[0], 'content' => 'Draft text', 'authors' => [$cassieId, $jeffreyId]])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Guide::query()
        ->sole()
        ->authors->modelKeys())->toBe([$cassieId, $jeffreyId])
        ->and(DB::table('guide_user')
            ->orderBy('user_id')
            ->pluck('position', 'user_id')
            ->all())
        ->toEqual([$jeffreyId => 1, $cassieId => 0]);
});
