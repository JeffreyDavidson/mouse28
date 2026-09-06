<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
