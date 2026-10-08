<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Post;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Select;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('an incomplete post can be saved as a draft without content', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => 'sample-draft', 'category_id' => Category::factory()
            ->create()
            ->id])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->sole())->content->toBe('')
        ->status->toBe(PublishStatus::Draft);
});

test('post creation validates required fields on the server', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => null, 'slug' => null, 'category_id' => null])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required', 'slug' => 'required', 'category_id' => 'required']);
});

test('post creation rejects a duplicate slug', function (): void {
    $post = Post::factory()->create(['slug' => 'existing-post']);
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => $post->slug, 'category_id' => Category::factory()
            ->create()
            ->id])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('authenticated user can render the create form', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Post');
});

test('create form explains editorial requirements', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('use the Publish action')
        ->assertSee('Optional for evergreen posts')
        ->assertSee('Landscape image (1.91:1)');
});

test('a new post saves every selected related episode', function (): void {
    $episodes = Episode::factory()
        ->count(2)
        ->create();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => 'sample-draft', 'category_id' => Category::factory()
            ->create()
            ->id, 'episodes' => $episodes->modelKeys()])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()
        ->sole()
        ->episodes->modelKeys())->toEqualCanonicalizing($episodes->modelKeys());
});

test('a new post saves the selected category', function (): void {
    $category = Category::factory()->create();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => 'sample-draft', 'category_id' => $category->id])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()
        ->sole()
        ->category_id)->toBe($category->id);
});

test('the post form category select offers every category by name', function (): void {
    Category::factory()->create(['name' => 'Sample Topic']);
    actingAs(User::factory()
        ->admin()
        ->create());

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Sample Topic')
        ->assertSee('Park Accessibility');
});

test('a category can be created inline from the post form', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->callAction(TestAction::make('createOption')->schemaComponent('category_id'), data: [
            'name' => 'Water Parks',
            'slug' => 'water-parks',
        ])
        ->assertHasNoFormErrors()
        ->assertSchemaStateSet(['category_id' => Category::query()
            ->where('slug', 'water-parks')
            ->sole()
            ->id]);

    expect(Category::query()
        ->where('slug', 'water-parks')
        ->sole()
        ->name)->toBe('Water Parks');
});

test('an inline category requires a name and a normalized unique slug', function (array $data, array $errors): void {
    Category::factory()->create(['slug' => 'existing-category']);
    $categories = Category::query()->count();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->callAction(TestAction::make('createOption')->schemaComponent('category_id'), data: $data)
        ->assertHasFormErrors($errors);

    expect(Category::query()->count())->toBe($categories);
})->with([
    'no name' => [['name' => '', 'slug' => 'new-category'], ['name' => 'required']],
    'no slug' => [['name' => 'New category', 'slug' => ''], ['slug' => 'required']],
    'a duplicate slug' => [['name' => 'New category', 'slug' => 'existing-category'], ['slug' => 'unique']],
    'spaces' => [['name' => 'New category', 'slug' => 'new category'], ['slug' => 'regex']],
    'uppercase characters' => [['name' => 'New category', 'slug' => 'New-Category'], ['slug' => 'regex']],
    'repeated hyphens' => [['name' => 'New category', 'slug' => 'new--category'], ['slug' => 'regex']],
    'a name that is too long' => [['name' => str_repeat('a', 256), 'slug' => 'new-category'], ['name' => 'max']],
]);

test('the post form credits every author by default, in order', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->assertSchemaStateSet(['authors' => User::authors()
            ->pluck('id')
            ->all()]);
});

test('the post form offers only authors in its author select', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create(['name' => 'Sample Admin']));

    livewire(CreatePost::class)
        ->assertFormFieldExists('authors', fn (Select $field): bool => $field->isMultiple()
            && collect($field->getOptions())->sort()
                ->values()
                ->all() === ['Cassie Davidson', 'Jeffrey Davidson']);
});

test('post creation requires at least one author', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => 'sample-draft', 'category_id' => Category::factory()
            ->create()
            ->id, 'authors' => []])
        ->call('create')
        ->assertHasFormErrors(['authors' => 'required']);

    expect(Post::query()->count())->toBe(0);
});

test('a new post credits the selected authors in the order they were chosen', function (): void {
    [$jeffreyId, $cassieId] = User::authorIds();
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => 'sample-draft', 'category_id' => Category::factory()
            ->create()
            ->id, 'authors' => [$cassieId, $jeffreyId]])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()
        ->sole()
        ->authors->modelKeys())->toBe([$cassieId, $jeffreyId])
        ->and(DB::table('post_user')
            ->orderBy('user_id')
            ->pluck('position', 'user_id')
            ->all())
        ->toEqual([$jeffreyId => 1, $cassieId => 0]);
});

test('post creation rejects a submitted user who is not an author', function (): void {
    $admin = User::factory()
        ->admin()
        ->create();
    $cassieId = User::authorIds()[1];
    actingAs($admin);

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => 'sample-draft', 'category_id' => Category::factory()
            ->create()
            ->id, 'authors' => [$admin->id, $cassieId]])
        ->call('create')
        ->assertHasFormErrors(['authors.0']);

    expect(Post::query()->count())->toBe(0);
});

test('a new post saves its review notes', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Sample draft',
            'slug' => 'sample-draft',
            'category_id' => Category::factory()
                ->create()
                ->id,
            'review_notes' => 'Needs a source for the new policy.',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()
        ->sole()
        ->review_notes)->toBe('Needs a source for the new policy.');
});
