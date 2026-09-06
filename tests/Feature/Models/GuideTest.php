<?php

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
