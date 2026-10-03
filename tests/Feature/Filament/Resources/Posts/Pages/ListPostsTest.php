<?php

use App\Enums\PublishStatus;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('authenticated user can render the resource listing', function (): void {
    actingAs(User::factory()->admin()->create());

    get(PostResource::getUrl())
        ->assertOk()
        ->assertSee('Blog Posts');
});

test('content table shows readiness and the persisted publish status', function (): void {
    $admin = User::factory()->admin()->create();
    Post::factory()->create(['status' => PublishStatus::InReview]);

    actingAs($admin);

    get(PostResource::getUrl())
        ->assertOk()
        ->assertSee('Readiness')
        ->assertSee('In Review');
});

test('draft and scheduled tabs filter posts', function (): void {
    $draft = Post::factory()->draft()->create();
    $inReview = Post::factory()->create(['status' => PublishStatus::InReview]);
    $scheduled = Post::factory()->scheduled()->create();
    actingAs(User::factory()->admin()->create());

    livewire(ListPosts::class)
        ->set('activeTab', 'drafts')
        ->assertCanSeeTableRecords([$draft, $inReview])
        ->assertCanNotSeeTableRecords([$scheduled])
        ->set('activeTab', 'scheduled')
        ->assertCanSeeTableRecords([$scheduled])
        ->assertCanNotSeeTableRecords([$draft]);
});

test('header does not count scheduled posts as published', function (): void {
    // Arrange
    Post::factory()->create();
    Post::factory()->scheduled()->create();
    Post::factory()->draft()->create();
    Post::factory()->create(['status' => PublishStatus::InReview]);
    actingAs(User::factory()->admin()->create());

    // Act
    $page = livewire(ListPosts::class);
    $component = $page->instance();
    $header = $component->getHeader();

    // Assert
    expect($header?->getData())
        ->toMatchArray([
            'published' => 1,
            'drafts' => 2,
        ]);
});

test('the review due filter and source review column show sources that need review', function (): void {
    actingAs(User::factory()->admin()->create());
    $due = Post::factory()->create(['source_url' => 'https://example.test/source', 'last_reviewed_at' => null]);
    $current = Post::factory()->create(['source_url' => 'https://example.test/source', 'last_reviewed_at' => today()]);

    livewire(ListPosts::class)
        ->filterTable('review_due')
        ->assertCanSeeTableRecords([$due])
        ->assertCanNotSeeTableRecords([$current])
        ->assertTableColumnExists('source_review_status');
});

test('the posts table shows each category name as a badge', function (): void {
    $post = Post::factory()->for(Category::factory()->create(['name' => 'Sample Topic']))->create();
    actingAs(User::factory()->admin()->create());

    livewire(ListPosts::class)
        ->assertTableColumnStateSet('category.name', 'Sample Topic', $post)
        ->assertSee('Sample Topic');
});

test('the posts table filters by category', function (): void {
    $category = Category::factory()->create();
    $inCategory = Post::factory()->for($category)->create();
    $elsewhere = Post::factory()->create();
    $uncategorized = Post::factory()->create(['category_id' => null]);
    actingAs(User::factory()->admin()->create());

    livewire(ListPosts::class)
        ->filterTable('category', $category->id)
        ->assertCanSeeTableRecords([$inCategory])
        ->assertCanNotSeeTableRecords([$elsewhere, $uncategorized]);
});

test('the posts table has no column for the removed single episode relation', function (): void {
    actingAs(User::factory()->admin()->create());

    livewire(ListPosts::class)
        ->assertTableColumnDoesNotExist('episode.title');
});

test('the posts table shows each author name in byline order', function (): void {
    [$jeffrey, $cassie] = User::authors()->get()->all();
    $post = Post::factory()->withAuthors($cassie, $jeffrey)->create();
    actingAs(User::factory()->admin()->create());

    livewire(ListPosts::class)
        ->assertTableColumnStateSet('authors.name', ['Cassie Davidson', 'Jeffrey Davidson'], $post);
});

test('the posts table filters by author', function (): void {
    [$jeffrey, $cassie] = User::authors()->get()->all();
    $byJeffrey = Post::factory()->withAuthors($jeffrey)->create();
    $byBoth = Post::factory()->withAuthors($jeffrey, $cassie)->create();
    $byCassie = Post::factory()->withAuthors($cassie)->create();
    $uncredited = Post::factory()->create();
    actingAs(User::factory()->admin()->create());

    livewire(ListPosts::class)
        ->filterTable('authors', $jeffrey->id)
        ->assertCanSeeTableRecords([$byJeffrey, $byBoth])
        ->assertCanNotSeeTableRecords([$byCassie, $uncredited]);
});

test('global search finds posts by author name', function (): void {
    [$jeffrey, $cassie] = User::authors()->get()->all();
    $byCassie = Post::factory()->withAuthors($cassie)->create(['title' => 'Sample post one']);
    Post::factory()->withAuthors($jeffrey)->create(['title' => 'Sample post two']);
    actingAs(User::factory()->admin()->create());

    expect(PostResource::getGlobalSearchResults('Cassie')->pluck('title')->all())
        ->toBe([$byCassie->title]);
});

test('the posts table names a post without authors', function (): void {
    Post::factory()->create();
    actingAs(User::factory()->admin()->create());

    livewire(ListPosts::class)
        ->assertSee('No authors');
});
