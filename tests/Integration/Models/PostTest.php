<?php

use App\Enums\ContentAuthor;
use App\Enums\PostCategory;
use App\Enums\PublishStatus;
use App\Enums\SourceReviewStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    expect(Activity::query()->count())->toBe(2);

    $record->delete();
    $record->restore();

    expect(Activity::query()->pluck('event')->all())
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
        'cover_image' => 'posts/complete.jpg',
        'meta_title' => 'Complete title',
        'meta_description' => 'Complete description',
    ]);
    $needsAttention = Post::factory()->create(['cover_image' => null]);

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

test('content enums round trip through their existing database strings', function (): void {
    $record = Post::factory()->create([
        'author' => 'cassie',
        'category' => 'park-accessibility',
    ]);

    $record->refresh();

    expect($record->author)->toBe(ContentAuthor::Cassie)
        ->and($record->category)->toBe(PostCategory::ParkAccessibility)
        ->and($record->author_name)->toBe('Cassie Davidson');

    $record->update(['author' => ContentAuthor::Both, 'category' => PostCategory::DisneyTips]);
    $record->refresh();

    expect($record->category)->toBe(PostCategory::DisneyTips)
        ->and($record->getRawOriginal('author'))->toBe('both')
        ->and($record->getRawOriginal('category'))->toBe('disney-tips')
        ->and($record->toArray()['author'])->toBe('both')
        ->and($record->toArray()['category'])->toBe('disney-tips')
        ->and($record->author_name)->toBe('Jeffrey & Cassie');
});

test('author initials are derived from the display name', function (?ContentAuthor $author, string $initials): void {
    $record = Post::factory()->make(['author' => $author]);

    expect($record->author_initials)->toBe($initials);
})->with([
    'Jeffrey' => [ContentAuthor::Jeffrey, 'JD'],
    'Cassie' => [ContentAuthor::Cassie, 'CD'],
    'both authors' => [ContentAuthor::Both, 'J&C'],
    'Mouse28 team fallback' => [null, 'MT'],
]);

test('posts are ready to publish with content, an excerpt, and a category', function (): void {
    $post = Post::factory()->draft()->make([
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);

    expect($post->publishingIssues())->toBeEmpty();
});

test('posts cannot be published without each required detail', function (string $attribute, string $issue): void {
    $post = Post::factory()->draft()->make([$attribute => null]);

    expect($post->publishingIssues())->toBe([$issue]);
})->with([
    'content' => ['body', 'Add post content'],
    'excerpt' => ['excerpt', 'Add an excerpt'],
    'category' => ['category', 'Choose a category'],
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
