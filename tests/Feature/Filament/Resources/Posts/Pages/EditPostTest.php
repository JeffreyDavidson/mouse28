<?php

use App\Enums\ContentAuthor;
use App\Enums\PostCategory;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Support\ResponsiveArtwork;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('previously published URLs stay locked after clearing the date and unpublishing', function (): void {
    actingAs(User::factory()->admin()->create());
    $record = Post::factory()->create(['slug' => 'original-public-url']);

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => null])
        ->call('save')
        ->assertHasNoFormErrors();
    $record->refresh()->update(['is_published' => false]);

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->slug)->toBe('original-public-url');
});

test('authenticated user can render the edit form', function (): void {
    $post = Post::factory()->draft()->create();

    actingAs(User::factory()->admin()->create());

    get(PostResource::getUrl('edit', ['record' => $post]))
        ->assertOk()
        ->assertSee($post->title)
        ->assertSee('Save changes');
});

test('explicit artwork action generates only the saved record cover', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('posts/cover.png', UploadedFile::fake()->image('cover.png', 1000, 800)->getContent());
    Storage::disk('public')->put('posts/other.png', UploadedFile::fake()->image('other.png', 1200, 800)->getContent());
    actingAs(User::factory()->admin()->create());
    $record = Post::factory()->create(['cover_image' => 'posts/cover.png']);
    $other = Post::factory()->create(['cover_image' => 'posts/other.png']);

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('generateArtwork')
        ->assertNotified('Responsive artwork prepared');

    expect(ResponsiveArtwork::srcset($record->cover_image, square: false))->not->toBeNull()
        ->and(ResponsiveArtwork::srcset($other->cover_image, square: false))->toBeNull();
});

test('artwork generation reports an unavailable source', function (): void {
    Storage::fake('public');
    actingAs(User::factory()->admin()->create());
    $record = Post::factory()->create(['cover_image' => 'posts/missing.png']);

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('generateArtwork')
        ->assertNotified('Artwork generation failed');
});

test('published URLs cannot be changed by submitted editor state', function (): void {
    $record = Post::factory()->create(['slug' => 'permanent-url']);
    actingAs(User::factory()->admin()->create());

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->fillForm(['slug' => 'replacement-url'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->slug)->toBe('permanent-url');
});

test('draft slugs reject characters that cannot form public routes', function (): void {
    $record = Post::factory()->draft()->create();
    actingAs(User::factory()->admin()->create());

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->fillForm(['slug' => 'Invalid/URL'])
        ->call('save')
        ->assertHasFormErrors(['slug' => 'regex']);
});

test('edit page offers a draft preview', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create();

    actingAs($admin);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', route('preview.posts', $post))
        ->assertActionShouldOpenUrlInNewTab('preview');
});

test('ready drafts can be explicitly published', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Post::factory()->draft()->create([
        'cover_image' => 'posts/complete.jpg',
        'meta_title' => 'Complete post title',
        'meta_description' => 'Complete post description',
    ]);

    actingAs($admin);

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Post published');

    expect($record->refresh()->is_published)->toBeTrue()
        ->and($record->published_at)->not->toBeNull();

});

test('published content can be explicitly unpublished', function (): void {
    actingAs(User::factory()->admin()->create());
    $record = Post::factory()->create();

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Post unpublished');

    expect($record->refresh()->is_published)->toBeFalse();
});

test('deleted content leaves the public site and can be restored by an administrator', function (): void {
    // Arrange
    $admin = User::factory()->admin()->create();
    $record = Post::factory()->create();

    $record->delete();

    get(route('blog.show', $record))->assertNotFound();

    actingAs($admin);

    $page = livewire(EditPost::class, ['record' => $record->getRouteKey()]);

    // Act
    $page->callAction('restore');
    $response = get(route('blog.show', $record));

    // Assert
    $page->assertNotified();
    $this->assertNotSoftDeleted($record);
    $response->assertOk();
});

test('publishing is blocked until editorial requirements are complete', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create([
        'excerpt' => null,
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);

    actingAs($admin);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Post is not ready to publish');

    expect($post->refresh()->is_published)->toBeFalse();
});

test('editor saves author and category selections as enums', function (): void {
    $record = Post::factory()->draft()->create();
    actingAs(User::factory()->admin()->create());

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->fillForm(['author' => 'jeffrey', 'category' => 'food-reviews'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->author)->toBe(ContentAuthor::Jeffrey)
        ->and($record->category)->toBe(PostCategory::FoodReviews);
});

test('publishing actions require permission to update the post', function (bool $isDraft, string $action): void {
    actingAs(User::factory()->admin()->create());
    $record = $isDraft ? Post::factory()->draft()->create() : Post::factory()->create();
    $page = livewire(EditPost::class, ['record' => $record->getRouteKey()]);

    Gate::before(fn (User $user, string $ability): ?bool => $ability === 'update' ? false : null);

    $page->assertActionHidden($action);
})->with([
    'publish' => [true, 'publish'],
    'unpublish' => [false, 'unpublish'],
]);
