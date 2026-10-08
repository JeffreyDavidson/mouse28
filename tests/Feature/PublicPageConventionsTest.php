<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use Database\Factories\EpisodeFactory;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('page copy and metadata avoid em dashes', function (string $route): void {
    get(route($route))
        ->assertOk()
        ->assertDontSee('—');
})->with([
    'about' => ['about'],
    'blog' => ['blog.index'],
    'podcast' => ['episodes.index'],
    'guides' => ['guides.index'],
]);

test('page uses the dispatch editorial system', function (string $route, string $marker): void {
    get(route($route))->assertOk()
        ->assertSeeHtml('data-brand-wordmark')
        ->assertSeeHtml($marker)
        ->assertSeeHtml('js-dispatch-pages');
})->with([
    'about' => ['about', 'data-about-editorial'],
    'blog' => ['blog.index', 'data-editorial-blog'],
    'podcast' => ['episodes.index', 'data-podcast-archive'],
    'guides' => ['guides.index', 'data-guide-archive'],
]);

test('page renders one newsletter signup', function (string $url): void {
    $response = get($url)->assertOk()
        ->assertSeeHtml('id="footer-newsletter-email"')
        ->assertSee('Connect');

    expect(substr_count((string) $response->getContent(), 'action="'.route('newsletter.subscribe').'"'))->toBe(1);
})->with([
    'home' => [fn (): string => route('home')],
    'blog' => [function (): string {
        Post::factory()->create();

        return route('blog.index');
    }],
    'post' => [fn (): string => route('blog.show', Post::factory()->create())],
    'podcast' => [function (): string {
        Episode::factory()->create();

        return route('episodes.index');
    }],
    'episode' => [fn (): string => route('episodes.show', Episode::factory()->create())],
]);

test('page exposes a single main landmark', function (string $url): void {
    $response = get($url)->assertOk();

    expect(mainLandmarkCount($response))->toBe(1);
})->with([
    'about' => [fn (): string => route('about')],
    'guides' => [function (): string {
        Guide::factory()->create();

        return route('guides.index');
    }],
    'guide' => [fn (): string => route('guides.show', Guide::factory()->create())],
]);

test('page links that open a new tab announce it', function (string $url): void {
    $response = get($url)->assertOk();

    expect($this->unannouncedNewTabLinks($response))->toBeEmpty();
})->with([
    'home' => [fn (): string => route('home')],
    'post' => [fn (): string => route('blog.show', Post::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/',
        'last_reviewed_at' => now(),
    ]))],
    'podcast' => [function (): string {
        primaryPodcast()->update(['apple_url' => 'https://podcasts.apple.com/podcast/mouse28']);
        Episode::factory()->create(['transistor_url' => 'https://share.transistor.fm/s/abc123']);

        return route('episodes.index');
    }],
    'episode' => [function (): string {
        primaryPodcast()->update(['apple_url' => 'https://podcasts.apple.com/podcast/mouse28']);

        return route('episodes.show', Episode::factory()->create(['transistor_url' => 'https://share.transistor.fm/s/abc123']));
    }],
]);

test('signed-in visitors see published content but nobody sees drafts at public URLs', function (PostFactory|EpisodeFactory|GuideFactory $factory, string $route, Closure $signIn): void {
    $published = $factory->createOne();
    $draft = $factory
        ->draft()
        ->createOne();
    $signIn();

    get(route($route, $published))->assertOk();
    get(route($route, $draft))->assertNotFound();
})->with([
    'posts' => [fn () => Post::factory(), 'blog.show'],
    'episodes' => [fn () => Episode::factory(), 'episodes.show'],
    'guides' => [fn () => Guide::factory(), 'guides.show'],
])->with([
    'non-admin' => [fn () => actingAs(User::factory()->create())],
    'admin' => [actingAsAdmin(...)],
]);
