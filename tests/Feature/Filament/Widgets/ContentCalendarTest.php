<?php

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Widgets\ContentCalendar;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

test('content calendar lists the next seven days in chronological order', function (): void {
    $post = Post::factory()->create([
        'title' => 'Example post',
        'published_at' => now()->addDays(2),
    ]);
    $episode = Episode::factory()->create([
        'title' => 'Example episode',
        'published_at' => now()->addDay(),
    ]);
    $guide = Guide::factory()->create([
        'title' => 'Example guide',
        'published_at' => now()->addDays(3),
    ]);
    Post::factory()->create(['published_at' => now()->addDays(8)]);

    $timeline = livewire(ContentCalendar::class)->instance()->getTimeline();

    expect(collect($timeline)->map(fn (array $item): array => [
        ...$item,
        'date' => $item['date']?->toIso8601String(),
    ])->all())->toBe([
        [
            'title' => $episode->title,
            'type' => 'Episode',
            'date' => $episode->published_at?->toIso8601String(),
            'status' => 'Scheduled',
            'url' => EpisodeResource::getUrl('edit', ['record' => $episode]),
        ],
        [
            'title' => $post->title,
            'type' => 'Post',
            'date' => $post->published_at?->toIso8601String(),
            'status' => 'Scheduled',
            'url' => PostResource::getUrl('edit', ['record' => $post]),
        ],
        [
            'title' => $guide->title,
            'type' => 'Guide',
            'date' => $guide->published_at?->toIso8601String(),
            'status' => 'Scheduled',
            'url' => GuideResource::getUrl('edit', ['record' => $guide]),
        ],
    ]);
});
