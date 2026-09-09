<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\from;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('search stays within its query budget as content grows', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()->count(30)->create(['title' => 'Disney planning']);
    Guide::factory()->count(15)->create(['title' => 'Disney planning']);
    Episode::factory()->count(15)->create(['title' => 'Disney planning']);

    $this->expectsDatabaseQueryCount(4);

    get(route('search', ['q' => 'Disney']))
        ->assertOk();
});

test('search page is available from public navigation', function (): void {
    get(route('search'))
        ->assertOk()
        ->assertSee('Search posts, guides, and podcast episodes')
        ->assertSee('noindex,follow', false);

    get(route('home'))
        ->assertOk()
        ->assertSee(route('search'), false)
        ->assertSee('Search Mouse28');
});

test('search query is limited to one hundred characters', function (): void {
    from(route('search'))
        ->get(route('search', ['q' => str_repeat('a', 101)]))
        ->assertRedirect(route('search'))
        ->assertSessionHasErrors('q');
});

test('canonical URLs preserve an HTTP application origin', function (): void {
    $applicationUrl = config('app.url');
    URL::forceScheme(null);
    URL::forceRootUrl('http://localhost');

    try {
        $response = get('http://localhost/search');
    } finally {
        URL::forceRootUrl($applicationUrl);
        URL::forceScheme(str_starts_with($applicationUrl, 'https://') ? 'https' : null);
    }

    $response->assertOk()
        ->assertSee('<link rel="canonical" href="http://localhost/search">', false);
});

test('search groups matching published content', function (): void {
    $post = Post::factory()->create(['title' => 'Sensory breaks in Magic Kingdom']);
    $guide = Guide::factory()->create(['title' => 'Sensory break planning guide']);
    $episode = Episode::factory()->create(['title' => 'How we plan sensory breaks']);
    $unrelatedPost = Post::factory()->create(['title' => 'Favorite resort meals']);

    get(route('search', ['q' => 'sensory']))
        ->assertOk()
        ->assertSee('Showing 3 results for “sensory”')
        ->assertSeeInOrder(['Blog posts', $post->title, 'Guides', $guide->title, 'Podcast episodes', $episode->title])
        ->assertDontSee($unrelatedPost->title);
});

test('search excludes drafts and scheduled content', function (): void {
    $publishedPost = Post::factory()->create(['title' => 'Accessible park planning']);
    $draftGuide = Guide::factory()->draft()->create(['title' => 'Accessible draft guide']);
    $scheduledEpisode = Episode::factory()->scheduled()->create(['title' => 'Accessible scheduled episode']);

    get(route('search', ['q' => 'accessible']))
        ->assertOk()
        ->assertSee($publishedPost->title)
        ->assertDontSee($draftGuide->title)
        ->assertDontSee($scheduledEpisode->title);
});

test('empty search offers useful paths forward', function (): void {
    get(route('search'))
        ->assertOk()
        ->assertSee('Start somewhere inspiring')
        ->assertSee('Browse practical guides')
        ->assertSee('Listen to the podcast');
});

test('text searches are not indexed', function (): void {
    get(route('search', ['q' => 'sensory']))
        ->assertOk()
        ->assertSee('<meta name="robots" content="noindex,follow">', false)
        ->assertSee('<link rel="canonical" href="'.route('search').'">', false);
});

test('page copy and metadata avoid em dashes', function (): void {
    get(route('search'))
        ->assertOk()
        ->assertDontSee('—');
});

test('page uses the dispatch editorial system', function (): void {
    get(route('search'))
        ->assertOk()
        ->assertSee('data-brand-wordmark', false)
        ->assertSee('dispatch-page-field', false)
        ->assertSee('js-dispatch-pages', false);
});
