<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Enums\SourceReviewStatus;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

test('guide editorial changes record the actor and changed values only', function (): void {
    $editor = User::factory()->admin()->create();
    actingAs($editor);
    $record = Guide::factory()->create(['title' => 'Original title']);
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
    config()->set('content.guide_review_interval_days', 180);

    $currentGuide = Guide::factory()->create([
        'last_reviewed_at' => now()->subDays(30),
    ]);
    $staleGuide = Guide::factory()->create([
        'last_reviewed_at' => now()->subDays(181),
    ]);
    $unreviewedGuide = Guide::factory()->create([
        'last_reviewed_at' => null,
    ]);

    $boundaryGuide = Guide::factory()->create([
        'last_reviewed_at' => today()->subDays(180),
    ]);

    $currentIsDue = $currentGuide->isReviewDue();
    $staleIsDue = $staleGuide->isReviewDue();
    $boundaryIsDue = $boundaryGuide->isReviewDue();
    $unreviewedIsDue = $unreviewedGuide->isReviewDue();
    $reviewDueIds = Guide::query()
        ->reviewDue()
        ->pluck('id')
        ->all();

    expect($currentIsDue)->toBeFalse()
        ->and($staleIsDue)->toBeTrue()
        ->and($boundaryIsDue)->toBeFalse()
        ->and($unreviewedIsDue)->toBeTrue()
        ->and($reviewDueIds)->toEqualCanonicalizing([$staleGuide->id, $unreviewedGuide->id]);
});

test('content enums round trip through their existing database strings', function (): void {
    $record = Guide::factory()->create([
        'author' => 'cassie',
        'category' => 'accessibility',
    ]);

    $record->refresh();

    expect($record->author)->toBe(ContentAuthor::Cassie)
        ->and($record->category)->toBe(GuideCategory::Accessibility)
        ->and($record->author_name)->toBe('Cassie Davidson');

    $record->update(['author' => ContentAuthor::Both, 'category' => GuideCategory::FamilyPlanning]);
    $record->refresh();

    expect($record->category)->toBe(GuideCategory::FamilyPlanning)
        ->and($record->getRawOriginal('author'))->toBe('both')
        ->and($record->getRawOriginal('category'))->toBe('family-planning')
        ->and($record->toArray()['author'])->toBe('both')
        ->and($record->toArray()['category'])->toBe('family-planning')
        ->and($record->author_name)->toBe('Jeffrey & Cassie');
});

test('guides are ready to publish with content, an excerpt, an official source, and a review date', function (): void {
    $guide = Guide::factory()->draft()->make([
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
    ]);

    expect($guide->publishingIssues())->toBeEmpty();
});

test('guides cannot be published without each required detail', function (string $attribute, string $issue): void {
    $guide = Guide::factory()->draft()->make([$attribute => null]);

    expect($guide->publishingIssues())->toBe([$issue]);
})->with([
    'content' => ['body', 'Add guide content'],
    'excerpt' => ['excerpt', 'Add an excerpt'],
    'official source' => ['source_url', 'Add an official source'],
    'review date' => ['last_reviewed_at', 'Set the review date'],
]);

test('guides are always tracked for review and never report an untracked source', function (?int $reviewedDaysAgo, SourceReviewStatus $status): void {
    $this->freezeTime();
    config()->set('content.guide_review_interval_days', 180);
    $guide = Guide::factory()->make([
        'last_reviewed_at' => $reviewedDaysAgo === null ? null : today()->subDays($reviewedDaysAgo),
    ]);

    expect($guide->sourceReviewStatus())->toBe($status);
})->with([
    'never reviewed' => [null, SourceReviewStatus::ReviewDue],
    'reviewed within the interval' => [179, SourceReviewStatus::Current],
    'reviewed on the interval boundary' => [180, SourceReviewStatus::Current],
    'reviewed past the interval' => [181, SourceReviewStatus::ReviewDue],
]);

test('the guide review interval comes from content configuration', function (): void {
    $this->freezeTime();
    config()->set('content.guide_review_interval_days', 30);
    $guide = Guide::factory()->make(['last_reviewed_at' => today()->subDays(31)]);

    expect($guide->isReviewDue())->toBeTrue();
});
