<?php

use App\Enums\PublishStatus;
use App\Models\Post;
use App\Support\TextSearch;
use Illuminate\Foundation\Testing\RefreshDatabase;

covers(TextSearch::class);

pest()->use(RefreshDatabase::class);

/** @param list<string> $expectedTitles */
test('text search matches terms as literal text in any listed column', function (string $term, array $expectedTitles): void {
    Post::factory()->create(['title' => '100% fun', 'excerpt' => '', 'content' => '']);
    Post::factory()->create(['title' => '1000 steps', 'excerpt' => 'first_pass', 'content' => '']);
    Post::factory()->create(['title' => 'Wow!', 'excerpt' => '', 'content' => 'fun_day']);

    $query = Post::query();
    TextSearch::constrain($query, ['title', 'excerpt', 'content'], $term);

    expect($query->orderBy('id')
        ->pluck('title')
        ->all())->toBe($expectedTitles);
})->with([
    'percent sign' => ['100%', ['100% fun']],
    'underscore' => ['_', ['1000 steps', 'Wow!']],
    'escape character' => ['!', ['Wow!']],
    'plain text across columns' => ['fun', ['100% fun', 'Wow!']],
]);

test('text search groups its column matches so other constraints still apply', function (): void {
    Post::factory()->create(['title' => 'Castle tour', 'status' => PublishStatus::Published]);
    Post::factory()->create(['title' => 'Castle draft', 'status' => PublishStatus::Draft]);

    $query = Post::query()->where('status', PublishStatus::Published);
    TextSearch::constrain($query, ['title', 'excerpt'], 'Castle');

    expect($query->pluck('title')
        ->all())->toBe(['Castle tour']);
});
