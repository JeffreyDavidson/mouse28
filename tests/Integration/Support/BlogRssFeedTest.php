<?php

use App\Models\Post;
use App\Support\BlogRssFeed;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

covers(BlogRssFeed::class);

test('blog feed includes published metadata and excludes unpublished posts', function (): void {
    $published = Post::factory()->create([
        'title' => 'Accessible Park Planning',
        'excerpt' => 'A practical planning guide.',
    ]);
    $draft = Post::factory()->draft()->create(['title' => 'Draft Park Planning']);
    $scheduled = Post::factory()->scheduled()->create(['title' => 'Scheduled Park Planning']);

    $content = app(BlogRssFeed::class)->content();

    expect($content)
        ->toContain('<title>Accessible Park Planning</title>')
        ->toContain('<description>A practical planning guide.</description>')
        ->toContain(route('blog.show', $published))
        ->not->toContain($draft->title)
        ->not->toContain($scheduled->title);
});

test('blog feed limits entries to the newest twenty published posts', function (): void {
    $oldest = Post::factory()->create(['published_at' => now()->subDays(30)]);
    Post::factory()->count(20)->create(['published_at' => now()->subDays(2)]);

    $content = app(BlogRssFeed::class)->content();

    expect(substr_count($content, '<item>'))->toBe(20)
        ->and($content)->not->toContain($oldest->title);
});
