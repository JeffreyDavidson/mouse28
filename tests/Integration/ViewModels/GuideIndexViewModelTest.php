<?php

use App\Models\Guide;
use App\ViewModels\GuideIndexViewModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

covers(GuideIndexViewModel::class);

pest()->use(RefreshDatabase::class);

test('guide index data uses stable ID ordering for equal publication dates', function (): void {
    $guides = Guide::factory()->count(2)->create(['published_at' => now()->subDay()]);

    $data = app(GuideIndexViewModel::class)->data(Request::create('/guides'));

    expect($data['guides']->getCollection()->pluck('id')->all())->toBe($guides->reverse()->pluck('id')->all());
});

test('guide index data filters published guides and builds category metadata', function (): void {
    $matching = Guide::factory()->create(['category' => 'accessibility']);
    $other = Guide::factory()->create(['category' => 'family-planning']);
    $draft = Guide::factory()->draft()->create(['category' => 'accessibility']);

    $request = Request::create('/guides', 'GET', ['category' => 'accessibility']);
    $data = app(GuideIndexViewModel::class)->data($request);

    expect($data['category'])->toBe('accessibility')
        ->and($data['guides']->getCollection()->pluck('id')->all())
        ->toContain($matching->id)
        ->not->toContain($other->id)
        ->not->toContain($draft->id)
        ->and($data['pageTitle'])->toContain('Accessibility');
});
