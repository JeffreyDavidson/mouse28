<?php

use App\Filament\Resources\Posts\PostResource;
use App\Filament\Widgets\RecentActivity;
use App\Models\Episode;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('recent activity combines content types in newest-first order and limits the result', function (): void {
    $oldestPost = Post::factory()->create([
        'title' => 'Oldest example',
        'updated_at' => now()->subDays(2),
    ]);
    $latestPost = Post::factory()->create([
        'title' => 'Newest example',
        'updated_at' => now()->subHour(),
    ]);
    $episode = Episode::factory()->create([
        'title' => 'Example episode',
        'updated_at' => now()->subHours(2),
    ]);

    Post::factory()->count(7)->create([
        'updated_at' => now()->subHours(3),
    ]);

    $activity = livewire(RecentActivity::class)->instance()->getActivity();

    expect($activity)->toHaveCount(8)
        ->and($activity[0])->toMatchArray([
            'label' => $latestPost->title,
            'type' => 'Published post',
            'url' => PostResource::getUrl('edit', ['record' => $latestPost]),
        ])
        ->and(collect($activity)->pluck('label'))->toContain($episode->title)
        ->and(collect($activity)->pluck('label'))->not->toContain($oldestPost->title)
        ->and(collect($activity)->pluck('type'))->toContain('Published episode');
});
