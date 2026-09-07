<?php

use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Widgets\RecentActivity;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('activity shows the eight newest records even when one content type dominates', function (string $modelClass): void {
    $this->freezeSecond();
    $records = collect(range(1, 9))->map(fn (int $offset) => $modelClass::factory()->create([
        'title' => "Recent item {$offset}",
        'updated_at' => now()->subMinutes($offset),
    ]));
    foreach ([Post::class, Episode::class, Guide::class] as $otherClass) {
        if ($otherClass !== $modelClass) {
            $otherClass::factory()->create(['updated_at' => now()->subDay()]);
        }
    }

    $activity = app(RecentActivity::class)->getActivity();

    expect(array_column($activity, 'label'))->toBe($records->take(8)->pluck('title')->all());
})->with([Post::class, Episode::class, Guide::class]);

test('activity includes guides and identifies scheduled content', function (): void {
    $guide = Guide::factory()->create(['title' => 'Updated Accessibility Guide']);
    $post = Post::factory()->scheduled()->create(['title' => 'Scheduled Park Story']);

    $activity = collect(app(RecentActivity::class)->getActivity())->keyBy('label');

    expect($activity[$guide->title])
        ->toMatchArray([
            'type' => 'Published guide',
            'url' => GuideResource::getUrl('edit', ['record' => $guide]),
        ])
        ->and($activity[$post->title]['type'])->toBe('Scheduled post');
});
