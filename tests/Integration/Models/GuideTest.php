<?php

use App\Enums\GuideCategory;
use App\Enums\SourceReviewStatus;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
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

test('guide categories round trip through their existing database strings', function (): void {
    $record = Guide::factory()->create([
        'category' => 'accessibility',
    ]);

    $record->refresh();

    expect($record->category)->toBe(GuideCategory::Accessibility);

    $record->update(['category' => GuideCategory::FamilyPlanning]);
    $record->refresh();

    expect($record->category)->toBe(GuideCategory::FamilyPlanning)
        ->and($record->getRawOriginal('category'))->toBe('family-planning')
        ->and($record->toArray()['category'])->toBe('family-planning');
});

test('guides credit authors through the pivot only', function (): void {
    $guide = new Guide;

    expect($guide->getFillable())->not->toContain('author')
        ->and($guide->getCasts())->not->toHaveKey('author')
        ->and($guide->getActivitylogOptions()->logAttributes)->not->toContain('author');
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
    'content' => ['content', 'Add guide content'],
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

test('guides without content need attention', function (?string $content): void {
    $guide = Guide::factory()->create([
        'content' => $content,
        'cover_image' => 'guides/complete.jpg',
        'source_url' => 'https://example.test/source',
        'last_reviewed_at' => Date::today(),
        'meta_title' => 'Complete title',
        'meta_description' => 'Complete description',
    ]);

    expect(Guide::query()->needsAttention()->pluck('id')->all())->toBe([$guide->id]);
})->with([
    'missing' => [null],
    'empty' => [''],
]);

test('guide reading time counts the words in the content', function (?string $content, int $minutes): void {
    $guide = Guide::factory()->make(['content' => $content]);

    expect($guide->reading_time)->toBe($minutes);
})->with([
    'missing content' => [null, 1],
    'two hundred words' => [str_repeat('word ', 200), 1],
    'two hundred and one words' => [str_repeat('word ', 201), 2],
]);

test('guide content edits are recorded in the editorial log', function (): void {
    $record = Guide::factory()->create(['content' => 'Original content']);

    $record->update(['content' => 'Updated content']);

    expect(Activity::query()->latest('id')->firstOrFail()->attribute_changes?->all() ?? [])->toEqual([
        'attributes' => ['content' => 'Updated content'],
        'old' => ['content' => 'Original content'],
    ]);
});
