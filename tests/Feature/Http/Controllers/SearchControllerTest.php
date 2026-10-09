<?php

use App\Http\Requests\SearchRequest;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\from;
use function Pest\Laravel\get;

covers(SearchRequest::class);

pest()->use(RefreshDatabase::class);

test('search returns its view model data', function (): void {
    get(route('search'))
        ->assertOk()
        ->assertViewIs('pages.search')
        ->assertViewHas('query')
        ->assertViewHas('results')
        ->assertViewHas('typeLabels')
        ->assertViewHas('resultCount');
});

test('search stays within its query budget as content grows', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()
        ->count(30)
        ->create(['title' => 'Disney planning']);
    Guide::factory()
        ->count(15)
        ->create(['title' => 'Disney planning']);
    Episode::factory()
        ->count(15)
        ->create(['title' => 'Disney planning']);

    // Includes one query for the footer social links and one for the post categories.
    $this->expectsDatabaseQueryCount(9);

    get(route('search', ['q' => 'Disney']))
        ->assertOk();
});

test('search page is available from public navigation', function (): void {
    get(route('search'))
        ->assertOk()
        ->assertSee('Search posts, guides, and podcast episodes')
        ->assertSeeHtml('noindex,follow');

    get(route('home'))->assertOk()
        ->assertSeeHtml(route('search'))
        ->assertSee('Search Mouse28');
});

test('search query is limited to one hundred characters', function (): void {
    from(route('search'))
        ->get(route('search', ['q' => str_repeat('a', 101)]))
        ->assertRedirect(route('search'))
        ->assertSessionHasErrors([
            'q' => 'Search terms may not be longer than 100 characters.',
        ]);
});

test('canonical URLs preserve an HTTP application origin', function (): void {
    $applicationUrl = config()->string('app.url');
    URL::forceScheme(null);
    URL::forceRootUrl('http://localhost');

    try {
        $response = get('http://localhost/search');
    } finally {
        URL::forceRootUrl($applicationUrl);
        URL::forceScheme(str_starts_with($applicationUrl, 'https://') ? 'https' : null);
    }

    $response->assertOk()
        ->assertSeeHtml('<link rel="canonical" href="http://localhost/search">');
});

test('search groups matching published content', function (): void {
    $post = Post::factory()->create(['title' => 'Sensory breaks in Magic Kingdom']);
    $guide = Guide::factory()->create(['title' => 'Sensory break planning guide']);
    $episode = Episode::factory()->create(['title' => 'How we plan sensory breaks']);
    $unrelatedPost = Post::factory()->create(['title' => 'Favorite resort meals']);

    get(route('search', ['q' => 'sensory']))
        ->assertOk()
        ->assertSee('3 results for “sensory”')
        ->assertSeeInOrder(['Blog posts', $post->title, 'Guides', $guide->title, 'Podcast episodes', $episode->title])
        ->assertDontSee($unrelatedPost->title);
});

test('search escapes HTML and regex characters in the query and its matches', function (string $query): void {
    Post::factory()->create(['title' => "Tips {$query} for parks"]);

    get(route('search', ['q' => $query]))
        ->assertOk()
        ->assertSee('1 result for “'.$query.'”')
        ->assertSee("Tips {$query} for parks")
        ->assertDontSeeHtml("Tips {$query} for parks");
})->with([
    'markup' => '<b onclick="x">Q&A</b>',
    'entity' => '&amp;',
    'regex and markup' => '<i>(.*)+[a-z]/</i>',
]);

test('search paginates every content group with accurate totals', function (PostFactory|GuideFactory|EpisodeFactory $factory, string $pageName): void {
    config()->set('mouse28.guides_enabled', true);
    $oldest = $factory->createOne(['title' => 'Sensory oldest match']);
    $factory->count(6)
        ->create(['title' => 'Sensory recent match']);
    $group = str_replace('Page', '', $pageName);
    $nextPageUrl = route('search', ['q' => 'Sensory', $pageName => 2])."#search-{$group}";

    get(route('search', ['q' => 'Sensory']))
        ->assertOk()
        ->assertSee('7 results for “Sensory”')
        ->assertSee('7 found')
        ->assertSee('Sensory recent match')
        ->assertDontSee($oldest->title)
        ->assertSeeHtml(e($nextPageUrl))
        ->assertViewHas('results', fn (array $results): bool => $results[$group] instanceof LengthAwarePaginator
            && $results[$group]->total() === 7
            && $results[$group]->count() === 6
            && $results[$group]->nextPageUrl() === $nextPageUrl);

    get(route('search', ['q' => 'Sensory', $pageName => 2]))
        ->assertOk()
        ->assertSee($oldest->title)
        ->assertDontSee('Sensory recent match')
        ->assertSee('7 results for “Sensory”');
})->with([
    'posts' => [fn (): PostFactory => Post::factory(), 'postsPage'],
    'guides' => [fn (): GuideFactory => Guide::factory(), 'guidesPage'],
    'episodes' => [fn (): EpisodeFactory => Episode::factory(), 'episodesPage'],
]);

test('search paginators keep other groups on their selected page', function (): void {
    config()->set('mouse28.guides_enabled', true);
    Post::factory()
        ->count(7)
        ->create(['title' => 'Sensory post']);
    Episode::factory()
        ->count(7)
        ->create(['title' => 'Sensory episode']);

    get(route('search', ['q' => 'Sensory', 'postsPage' => 2]))
        ->assertOk()
        ->assertSee('14 results for “Sensory”')
        ->assertViewHas('results', fn (array $results): bool => $results['posts'] instanceof LengthAwarePaginator
            && $results['episodes'] instanceof LengthAwarePaginator
            && $results['posts']->currentPage() === 2
            && $results['posts']->count() === 1
            && $results['episodes']->currentPage() === 1
            && $results['episodes']->count() === 6
            && str_contains((string) $results['episodes']->nextPageUrl(), 'postsPage=2')
            && str_contains((string) $results['episodes']->nextPageUrl(), 'episodesPage=2'));
});

test('out of range search pages retain pagination to existing results', function (): void {
    Post::factory()
        ->count(7)
        ->create(['title' => 'Sensory post']);

    get(route('search', ['q' => 'Sensory', 'postsPage' => 99]))
        ->assertOk()
        ->assertSee('7 results for “Sensory”')
        ->assertSee('Blog posts')
        ->assertSeeHtml('postsPage=1');
});

test('search excludes drafts and scheduled content', function (): void {
    $publishedPost = Post::factory()->create(['title' => 'Accessible park planning']);
    $draftGuide = Guide::factory()
        ->draft()
        ->create(['title' => 'Accessible draft guide']);
    $scheduledEpisode = Episode::factory()
        ->scheduled()
        ->create(['title' => 'Accessible scheduled episode']);

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
    get(route('search', ['q' => 'sensory']))->assertOk()
        ->assertSeeHtml('<meta name="robots" content="noindex,follow">')
        ->assertSeeHtml('<link rel="canonical" href="'.route('search').'">');
});

test('page copy and metadata avoid em dashes', function (): void {
    get(route('search'))
        ->assertOk()
        ->assertDontSee('—');
});

test('page uses the dispatch editorial system', function (): void {
    get(route('search'))->assertOk()
        ->assertSeeHtml('data-brand-wordmark')
        ->assertSeeHtml('dispatch-page-field')
        ->assertSeeHtml('js-dispatch-pages');
});

test('repeated searches from one visitor are throttled with the branded page', function (): void {
    config()->set('mouse28.rate_limits.search_per_minute', 1);

    get(route('search', ['q' => 'castle']))->assertOk();

    get(route('search', ['q' => 'castle']))
        ->assertTooManyRequests()
        ->assertSeeHtml('<title>Too Many Requests | Mouse28</title>');
});

test('blank searches are never throttled', function (string $query): void {
    config()->set('mouse28.rate_limits.search_per_minute', 1);

    get(route('search', ['q' => $query]))->assertOk();
    get(route('search', ['q' => $query]))->assertOk();
    get(route('search', ['q' => $query]))->assertOk();
})->with([
    'empty' => [''],
    'whitespace' => ['   '],
]);
