<?php

use App\Enums\PostCategory;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('an incomplete post can be saved as a draft without body content', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => 'sample-draft', 'category' => PostCategory::cases()[0]])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::query()->sole())->body->toBe('')->is_published->toBeFalse();
});

test('post creation validates required fields on the server', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => null, 'slug' => null, 'category' => null])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required', 'slug' => 'required', 'category' => 'required']);
});

test('post creation rejects a duplicate slug', function (): void {
    $post = Post::factory()->create(['slug' => 'existing-post']);
    actingAs(User::factory()->admin()->create());

    livewire(CreatePost::class)
        ->fillForm(['title' => 'Sample draft', 'slug' => $post->slug, 'category' => PostCategory::cases()[0]])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('authenticated user can render the create form', function (): void {
    actingAs(User::factory()->admin()->create());

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('Create Post');
});

test('create form explains editorial requirements', function (): void {
    actingAs(User::factory()->admin()->create());

    get(PostResource::getUrl('create'))
        ->assertOk()
        ->assertSee('use the Publish action')
        ->assertSee('Optional for evergreen posts')
        ->assertSee('Landscape image (1.91:1)');
});
