<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('episode pages link to the adjacent published episodes', function (): void {
    $olderEpisode = Episode::factory()->create([
        'title' => 'Older published episode',
        'published_at' => now()->subDays(3),
    ]);
    $episode = Episode::factory()->create([
        'title' => 'Current episode',
        'published_at' => now()->subDays(2),
    ]);
    $newerEpisode = Episode::factory()->create([
        'title' => 'Newer published episode',
        'published_at' => now()->subDay(),
    ]);
    $draftEpisode = Episode::factory()->draft()->create([
        'title' => 'Private draft episode',
    ]);
    $scheduledEpisode = Episode::factory()->scheduled()->create([
        'title' => 'Future scheduled episode',
    ]);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('Previous episode')
        ->assertSee($olderEpisode->title)
        ->assertSee(route('episodes.show', $olderEpisode), false)
        ->assertSee('Next episode')
        ->assertSee($newerEpisode->title)
        ->assertSee(route('episodes.show', $newerEpisode), false)
        ->assertDontSee($draftEpisode->title)
        ->assertDontSee($scheduledEpisode->title);
});

test('related guides prioritize the category and fill open slots', function (): void {
    $guide = Guide::factory()->create([
        'title' => 'Current accessibility guide',
        'category' => 'accessibility',
    ]);
    $sameCategory = Guide::factory()->create([
        'title' => 'Related accessibility guide',
        'category' => 'accessibility',
    ]);
    $fallbackGuide = Guide::factory()->create([
        'title' => 'Useful planning guide',
        'category' => 'family-planning',
    ]);
    $draftGuide = Guide::factory()->draft()->create([
        'title' => 'Private draft guide',
        'category' => 'accessibility',
    ]);

    get(route('guides.show', $guide))
        ->assertOk()
        ->assertSeeInOrder([$sameCategory->title, $fallbackGuide->title])
        ->assertDontSee($draftGuide->title);
});

test('content category labels link to their filtered indexes', function (): void {
    $post = Post::factory()->create(['category' => 'park-accessibility']);
    $guide = Guide::factory()->create(['category' => 'family-planning']);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertSee(route('blog.index', ['category' => $post->category]), false);

    get(route('guides.show', $guide))
        ->assertOk()
        ->assertSee(route('guides.index', ['category' => $guide->category]), false);
});

test('posts do not reveal an unpublished related episode', function (): void {
    $draftEpisode = Episode::factory()->draft()->create([
        'title' => 'Unannounced podcast episode',
    ]);
    $post = Post::factory()->create([
        'episode_id' => $draftEpisode->getKey(),
    ]);

    get(route('blog.show', $post))
        ->assertOk()
        ->assertDontSee($draftEpisode->title)
        ->assertDontSee(route('episodes.show', $draftEpisode), false);
});
