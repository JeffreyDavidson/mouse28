<?php

use App\Enums\ContentAuthor;
use App\Enums\GuideCategory;
use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('editorial review dates determine the review queue', function (): void {
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

    expect($currentGuide->isReviewDue())->toBeFalse()
        ->and($staleGuide->isReviewDue())->toBeTrue()
        ->and($unreviewedGuide->isReviewDue())->toBeTrue()
        ->and(Guide::reviewDue()->pluck('id')->all())
        ->toEqualCanonicalizing([$staleGuide->id, $unreviewedGuide->id]);
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

    $record->update(['author' => ContentAuthor::Both]);
    $record->refresh();

    expect($record->getRawOriginal('author'))->toBe('both')
        ->and($record->getRawOriginal('category'))->toBe('accessibility')
        ->and($record->toArray()['author'])->toBe('both')
        ->and($record->toArray()['category'])->toBe('accessibility')
        ->and($record->author_name)->toBe('Jeffrey & Cassie');
});
