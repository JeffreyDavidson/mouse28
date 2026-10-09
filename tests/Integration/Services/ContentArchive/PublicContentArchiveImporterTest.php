<?php

use App\Enums\GuideCategory;
use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use App\Services\ContentArchive\PublicContentArchiveExporter;
use App\Services\ContentArchive\PublicContentArchiveImporter;
use App\Services\ContentArchive\PublicContentArchiveRelations;
use App\Services\ContentArchive\PublicContentArchiveSchema;
use App\Services\ContentArchive\PublicContentArchiveValidator;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

covers(
    PublicContentArchiveImporter::class,
    PublicContentArchiveRelations::class,
    PublicContentArchiveSchema::class,
    PublicContentArchiveValidator::class,
);

pest()->use(RefreshDatabase::class);

test('content tags round trip and replace stale tags while legacy archives preserve them', function (): void {
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $episode = Episode::factory()->create();
    foreach ([$post, $guide, $episode] as $record) {
        $record->syncTagsWithType(['Accessibility', 'Family'], 'content');
    }
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    foreach ([$post, $guide, $episode] as $record) {
        $record->syncTagsWithType(['Stale'], 'content');
    }

    $service->import($archive);

    foreach ([$post, $guide, $episode] as $record) {
        expect($record->refresh()
            ->tagsWithType('content')
            ->pluck('name')
            ->sort()
            ->values()
            ->all())
            ->toBe(['Accessibility', 'Family']);
    }
    unset($archive['posts'][0]['tags']);
    $archive['guides'][0]['tags'] = [];

    $service->import($archive);

    expect($post->refresh()
        ->tagsWithType('content'))->toHaveCount(2)
        ->and($guide->refresh()
            ->tagsWithType('content'))
        ->toBeEmpty();
});

test('invalid imported attributes leave all existing records unchanged', function (string $field, mixed $value): void {
    $post = Post::factory()->create(['title' => 'Original']);
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['posts'][0]['title'] = 'Changed';
    $archive['posts'][0][$field] = $value;

    expect(fn () => $service->import($archive))->toThrow(InvalidArgumentException::class)
        ->and($post->refresh()
            ->title)
        ->toBe('Original');
})->with([
    'unsafe media' => ['featured_image_path', '../private.jpg'],
    'invalid date' => ['published_at', 'not a date'],
    'invalid slug' => ['slug', 'folder/story'],
    'invalid tags' => ['tags', [42]],
    'invalid URL' => ['source_url', 'javascript:alert(1)'],
    'invalid media type' => ['featured_image_path', []],
    'invalid relation type' => ['episode_slug', []],
    'invalid relation list' => ['episode_slugs', 'example-episode'],
    'invalid relation slug' => ['episode_slugs', ['Not A Slug']],
    'null content' => ['content', null],
]);

test('an older archive with an unsafe cover_image is rejected before importing', function (): void {
    $post = Post::factory()->create(['title' => 'Original']);
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['posts'][0]['title'] = 'Changed';
    unset($archive['posts'][0]['featured_image_path']);
    $archive['posts'][0]['cover_image'] = '../private.jpg';

    expect(fn () => $service->import($archive))->toThrow(InvalidArgumentException::class)
        ->and($post->refresh()
            ->title)
        ->toBe('Original');
});

test('archives export stored media paths and import them under either key name', function (): void {
    $post = Post::factory()->create();
    $guide = Guide::factory()->create();
    $episode = Episode::factory()->create();
    foreach ([$post, $guide, $episode] as $record) {
        $record->forceFill(['featured_image_path' => "{$record->getTable()}/exported.webp"])
            ->saveQuietly();
    }
    primaryPodcast()->forceFill(['cover_image_path' => 'podcast/exported.webp'])
        ->saveQuietly();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();

    expect($archive['posts'][0]['featured_image_path'])->toBe('posts/exported.webp')
        ->and($archive['guides'][0]['featured_image_path'])
        ->toBe('guides/exported.webp')
        ->and($archive['episodes'][0]['featured_image_path'])
        ->toBe('episodes/exported.webp')
        ->and($archive['podcast']['cover_image_path'] ?? null)
        ->toBe('podcast/exported.webp')
        ->and($archive['posts'][0])
        ->not->toHaveKey('cover_image');

    // An older archive carries only `cover_image`; a newer key wins when both are present.
    unset($archive['posts'][0]['featured_image_path'], $archive['podcast']['cover_image_path']);
    $archive['posts'][0]['cover_image'] = 'posts/legacy.webp';
    $archive['podcast']['cover_image'] = 'podcast/legacy.webp';
    $archive['guides'][0]['cover_image'] = 'guides/ignored.webp';

    $service->import($archive);

    expect($post->refresh()
        ->featured_image_path)->toBe('posts/legacy.webp')
        ->and($guide->refresh()
            ->featured_image_path)
        ->toBe('guides/exported.webp')
        ->and($episode->refresh()
            ->featured_image_path)
        ->toBe('episodes/exported.webp')
        ->and(primaryPodcast()->cover_image_path)
        ->toBe('podcast/legacy.webp');
});

test('sync refuses unpublished identity collisions without changing content', function (PostFactory|GuideFactory|EpisodeFactory $factory, string $state): void {
    // Arrange
    $record = $factory->createOne();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $record->update($state === 'draft'
        ? ['status' => PublishStatus::Draft, 'title' => 'Local work']
        : ['published_at' => now()->addWeek(), 'title' => 'Local work']);

    $exception = null;

    // Act
    try {
        $service->sync($archive);
    } catch (InvalidArgumentException $caught) {
        $exception = $caught;
    }

    // Assert
    expect($exception)->toBeInstanceOf(InvalidArgumentException::class)
        ->and($record->refresh()
            ->title)
        ->toBe('Local work');
})->with([
    'posts' => fn () => Post::factory(),
    'guides' => fn () => Guide::factory(),
    'episodes' => fn () => Episode::factory(),
])->with(['draft', 'scheduled']);

test('public archives retain string values and restore enum backed content', function (): void {
    $post = Post::factory()
        ->inCategory('disney-tips')
        ->create();
    $guide = Guide::factory()->create(['category' => GuideCategory::Accessibility]);
    $service = app(PublicContentArchiveImporter::class);

    $archive = app(PublicContentArchiveExporter::class)->export();

    expect($archive['posts'][0]['category'])->toBe('disney-tips')
        ->and($archive['posts'][0]['category_name'])
        ->toBe('Disney Tips')
        ->and($archive['posts'][0])
        ->not->toHaveKey('category_id')
        ->and($archive['guides'][0]['category'])
        ->toBe('accessibility');

    $post->delete();
    $guide->delete();

    $serializedArchive = json_encode($archive, JSON_THROW_ON_ERROR);
    $decodedArchive = json_decode($serializedArchive, true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($decodedArchive)) {
        throw new UnexpectedValueException('The serialized archive must decode to an array.');
    }

    $importArchive = [];
    foreach ($decodedArchive as $key => $value) {
        if (! is_string($key)) {
            throw new UnexpectedValueException('The decoded archive keys must be strings.');
        }

        $importArchive[$key] = $value;
    }

    $service->import($importArchive);
    $post->refresh();
    $guide->refresh();

    expect($post->trashed())->toBeFalse()
        ->and($post->category?->slug)
        ->toBe('disney-tips')
        ->and($guide->trashed())
        ->toBeFalse()
        ->and($guide->category)
        ->toBe(GuideCategory::Accessibility);
});

test('archive validation rejects a non-string slug before importing any records', function (): void {
    $post = Post::factory()->create(['title' => 'Original title']);
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['posts'][0]['title'] = 'Changed by import';
    $archive['posts'][0]['slug'] = ['invalid-slug'];

    expect(fn () => $service->import($archive))
        ->toThrow(InvalidArgumentException::class, 'invalid posts');

    $post->refresh();

    expect($post->title)->toBe('Original title');
});

test('invalid archive category values roll back earlier imported records', function (string $field, mixed $value): void {
    $first = Post::factory()->create(['title' => 'Original first title', 'published_at' => now()->subDays(2)]);
    Post::factory()->create();
    $categories = Category::query()->count();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['posts'][0]['title'] = 'Changed by import';
    $archive['posts'][1][$field] = $value;

    expect(fn () => $service->import($archive))->toThrow(InvalidArgumentException::class);

    $first->refresh();

    expect($first->title)->toBe('Original first title')
        ->and(Category::query()->count())
        ->toBe($categories);
})->with([
    'a category that is not a slug' => ['category', 'Not A Category'],
    'a category that is not a string' => ['category', ['disney-tips']],
    'a category slug that is too long' => ['category', str_repeat('a', 256)],
    'a category name that is not a string' => ['category_name', ['Disney Tips']],
    'a category name that is too long' => ['category_name', str_repeat('a', 256)],
]);

test('archive import creates a category the local site does not have yet', function (): void {
    $post = Post::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['posts'][0]['category'] = 'water-parks';
    $archive['posts'][0]['category_name'] = 'Water Parks & Slides';

    $service->import($archive);

    $category = Category::query()
        ->where('slug', 'water-parks')
        ->sole();
    expect($category->name)->toBe('Water Parks & Slides')
        ->and($category->description)
        ->toBeNull()
        ->and($post->refresh()
            ->category_id)
        ->toBe($category->id);
});

test('archive import names a new category from its slug when the archive has no name', function (bool $withName, ?string $name): void {
    $post = Post::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['posts'][0]['category'] = 'water-parks';
    unset($archive['posts'][0]['category_name']);

    if ($withName) {
        $archive['posts'][0]['category_name'] = $name;
    }

    $service->import($archive);

    expect(Category::query()
        ->where('slug', 'water-parks')
        ->sole()
        ->name)->toBe('Water Parks')
        ->and($post->refresh()
            ->category?->slug)
        ->toBe('water-parks');
})->with([
    'an older archive without the name' => [false, null],
    'a null name' => [true, null],
    'an empty name' => [true, ''],
]);

test('archive import links an existing category by slug and keeps its local name', function (): void {
    $post = Post::factory()->create();
    $local = Category::query()
        ->where('slug', 'general')
        ->sole();
    $local->update(['name' => 'Local General']);
    $categories = Category::query()->count();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['posts'][0]['category'] = 'general';
    $archive['posts'][0]['category_name'] = 'General';

    $service->import($archive);

    expect($post->refresh()
        ->category_id)->toBe($local->id)
        ->and($local->refresh()
            ->name)
        ->toBe('Local General')
        ->and(Category::query()->count())
        ->toBe($categories);
});

test('archive import clears the category of a post exported without one', function (?string $slug): void {
    $post = Post::factory()->create();
    $categories = Category::query()->count();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['posts'][0]['category'] = $slug;
    $archive['posts'][0]['category_name'] = null;

    $service->import($archive);

    expect($post->refresh()
        ->category_id)->toBeNull()
        ->and(Category::query()->count())
        ->toBe($categories);
})->with([
    'a null category' => [null],
    'an empty category' => [''],
]);

test('archive import leaves the category alone when the record carries none', function (): void {
    $post = Post::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    unset($archive['posts'][0]['category'], $archive['posts'][0]['category_name']);

    $service->import($archive);

    expect($post->refresh()
        ->category_id)->toBe($post->category_id);
});

test('archive validation requires the guide category', function (): void {
    Guide::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive['guides'][0]['category'] = null;

    expect(fn () => $service->import($archive))
        ->toThrow(InvalidArgumentException::class, 'category field is required');
});

test('archives without a status field import as published content', function (PostFactory|GuideFactory|EpisodeFactory $factory): void {
    $record = $factory->createOne();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $record->forceDelete();

    $service->import($archive);

    $imported = $record::query()->sole();
    expect([...$archive['posts'], ...$archive['guides'], ...$archive['episodes']])->each->not->toHaveKeys(['status', 'is_published'])
        ->and($imported->publishStatus())
        ->toBe(PublishStatus::Published)
        ->and($imported->isPublished())
        ->toBeTrue();
})->with([
    'posts' => fn () => Post::factory(),
    'guides' => fn () => Guide::factory(),
    'episodes' => fn () => Episode::factory(),
]);

test('archive import restores the written content from either archive format', function (PostFactory|GuideFactory $factory, string $type, string $key): void {
    $record = $factory->createOne(['content' => 'Exported content.']);
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive = withFirstArchivedRecord($archive, $type, [$key => "## Café ✨\n\nImported content."], without: ['content']);
    $record->forceDelete();

    $service->import($archive);

    $imported = $record::query()->sole();
    expect($imported->getAttribute('content'))->toBe("## Café ✨\n\nImported content.");
})->with('archived written content')
    ->with([
        'current content key' => ['content'],
        'older body key' => ['body'],
    ]);

test('archive import prefers the content key when an archive carries both keys', function (PostFactory|GuideFactory $factory, string $type): void {
    $record = $factory->createOne();
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive = withFirstArchivedRecord($archive, $type, ['content' => 'Current content.', 'body' => 'Older body.']);
    $record->forceDelete();

    $service->import($archive);

    expect($record::query()->sole()
        ->getAttribute('content'))->toBe('Current content.');
})->with('archived written content');

test('archive import rejects a record without any written content', function (PostFactory|GuideFactory $factory, string $type): void {
    $factory->createOne(['title' => 'Original']);
    $service = app(PublicContentArchiveImporter::class);
    $archive = app(PublicContentArchiveExporter::class)->export();
    $archive = withFirstArchivedRecord($archive, $type, ['title' => 'Changed'], without: ['content']);

    expect(fn () => $service->import($archive))->toThrow(InvalidArgumentException::class)
        ->and($factory->newModel()
            ->newQuery()
            ->sole()
            ->getAttribute('title'))
        ->toBe('Original');
})->with('archived written content');

test('archive import restores related episodes from either archive format', function (string $key, string|array $value, array $expected): void {
    Episode::factory()->create(['slug' => 'first-episode']);
    Episode::factory()->create(['slug' => 'second-episode']);
    $post = Post::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', [$key => $value], without: ['episode_slugs']);
    $post->forceDelete();

    $service->import($archive);

    expect(Post::query()
        ->sole()
        ->episodes->pluck('slug')
        ->sort()
        ->values()
        ->all())->toBe($expected);
})->with([
    'current episode_slugs list' => ['episode_slugs', ['first-episode', 'second-episode'], ['first-episode', 'second-episode']],
    'older single episode_slug' => ['episode_slug', 'first-episode', ['first-episode']],
]);

test('archive import prefers the episode_slugs list when an archive carries both keys', function (): void {
    Episode::factory()->create(['slug' => 'listed-episode']);
    Episode::factory()->create(['slug' => 'older-episode']);
    $post = Post::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', ['episode_slugs' => ['listed-episode'], 'episode_slug' => 'older-episode']);
    $post->forceDelete();

    $service->import($archive);

    expect(Post::query()
        ->sole()
        ->episodes->pluck('slug')
        ->all())->toBe(['listed-episode']);
});

test('archive import replaces the related episodes of an existing post', function (): void {
    $kept = Episode::factory()->create(['slug' => 'kept-episode']);
    $post = Post::factory()->create();
    $post->episodes()
        ->attach([$kept->id, Episode::factory()
            ->create()
            ->id]);
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', ['episode_slugs' => ['kept-episode']]);

    $service->import($archive);

    expect($post->refresh()
        ->episodes->modelKeys())->toBe([$kept->id]);
});

/** @return array<mixed> the author names credited on the archived record, in byline order */
function importedAuthorNames(PostFactory|GuideFactory $factory): array
{
    return $factory->newModel()
        ->newQuery()
        ->sole()
        ->authors->pluck('name')
        ->all();
}

test('archive import credits existing authors by name in archive order and ignores unknown names', function (PostFactory|GuideFactory $factory, string $type): void {
    $factory->createOne();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), $type, ['authors' => ['Cassie Davidson', 'Someone Unknown', 'Jeffrey Davidson']]);
    $users = User::query()->count();

    $service->import($archive);

    expect(importedAuthorNames($factory))->toBe(['Cassie Davidson', 'Jeffrey Davidson'])
        ->and(User::query()->count())
        ->toBe($users);
})->with('archived written content');

test('archive import credits the first of two authors who share a name', function (): void {
    $first = User::factory()
        ->author()
        ->create(['name' => 'Sample Author']);
    User::factory()
        ->author()
        ->create(['name' => 'Sample Author']);
    Post::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', ['authors' => ['Sample Author']]);

    $service->import($archive);

    expect(Post::query()
        ->sole()
        ->authors->modelKeys())->toBe([$first->id]);
});

test('archive import clears the authors of content archived with a null author list', function (): void {
    Post::factory()
        ->credited()
        ->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', ['authors' => null]);

    $service->import($archive);

    expect(importedAuthorNames(Post::factory()))->toBeEmpty();
});

test('archive import never credits a same-named user who is not an author', function (): void {
    User::factory()
        ->admin()
        ->create(['name' => 'Sample Admin']);
    Post::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', ['authors' => ['Sample Admin']]);

    $service->import($archive);

    expect(importedAuthorNames(Post::factory()))->toBeEmpty();
});

test('archive import maps an older author value to the author users', function (PostFactory|GuideFactory $factory, string $type, ?string $legacyAuthor, array $names): void {
    $factory->createOne();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), $type, ['author' => $legacyAuthor], without: ['authors']);

    $service->import($archive);

    expect(importedAuthorNames($factory))->toBe($names);
})->with('archived written content')
    ->with([
        'jeffrey' => ['jeffrey', ['Jeffrey Davidson']],
        'cassie' => ['cassie', ['Cassie Davidson']],
        'both, Jeffrey first' => ['both', ['Jeffrey Davidson', 'Cassie Davidson']],
        'no author' => [null, []],
    ]);

test('archive import prefers the authors list when an archive carries both keys', function (): void {
    Post::factory()->create();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', ['authors' => ['Cassie Davidson'], 'author' => 'jeffrey']);

    $service->import($archive);

    expect(importedAuthorNames(Post::factory()))->toBe(['Cassie Davidson']);
});

test('archive import replaces the authors of existing content when the archive lists them', function (PostFactory|GuideFactory $factory, string $type, array $archivedAuthors, array $names): void {
    [$jeffrey] = User::authors()
        ->get()
        ->all();
    $factory->withAuthors($jeffrey)
        ->createOne();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), $type, ['authors' => $archivedAuthors]);

    $service->import($archive);

    expect(importedAuthorNames($factory))->toBe($names);
})->with('archived written content')
    ->with([
        'another author' => [['Cassie Davidson'], ['Cassie Davidson']],
        'no authors' => [[], []],
    ]);

test('archive import leaves the authors alone when the record carries none', function (PostFactory|GuideFactory $factory, string $type): void {
    [$jeffrey, $cassie] = User::authors()
        ->get()
        ->all();
    $factory->withAuthors($cassie, $jeffrey)
        ->createOne();
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), $type, [], without: ['authors']);

    $service->import($archive);

    expect(importedAuthorNames($factory))->toBe(['Cassie Davidson', 'Jeffrey Davidson']);
})->with('archived written content');

test('archive validation rejects an unknown older author value or a malformed author list', function (string $field, mixed $value): void {
    Post::factory()->create(['title' => 'Original title']);
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', ['title' => 'Changed by import', $field => $value]);

    expect(fn () => $service->import($archive))->toThrow(InvalidArgumentException::class)
        ->and(Post::query()
            ->sole()
            ->title)
        ->toBe('Original title');
})->with([
    'an unknown older author value' => ['author', 'someone-else'],
    'an author list that is not a list' => ['authors', 'Jeffrey Davidson'],
    'a blank author name' => ['authors', ['']],
    'a non-string author name' => ['authors', [42]],
]);

test('archives carry the SEO title and description under their original keys', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $type): void {
    // Arrange
    $record = $factory->withSeo('Archived SEO title', 'Archived SEO description.')
        ->createOne();
    $service = app(PublicContentArchiveImporter::class);

    // Act
    $archive = app(PublicContentArchiveExporter::class)->export();
    $record->forceDelete();
    DB::table('seo')->delete();
    $service->import($archive);

    // Assert
    $imported = $record::query()->sole();
    expect(firstArchivedRecord($archive, $type))->toMatchArray([
        'meta_title' => 'Archived SEO title',
        'meta_description' => 'Archived SEO description.',
    ])->and($imported->seo->title)
        ->toBe('Archived SEO title')
        ->and($imported->seo->description)
        ->toBe('Archived SEO description.');
})->with([
    'posts' => [fn () => Post::factory(), 'posts'],
    'episodes' => [fn () => Episode::factory(), 'episodes'],
    'guides' => [fn () => Guide::factory(), 'guides'],
]);

test('archive import and sync keep the review notes of an existing post', function (string $method): void {
    $reviewer = User::factory()->create();
    $post = Post::factory()->create(['review_notes' => 'Keep this note.', 'reviewed_by' => $reviewer->id]);
    $service = app(PublicContentArchiveImporter::class);
    $archive = withFirstArchivedRecord(app(PublicContentArchiveExporter::class)->export(), 'posts', [
        'title' => 'Updated title',
        'review_notes' => 'Archived note',
        'reviewed_by' => null,
    ]);

    $service->{$method}($archive);

    expect($post->refresh())
        ->title->toBe('Updated title')
        ->review_notes->toBe('Keep this note.')
        ->reviewed_by->toBe($reviewer->id);
})->with(['import', 'sync']);
