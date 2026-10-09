<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Models\Category;
use App\Models\Post;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => actingAsAdmin());

test('an administrator can render the category edit page', function (): void {
    get(CategoryResource::getUrl('edit', ['record' => Category::factory()->create()]))
        ->assertOk();
});

test('an administrator can rename a category and keep its slug', function (): void {
    $category = Category::factory()->create(['slug' => 'sample-topic']);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['name' => 'Renamed Topic', 'description' => 'Updated description.'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->refresh()
        ->only(['name', 'slug', 'description']))->toBe([
            'name' => 'Renamed Topic',
            'slug' => 'sample-topic',
            'description' => 'Updated description.',
        ]);
});

test('category editing rejects the slug of another category', function (): void {
    Category::factory()->create(['slug' => 'existing-topic']);
    $category = Category::factory()->create(['slug' => 'sample-topic']);

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm(['slug' => 'existing-topic'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'unique']);

    expect($category->refresh()
        ->slug)->toBe('sample-topic');
});

test('deleting a category from its edit page keeps its posts uncategorized', function (): void {
    $category = Category::factory()->create();
    $post = Post::factory()
        ->for($category)
        ->create();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(Category::query()->find($category->id))->toBeNull()
        ->and($post->refresh()
            ->category_id)
        ->toBeNull();
});
