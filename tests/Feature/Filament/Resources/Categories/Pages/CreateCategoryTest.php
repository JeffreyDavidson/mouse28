<?php

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('an administrator can render the category create page', function (): void {
    actingAs(User::factory()->admin()->create());

    get(CategoryResource::getUrl('create'))
        ->assertOk();
});

test('an administrator can create a category with a description', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Sample Topic',
            'slug' => 'sample-topic',
            'description' => 'Sample description.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::query()->where('slug', 'sample-topic')->sole()->only(['name', 'description']))->toBe([
        'name' => 'Sample Topic',
        'description' => 'Sample description.',
    ]);
});

test('category creation rejects a slug that is not normalized', function (string $slug): void {
    actingAs(User::factory()->admin()->create());

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Sample Topic',
            'slug' => $slug,
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'regex']);

    expect(Category::query()->where('name', 'Sample Topic')->exists())->toBeFalse();
})->with([
    'path traversal' => '../sample-topic',
    'spaces' => 'sample topic',
    'uppercase characters' => 'Sample-Topic',
    'leading hyphen' => '-sample-topic',
    'trailing hyphen' => 'sample-topic-',
    'repeated hyphens' => 'sample--topic',
]);

test('category creation validates the name and slug', function (array $data, array $errors): void {
    Category::factory()->create(['slug' => 'existing-topic']);
    $categories = Category::query()->count();
    actingAs(User::factory()->admin()->create());

    livewire(CreateCategory::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors);

    expect(Category::query()->count())->toBe($categories);
})->with([
    'no name' => [['name' => '', 'slug' => 'sample-topic'], ['name' => 'required']],
    'a name that is too long' => [['name' => str_repeat('a', 256), 'slug' => 'sample-topic'], ['name' => 'max']],
    'no slug' => [['name' => 'Sample Topic', 'slug' => ''], ['slug' => 'required']],
    'a duplicate slug' => [['name' => 'Sample Topic', 'slug' => 'existing-topic'], ['slug' => 'unique']],
]);
