<?php

use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Widgets\RecentActivity;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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
