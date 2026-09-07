<?php

use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Widgets\ContentCalendar;
use App\Models\Guide;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('timeline includes scheduled guides', function (): void {
    $guide = Guide::factory()->scheduled()->create([
        'title' => 'Airport Accessibility Guide',
        'published_at' => now()->addDays(3),
    ]);

    $timeline = collect(app(ContentCalendar::class)->getTimeline())->keyBy('title');

    expect($timeline[$guide->title])
        ->toMatchArray([
            'type' => 'Guide',
            'status' => 'Scheduled',
            'url' => GuideResource::getUrl('edit', ['record' => $guide]),
        ]);
});
