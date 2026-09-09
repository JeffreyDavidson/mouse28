<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Models\Guide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

pest()->use(RefreshDatabase::class);

test('guide editorial changes record the actor and changed values only', function (): void {
    $editor = User::factory()->admin()->create();
    \Pest\Laravel\actingAs($editor);
    $record = Guide::factory()->create(['title' => 'Original title']);
    $created = Activity::query()->latest('id')->firstOrFail();

    $record->update(['title' => 'Updated title']);

    $updated = Activity::query()->latest('id')->firstOrFail();
    expect($created->event)->toBe('created')
        ->and($updated->event)->toBe('updated')
        ->and($updated->log_name)->toBe('editorial')
        ->and($updated->causer_id)->toBe($editor->id)
        ->and($updated->subject_id)->toBe($record->id)
        ->and($updated->attribute_changes?->all() ?? [])->toBe([
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
    config()->set('mouse28.guide_review_interval_days', 180);

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
