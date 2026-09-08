<?php

use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Widgets\ContentCalendar;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

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

test('timeline queries select only fields rendered by the calendar', function (): void {
    Post::factory()->scheduled()->create(['published_at' => now()->addDay()]);
    Episode::factory()->scheduled()->create(['published_at' => now()->addDays(2)]);
    Guide::factory()->scheduled()->create(['published_at' => now()->addDays(3)]);

    $queries = [];

    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        if (str_contains($query->sql, 'from "posts"') || str_contains($query->sql, 'from "episodes"') || str_contains($query->sql, 'from "guides"')) {
            $queries[] = $query->sql;
        }
    });

    app(ContentCalendar::class)->getTimeline();

    expect($queries)->toHaveCount(3)
        ->each->not->toContain('select *')
        ->toContain('select "id", "title", "is_published", "published_at"');
});
