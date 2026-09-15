<?php

use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Widgets\RecentActivity;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

test('activity shows the eight newest records even when one content type dominates', function (PostFactory|EpisodeFactory|GuideFactory $factory): void {
    $this->freezeSecond();
    $records = collect(range(1, 9))->map(fn (int $offset) => $factory->createOne([
        'title' => "Recent item {$offset}",
        'updated_at' => now()->subMinutes($offset),
    ]));
    foreach ([Post::class, Episode::class, Guide::class] as $otherClass) {
        if ($otherClass !== $factory->modelName()) {
            $otherClass::factory()->create(['updated_at' => now()->subDay()]);
        }
    }

    $activity = app(RecentActivity::class)->getActivity();

    expect(array_column($activity, 'label'))->toBe($records->take(8)->pluck('title')->all());
})->with([
    'posts' => fn () => Post::factory(),
    'episodes' => fn () => Episode::factory(),
    'guides' => fn () => Guide::factory(),
]);

test('activity includes guides and identifies scheduled content', function (): void {
    $guide = Guide::factory()->create(['title' => 'Updated Accessibility Guide']);
    $post = Post::factory()->scheduled()->create(['title' => 'Scheduled Park Story']);

    $activity = collect(app(RecentActivity::class)->getActivity())->keyBy('label');

    expect($activity[$guide->title])
        ->toMatchArray([
            'type' => 'Published guide',
            'url' => GuideResource::getUrl('edit', ['record' => $guide]),
        ])
        ->and($activity[$post->title])->toMatchArray(['type' => 'Scheduled post']);
});

test('activity queries select only fields rendered by the widget', function (): void {
    Post::factory()->create();
    Episode::factory()->create();
    Guide::factory()->create();

    $queries = [];

    DB::listen(function (QueryExecuted $query) use (&$queries): void {
        if (str_contains($query->sql, 'from "posts"') || str_contains($query->sql, 'from "episodes"') || str_contains($query->sql, 'from "guides"')) {
            $queries[] = $query->sql;
        }
    });

    app(RecentActivity::class)->getActivity();

    expect($queries)->toHaveCount(3)
        ->each->not->toContain('select *')
        ->toContain('select "id", "title", "is_published", "published_at", "updated_at"');
});
