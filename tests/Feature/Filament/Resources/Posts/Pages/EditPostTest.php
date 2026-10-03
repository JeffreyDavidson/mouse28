<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Post;
use App\Models\User;
use App\Support\ResponsiveArtwork;
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
    actingAs(User::factory()->admin()->create());
    $record = Post::factory()->credited()->create(['slug' => 'original-public-url']);

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->fillForm(['published_at' => null])
        ->call('save')
        ->assertHasNoFormErrors();
    $record->refresh()->update(['status' => PublishStatus::Draft]);

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
    $record = Post::factory()->credited()->create(['slug' => 'permanent-url']);
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
    Date::setTestNow('2026-09-27 12:00:00');
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->draft()->create();

    actingAs($admin);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->assertActionVisible('preview')
        ->assertActionHasUrl('preview', URL::temporarySignedRoute('preview.post', Date::now()->addHours(24), ['post' => $post]))
        ->assertActionShouldOpenUrlInNewTab('preview');
});

test('drafts with their required details can be published while advisory details are missing', function (): void {
    $admin = User::factory()->admin()->create();
    $record = Post::factory()->draft()->create([
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);

    actingAs($admin);

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('publish')
        ->assertNotified('Post published');

    expect($record->refresh()->status)->toBe(PublishStatus::Published)
        ->and($record->published_at)->not->toBeNull();

});

test('published content can be explicitly unpublished', function (): void {
    actingAs(User::factory()->admin()->create());
    $record = Post::factory()->create();

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->callAction('unpublish')
        ->assertNotified('Post unpublished');

    expect($record->refresh()->status)->toBe(PublishStatus::Draft);
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

    expect($post->refresh()->status)->toBe(PublishStatus::Draft);
});

test('editor loads the post authors in byline order', function (): void {
    [$jeffrey, $cassie] = User::authors()->get()->all();
    $record = Post::factory()->draft()->withAuthors($cassie, $jeffrey)->create();
    actingAs(User::factory()->admin()->create());

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->assertSchemaStateSet(['authors' => [$cassie->id, $jeffrey->id]]);
});

test('editor saves the author and category selections', function (): void {
    [$jeffrey, $cassie] = User::authors()->get()->all();
    $record = Post::factory()->draft()->withAuthors($jeffrey, $cassie)->create();
    $category = Category::factory()->create();
    actingAs(User::factory()->admin()->create());

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->assertSchemaStateSet(['category_id' => $record->category_id])
        ->fillForm(['authors' => [$jeffrey->id], 'category_id' => $category->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->authors->modelKeys())->toBe([$jeffrey->id])
        ->and($record->category_id)->toBe($category->id);
});

test('publishing actions disappear when admin access is revoked for the post', function (bool $isDraft, string $action): void {
    $admin = User::factory()->admin()->create();
    actingAs($admin);
    $record = $isDraft ? Post::factory()->draft()->create() : Post::factory()->create();
    $page = livewire(EditPost::class, ['record' => $record->getRouteKey()]);

    $admin->is_admin = false;

    $page->assertActionHidden($action);
})->with([
    'publish' => [true, 'publish'],
    'unpublish' => [false, 'unpublish'],
]);

test('publishing actions follow whether the post is a draft, live, or scheduled', function (PublishStatus $status, ?string $publishedAt, bool $canPublish): void {
    actingAs(User::factory()->admin()->create());
    $post = Post::factory()->create(['status' => $status, 'published_at' => $publishedAt]);

    $page = livewire(EditPost::class, ['record' => $post->getRouteKey()]);

    $canPublish
        ? $page->assertActionVisible('publish')->assertActionHidden('unpublish')
        : $page->assertActionHidden('publish')->assertActionVisible('unpublish');
})->with([
    'draft' => [PublishStatus::Draft, null, true],
    'in review' => [PublishStatus::InReview, null, true],
    'live' => [PublishStatus::Published, '2000-01-01 09:00:00', false],
    'scheduled' => [PublishStatus::Scheduled, '2999-01-01 09:00:00', false],
    'published without a date' => [PublishStatus::Published, null, true],
]);

test('saving after publishing keeps the publication date the action set', function (): void {
    Date::setTestNow('2026-09-25 12:00:00');
    actingAs(User::factory()->admin()->create());
    $post = Post::factory()->draft()->credited()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->callAction('publish')
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->status)->toBe(PublishStatus::Published)
        ->and($post->published_at?->toDateTimeString())->toBe('2026-09-25 12:00:00');
});

test('the official source and its review date are saved together', function (): void {
    actingAs(User::factory()->admin()->create());
    $post = Post::factory()->draft()->credited()->create();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'source_url' => 'https://example.test/official-source',
            'last_reviewed_at' => Date::today()->toDateString(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->refresh()->source_url)->toBe('https://example.test/official-source')
        ->and($post->last_reviewed_at?->isToday())->toBeTrue();
});

test('the official source and its review date are required together', function (array $data, string $missing): void {
    actingAs(User::factory()->admin()->create());
    $post = Post::factory()->draft()->credited()->create(['source_url' => null, 'last_reviewed_at' => null]);

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm($data)
        ->call('save')
        ->assertHasFormErrors([$missing => 'required']);
})->with([
    'source without a review date' => [['source_url' => 'https://example.test/official-source'], 'last_reviewed_at'],
    'review date without a source' => [['last_reviewed_at' => '2026-09-01'], 'source_url'],
]);

test('the edit form loads and saves the post content', function (): void {
    actingAs(User::factory()->admin()->create());
    $record = Post::factory()->draft()->credited()->create(['content' => 'Original post content.']);

    $page = livewire(EditPost::class, ['record' => $record->getRouteKey()]);
    $page->assertSchemaStateSet(['content' => 'Original post content.']);
    $page->fillForm(['content' => "## Updated\n\nNew post content."])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->content)->toBe("## Updated\n\nNew post content.");
});

test('the related episodes select loads the episodes already linked', function (): void {
    $record = Post::factory()->draft()->create();
    $episodes = Episode::factory()->count(2)->create();
    $record->episodes()->attach($episodes);
    actingAs(User::factory()->admin()->create());

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->assertSchemaStateSet(['episodes' => array_map(strval(...), $episodes->modelKeys())]);
});

test('editor replaces the related episodes with the selected ones', function (): void {
    $record = Post::factory()->draft()->credited()->create();
    $record->episodes()->attach(Episode::factory()->create());
    $selected = Episode::factory()->count(2)->create();
    actingAs(User::factory()->admin()->create());

    livewire(EditPost::class, ['record' => $record->getRouteKey()])
        ->fillForm(['episodes' => $selected->modelKeys()])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($record->refresh()->episodes->modelKeys())->toEqualCanonicalizing($selected->modelKeys());
});
