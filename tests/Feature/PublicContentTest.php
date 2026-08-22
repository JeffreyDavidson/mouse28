<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('only currently published content is publicly visible', function (): void {
    $publishedPost = Post::factory()->create(['title' => 'Published park post']);
    $draftPost = Post::factory()->draft()->create(['title' => 'Draft park post']);
    $scheduledPost = Post::factory()->scheduled()->create(['title' => 'Scheduled park post']);

    $publishedEpisode = Episode::factory()->create(['title' => 'Published park episode']);
    $scheduledEpisode = Episode::factory()->scheduled()->create(['title' => 'Scheduled park episode']);

    $publishedGuide = Guide::factory()->create(['title' => 'Published park guide']);
    $draftGuide = Guide::factory()->draft()->create(['title' => 'Draft park guide']);
    $scheduledGuide = Guide::factory()->scheduled()->create(['title' => 'Scheduled park guide']);

    get(route('blog.index'))
        ->assertOk()
        ->assertSee($publishedPost->title)
        ->assertDontSee($draftPost->title)
        ->assertDontSee($scheduledPost->title);

    get(route('guides.index'))
        ->assertOk()
        ->assertSee($publishedGuide->title)
        ->assertDontSee($draftGuide->title)
        ->assertDontSee($scheduledGuide->title);

    get(route('blog.show', $scheduledPost))->assertNotFound();
    get(route('episodes.show', $scheduledEpisode))->assertNotFound();
    get(route('guides.show', $scheduledGuide))->assertNotFound();

    get(route('episodes.show', $publishedEpisode))->assertOk();
    get(route('guides.show', $publishedGuide))
        ->assertOk()
        ->assertSee($publishedGuide->title)
        ->assertSee('Last reviewed');
});

test('blog search category sorting and pagination preserve filters', function (): void {
    Post::factory()->create([
        'title' => 'Newest accessible plan',
        'category' => 'park-accessibility',
        'published_at' => now()->subDay(),
    ]);
    Post::factory()->create([
        'title' => 'Oldest accessible plan',
        'category' => 'park-accessibility',
        'published_at' => now()->subWeek(),
    ]);
    Post::factory()->create([
        'title' => 'Unrelated dining review',
        'category' => 'food-reviews',
    ]);

    get(route('blog.index', [
        'category' => 'park-accessibility',
        'q' => 'accessible',
        'sort' => 'oldest',
    ]))
        ->assertOk()
        ->assertSeeInOrder(['Oldest accessible plan', 'Newest accessible plan'])
        ->assertDontSee('Unrelated dining review');
});

test('homepage uses the documented content order without community stories', function (): void {
    Post::factory()->count(2)->create();
    Guide::factory()->create(['title' => 'Sensory planning guide']);
    Episode::factory()->create();

    get(route('home'))
        ->assertOk()
        ->assertSeeInOrder([
            'Latest from the Blog',
            'Latest Stories',
            'Your Guide to the Parks',
            'From the Podcast',
            'Meet Jeffrey & Cassie',
            'Stay in the Loop',
        ])
        ->assertDontSee('Community Stories');
});

test('sitemap and feeds are valid and exclude unpublished content', function (): void {
    config()->set('podcast.owner_name', 'Mouse28 Owner');
    config()->set('podcast.owner_email', 'podcast@example.com');

    $post = Post::factory()->create();
    $draftPost = Post::factory()->draft()->create();
    $episode = Episode::factory()->create(['audio_url' => 'https://cdn.example.com/episode.mp3']);
    $guide = Guide::factory()->create();
    Guide::factory()->draft()->create();

    $sitemap = get(route('sitemap'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee(route('guides.show', $guide), false)
        ->assertDontSee($draftPost->slug);

    expect(simplexml_load_string($sitemap->getContent()))->not->toBeFalse();

    $blogFeed = get(route('rss.blog'))
        ->assertOk()
        ->assertSee($post->title)
        ->assertDontSee($draftPost->title);
    expect(simplexml_load_string($blogFeed->getContent()))->not->toBeFalse();

    $podcastFeed = get(route('rss.podcast'))
        ->assertOk()
        ->assertSee($episode->title)
        ->assertSee('podcast@example.com');
    expect(simplexml_load_string($podcastFeed->getContent()))->not->toBeFalse();
    assertDatabaseCount('podcasts', 0);
});

test('hosted episode audio is used by the player structured data and podcast feed', function (): void {
    Storage::fake('public');
    Storage::disk('public')->put('episodes/audio/hosted-episode.mp3', 'hosted audio');

    $episode = Episode::factory()->create([
        'audio_path' => 'episodes/audio/hosted-episode.mp3',
        'audio_url' => 'https://cdn.example.com/legacy-episode.mp3',
    ]);
    $audioUrl = Storage::disk('public')->url($episode->audio_path);

    get(route('episodes.show', $episode))
        ->assertOk()
        ->assertSee('<source src="'.$audioUrl.'" type="audio/mpeg"', false)
        ->assertSee('"contentUrl":"'.$audioUrl.'"', false)
        ->assertDontSee($episode->audio_url, false);

    get(route('rss.podcast'))
        ->assertOk()
        ->assertSee('enclosure url="'.$audioUrl.'" length="12" type="audio/mpeg"', false)
        ->assertDontSee($episode->audio_url, false);
});

test('missing hosted audio falls back to the external episode URL', function (): void {
    Storage::fake('public');

    $episode = Episode::factory()->create([
        'audio_path' => 'episodes/audio/missing.mp3',
        'audio_url' => 'https://cdn.example.com/fallback-episode.mp3',
    ]);

    expect($episode->audio_source_url)->toBe($episode->audio_url)
        ->and($episode->audioFileSize())->toBe(0);
});

test('invalid guide category falls back to all guides', function (): void {
    $guide = Guide::factory()->create();

    get(route('guides.index', ['category' => 'not-a-category']))
        ->assertOk()
        ->assertSee($guide->title);
});

test('guides are flagged when their editorial review is due', function (): void {
    config()->set('mouse28.guide_review_interval_days', 180);

    $currentGuide = Guide::factory()->create([
        'last_reviewed_at' => now()->subDays(30),
    ]);
    $staleGuide = Guide::factory()->create([
        'last_reviewed_at' => now()->subDays(181),
    ]);
    $unreviewedGuide = Guide::factory()->create([
        'last_reviewed_at' => null,
    ]);

    expect($currentGuide->isReviewDue())->toBeFalse()
        ->and($staleGuide->isReviewDue())->toBeTrue()
        ->and($unreviewedGuide->isReviewDue())->toBeTrue()
        ->and(Guide::reviewDue()->pluck('id')->all())
        ->toEqualCanonicalizing([$staleGuide->id, $unreviewedGuide->id]);

    get(route('guides.show', $currentGuide))
        ->assertOk()
        ->assertDontSee('due for editorial review');

    get(route('guides.show', $staleGuide))
        ->assertOk()
        ->assertSee('due for editorial review');
});
