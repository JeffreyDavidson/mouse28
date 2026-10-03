<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('administrators see the categories list', function (): void {
    // Sorted first by name, so it is on the first page next to the standard categories.
    $category = Category::factory()->create(['name' => 'A Sample Topic']);
    actingAs(User::factory()->admin()->create());

    get(CategoryResource::getUrl())
        ->assertOk()
        ->assertSee('A Sample Topic');

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords([$category]);
});

test('non-administrators cannot open the categories list', function (): void {
    actingAs(User::factory()->create());

    get(CategoryResource::getUrl())
        ->assertForbidden();
});

test('the categories list deletes the selected categories', function (): void {
    $categories = Category::factory()->count(2)->create();
    actingAs(User::factory()->admin()->create());

    livewire(ListCategories::class)
        ->selectTableRecords($categories)
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    expect(Category::query()->whereKey($categories->modelKeys())->count())->toBe(0);
});
