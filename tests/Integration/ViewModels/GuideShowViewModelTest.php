<?php

use App\Models\Guide;
use App\ViewModels\GuideShowViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(GuideShowViewModel::class);

pest()->use(RefreshDatabase::class);

test('guide data includes related guides and omits preview state by default', function (): void {
    $guide = Guide::factory()->create(['category' => 'accessibility']);
    $relatedGuide = Guide::factory()->create(['category' => 'accessibility']);

    $data = app(GuideShowViewModel::class)->data($guide);

    expect($data['guide']->is($guide))->toBeTrue()
        ->and($data['relatedGuides']->modelKeys())->toContain($relatedGuide->id)
        ->and($data)->not->toHaveKey('isPreview');
});

test('guide preview data marks the payload as a preview', function (): void {
    $guide = Guide::factory()->draft()->create();

    expect(app(GuideShowViewModel::class)->data($guide, preview: true))
        ->toHaveKey('isPreview', true);
});
