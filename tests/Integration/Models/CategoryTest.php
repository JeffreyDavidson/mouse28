<?php

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

test('the category factory makes a category with a slug derived from its name', function (): void {
    $category = Category::factory()->create();

    expect($category->name)->not->toBeEmpty()
        ->and($category->slug)->toBe(str($category->name)->slug()->toString())
        ->and($category->description)->toBeNull();
});

test('a category stores its name, slug and description', function (): void {
    $category = Category::query()->create([
        'name' => 'Water Parks',
        'slug' => 'water-parks',
        'description' => 'Slides and lazy rivers.',
    ]);

    expect($category->refresh()->only(['name', 'slug', 'description']))->toBe([
        'name' => 'Water Parks',
        'slug' => 'water-parks',
        'description' => 'Slides and lazy rivers.',
    ]);
});

test('two categories cannot share a slug', function (): void {
    Category::factory()->create(['slug' => 'water-parks']);

    expect(fn () => Category::factory()->create(['slug' => 'water-parks']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('a category lists its posts and its published posts', function (): void {
    $category = Category::factory()->create();
    $published = Post::factory()->for($category)->create();
    $draft = Post::factory()->for($category)->draft()->create();
    $scheduled = Post::factory()->for($category)->scheduled()->create();
    Post::factory()->create();

    expect($category->posts->modelKeys())->toEqualCanonicalizing([$published->id, $draft->id, $scheduled->id])
        ->and($category->publishedPosts->modelKeys())->toBe([$published->id]);
});

test('deleting a category keeps its posts without a category', function (): void {
    $category = Category::factory()->create();
    $post = Post::factory()->for($category)->create();

    $category->delete();

    expect($post->refresh()->category_id)->toBeNull()
        ->and($post->category)->toBeNull()
        ->and($post->category_label)->toBeEmpty();
});

test('category changes are recorded in the editorial log', function (): void {
    $editor = User::factory()->admin()->create();
    actingAs($editor);
    $category = Category::factory()->create(['name' => 'Original name']);

    $category->update(['name' => 'Updated name']);

    $updated = Activity::query()->latest('id')->firstOrFail();
    expect($updated->log_name)->toBe('editorial')
        ->and($updated->causer_id)->toBe($editor->id)
        ->and($updated->subject_id)->toBe($category->id)
        ->and($updated->attribute_changes?->all() ?? [])->toEqual([
            'attributes' => ['name' => 'Updated name'],
            'old' => ['name' => 'Original name'],
        ]);
});
