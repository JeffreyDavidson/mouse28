<?php

use App\Enums\PublishStatus;
use App\Enums\SourceReviewStatus;
use App\Models\Category;
use App\Models\Episode;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

test('post editorial changes record the actor and changed values only', function (): void {
    $editor = User::factory()->admin()->create();
    actingAs($editor);
    $record = Post::factory()->create(['title' => 'Original title']);
    $created = Activity::query()->latest('id')->firstOrFail();

    $record->update(['title' => 'Updated title']);

    $updated = Activity::query()->latest('id')->firstOrFail();
    expect($created->event)->toBe('created')
        ->and($updated->event)->toBe('updated')
        ->and($updated->log_name)->toBe('editorial')
        ->and($updated->causer_id)->toBe($editor->id)
        ->and($updated->subject_id)->toBe($record->id)
        ->and($updated->attribute_changes?->all() ?? [])->toEqual([
            'attributes' => ['title' => 'Updated title'],
            'old' => ['title' => 'Original title'],
        ]);

    $record->save();

    expect(Activity::query()->whereMorphedTo('subject', $record)->count())->toBe(2);

    $record->delete();
    $record->restore();

    expect(Activity::query()->whereMorphedTo('subject', $record)->pluck('event')->all())
        ->toBe(['created', 'updated', 'deleted', 'restored']);
});

test('editorial review dates determine the review queue', function (): void {
    $this->freezeTime();
    config()->set('content.post_review_interval_days', 180);

    $currentPost = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => today()->subDays(30),
    ]);
    $stalePost = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => today()->subDays(181),
    ]);

    $boundaryPost = Post::factory()->create([
        'last_reviewed_at' => today()->subDays(180),
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/',
    ]);
    $unreviewedPost = Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/',
        'last_reviewed_at' => null,
    ]);
    $untrackedPost = Post::factory()->create([
        'source_url' => null,
        'last_reviewed_at' => null,
    ]);

    $currentIsDue = $currentPost->isReviewDue();
    $staleIsDue = $stalePost->isReviewDue();
    $boundaryIsDue = $boundaryPost->isReviewDue();
    $unreviewedIsDue = $unreviewedPost->isReviewDue();
    $untrackedIsDue = $untrackedPost->isReviewDue();
    $reviewDueIds = Post::query()
        ->reviewDue()
        ->pluck('id')
        ->all();

    expect($currentIsDue)->toBeFalse()
        ->and($staleIsDue)->toBeTrue()
        ->and($boundaryIsDue)->toBeFalse()
        ->and($unreviewedIsDue)->toBeTrue()
        ->and($untrackedIsDue)->toBeFalse()
        ->and($reviewDueIds)->toEqualCanonicalizing([$stalePost->id, $unreviewedPost->id]);
});

test('editorial scopes separate the content work queue', function (): void {
    $draft = Post::factory()->draft()->create();
    $scheduled = Post::factory()->scheduled()->create();
    $published = Post::factory()->create([
        'featured_image_path' => 'posts/complete.jpg',
        'meta_title' => 'Complete title',
        'meta_description' => 'Complete description',
    ]);
    $needsAttention = Post::factory()->create(['featured_image_path' => null]);

    $draftIds = Post::query()
        ->where('status', PublishStatus::Draft)
        ->pluck('id')
        ->all();
    $scheduledIds = Post::query()
        ->scheduled()
        ->pluck('id')
        ->all();
    $publishedIds = Post::query()
        ->published()
        ->pluck('id')
        ->all();
    $attentionIds = Post::query()
        ->needsAttention()
        ->pluck('id')
        ->all();

    expect($draftIds)->toBe([$draft->id])
        ->and($scheduledIds)->toBe([$scheduled->id])
        ->and($publishedIds)->toEqualCanonicalizing([$published->id, $needsAttention->id])
        ->and($attentionIds)->toEqualCanonicalizing([$draft->id, $scheduled->id, $needsAttention->id]);
});

test('posts credit authors through the pivot only', function (): void {
    $post = new Post;

    expect($post->getFillable())->not->toContain('author')
        ->and($post->getCasts())->not->toHaveKey('author')
        ->and($post->getActivitylogOptions()->logAttributes)->not->toContain('author');
});

test('a post belongs to a category and is labelled with its name', function (): void {
    $category = Category::factory()->create(['name' => 'Water Parks']);

    $post = Post::factory()->for($category)->create();

    expect($post->category?->is($category))->toBeTrue()
        ->and($post->category_id)->toBe($category->id)
        ->and($post->category_label)->toBe('Water Parks');
});

test('a post without a category has an empty category label', function (): void {
    $post = Post::factory()->create(['category_id' => null]);

    expect($post->category)->toBeNull()
        ->and($post->category_label)->toBeEmpty();
});

test('posts write their category through category_id only', function (): void {
    $post = new Post;

    expect($post->getFillable())->toContain('category_id')
        ->not->toContain('category')
        ->and($post->getCasts())->not->toHaveKey('category')
        ->and($post->getActivitylogOptions()->logAttributes)->toContain('category_id')
        ->not->toContain('category');
});

test('post category changes are recorded in the editorial log', function (): void {
    $from = Category::factory()->create();
    $to = Category::factory()->create();
    $record = Post::factory()->for($from)->create();

    $record->update(['category_id' => $to->id]);

    expect(Activity::query()->latest('id')->firstOrFail()->attribute_changes?->all() ?? [])->toEqual([
        'attributes' => ['category_id' => $to->id],
        'old' => ['category_id' => $from->id],
    ]);
});

test('posts are ready to publish with content, an excerpt, and a category', function (): void {
    $post = Post::factory()->draft()->make([
        'featured_image_path' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);

    expect($post->publishingIssues())->toBeEmpty();
});

test('posts cannot be published without each required detail', function (string $attribute, string $issue): void {
    $post = Post::factory()->draft()->make([$attribute => null]);

    expect($post->publishingIssues())->toBe([$issue]);
})->with([
    'content' => ['content', 'Add post content'],
    'excerpt' => ['excerpt', 'Add an excerpt'],
    'category' => ['category_id', 'Choose a category'],
]);

test('posts report the freshness of their official source', function (?string $sourceUrl, ?int $reviewedDaysAgo, SourceReviewStatus $status): void {
    $this->freezeTime();
    config()->set('content.post_review_interval_days', 180);
    $post = Post::factory()->make([
        'source_url' => $sourceUrl,
        'last_reviewed_at' => $reviewedDaysAgo === null ? null : today()->subDays($reviewedDaysAgo),
    ]);

    expect($post->sourceReviewStatus())->toBe($status);
})->with([
    'no source' => [null, null, SourceReviewStatus::NotTracked],
    'source never reviewed' => ['https://example.test/source', null, SourceReviewStatus::ReviewDue],
    'reviewed within the interval' => ['https://example.test/source', 179, SourceReviewStatus::Current],
    'reviewed on the interval boundary' => ['https://example.test/source', 180, SourceReviewStatus::Current],
    'reviewed past the interval' => ['https://example.test/source', 181, SourceReviewStatus::ReviewDue],
]);

test('the post review interval comes from content configuration', function (): void {
    $this->freezeTime();
    config()->set('content.post_review_interval_days', 30);
    $post = Post::factory()->make([
        'source_url' => 'https://example.test/source',
        'last_reviewed_at' => today()->subDays(31),
    ]);

    expect($post->isReviewDue())->toBeTrue();
});

test('posts without content need attention', function (?string $content): void {
    $post = Post::factory()->create([
        'content' => $content,
        'featured_image_path' => 'posts/complete.jpg',
        'meta_title' => 'Complete title',
        'meta_description' => 'Complete description',
    ]);

    expect(Post::query()->needsAttention()->pluck('id')->all())->toBe([$post->id]);
})->with([
    'missing' => [null],
    'empty' => [''],
]);

test('post reading time counts the words in the content', function (?string $content, int $minutes): void {
    $post = Post::factory()->make(['content' => $content]);

    expect($post->reading_time)->toBe($minutes);
})->with([
    'missing content' => [null, 1],
    'two hundred words' => [str_repeat('word ', 200), 1],
    'two hundred and one words' => [str_repeat('word ', 201), 2],
]);

test('post content edits are recorded in the editorial log', function (): void {
    $record = Post::factory()->create(['content' => 'Original content']);

    $record->update(['content' => 'Updated content']);

    expect(Activity::query()->latest('id')->firstOrFail()->attribute_changes?->all() ?? [])->toEqual([
        'attributes' => ['content' => 'Updated content'],
        'old' => ['content' => 'Original content'],
    ]);
});

test('a post relates to every episode it is linked to', function (): void {
    $post = Post::factory()->create();
    $episodes = Episode::factory()->count(2)->create();

    $post->episodes()->attach($episodes);

    expect($post->episodes->modelKeys())->toEqualCanonicalizing($episodes->modelKeys());
});

test('a post has no related episodes unless some are linked', function (): void {
    expect(Post::factory()->create()->episodes)->toBeEmpty();
});

test('a post cannot be linked to the same episode twice', function (): void {
    $post = Post::factory()->create();
    $episode = Episode::factory()->create();
    $post->episodes()->attach($episode);

    expect(fn () => $post->episodes()->attach($episode))->toThrow(QueryException::class);
});

test('a soft deleted episode is left out of the related episodes', function (): void {
    $post = Post::factory()->create();
    $episode = Episode::factory()->create();
    $post->episodes()->attach($episode);

    $episode->delete();

    expect($post->refresh()->episodes)->toBeEmpty()
        ->and(DB::table('episode_post')->count())->toBe(1);
});

test('permanently deleting a related episode keeps the post and drops the link', function (): void {
    $post = Post::factory()->create();
    $episode = Episode::factory()->create();
    $post->episodes()->attach($episode);

    $episode->forceDelete();

    expect($post->refresh()->episodes)->toBeEmpty()
        ->and(Post::query()->whereKey($post->id)->exists())->toBeTrue();
});

test('permanently deleting a post drops its episode links and keeps the episodes', function (): void {
    $post = Post::factory()->create();
    $episode = Episode::factory()->create();
    $post->episodes()->attach($episode);

    $post->forceDelete();

    expect(DB::table('episode_post')->count())->toBe(0)
        ->and(Episode::query()->whereKey($episode->id)->exists())->toBeTrue();
});

test('posts no longer expose the legacy single episode link', function (): void {
    $post = new Post;

    expect(new ReflectionClass($post)->hasMethod('episode'))->toBeFalse()
        ->and($post->getFillable())->not->toContain('episode_id')
        ->and($post->getActivitylogOptions()->logAttributes)->not->toContain('episode_id');
});
