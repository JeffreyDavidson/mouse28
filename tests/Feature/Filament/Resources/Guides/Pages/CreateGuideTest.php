<?php

use App\Enums\GuideCategory;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Guides\Pages\CreateGuide;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('a guide can be saved as a draft before it is ready for publication', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(CreateGuide::class)
        ->fillForm(['title' => 'Sample guide', 'slug' => 'sample-guide', 'category' => GuideCategory::cases()[0], 'body' => 'Draft text'])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Guide::query()->sole())->body->toBe('Draft text')->is_published->toBeFalse();
});

test('guide creation validates required content on the server', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(CreateGuide::class)
        ->fillForm(['title' => 'Sample guide', 'slug' => 'sample-guide', 'category' => GuideCategory::cases()[0], 'body' => null])
        ->call('create')
        ->assertHasFormErrors(['body' => 'required']);
});

test('guide creation rejects a duplicate slug', function (): void {
    Guide::factory()->create(['slug' => 'existing-guide']);
    actingAs(User::factory()->admin()->create());

    livewire(CreateGuide::class)
        ->fillForm(['title' => 'Sample guide', 'slug' => 'existing-guide', 'category' => GuideCategory::cases()[0], 'body' => 'Draft text'])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

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
