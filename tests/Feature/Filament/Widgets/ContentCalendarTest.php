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
    $post = Post::factory()
        ->scheduled()
        ->create([
            'title' => 'Example post',
            'published_at' => now()->addDays(2),
        ]);
    $episode = Episode::factory()
        ->scheduled()
        ->create([
            'title' => 'Example episode',
            'published_at' => now()->addDay(),
        ]);
    $guide = Guide::factory()
        ->scheduled()
        ->create([
            'title' => 'Example guide',
            'published_at' => now()->addDays(3),
        ]);
    Post::factory()->create(['published_at' => now()->addDays(8)]);

    $timeline = livewire(ContentCalendar::class)->instance()
        ->getTimeline();

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

test('content calendar shows scheduled times in Eastern time', function (): void {
    $this->travelTo('2026-10-05 12:00:00');
    Post::factory()
        ->scheduled()
        ->create(['title' => 'Evening post', 'published_at' => '2026-10-07 01:00:00']);

    livewire(ContentCalendar::class)
        ->assertSee('Oct 6, 9:00pm')
        ->assertDontSee('Oct 7, 1:00am');
});

test('content calendar covers the next seven Eastern days', function (): void {
    $this->travelTo('2026-10-06 02:00:00');
    $lateTonight = Post::factory()
        ->scheduled()
        ->create(['published_at' => '2026-10-06 03:30:00']);
    $pastMidnightUtc = Post::factory()
        ->scheduled()
        ->create(['published_at' => '2026-10-13 03:00:00']);

    $titles = collect(livewire(ContentCalendar::class)->instance()
        ->getTimeline())->pluck('title')
        ->all();

    expect($titles)->toContain($lateTonight->title)
        ->toContain($pastMidnightUtc->title);
});
