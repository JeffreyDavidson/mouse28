<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

/**
 * The exact bytes of the head's title, meta and canonical tags, one per line, so a layout
 * refactor that changes any SEO or social tag fails here.
 *
 * @param  TestResponse<Response>  $response
 */
function seoTags(TestResponse $response): string
{
    preg_match('/<head>(.*?)<\/head>/s', (string) $response->getContent(), $head);
    preg_match_all('/<title>.*?<\/title>|<meta\s[^>]*>|<link rel="canonical"[^>]*>/s', $head[1] ?? '', $tags);

    return implode("\n", $tags[0]);
}

test('public pages keep their seo and social tags', function (string $url, int $status): void {
    Post::factory()->create([
        'title' => 'Snapshot Post',
        'slug' => 'snapshot-post',
        'excerpt' => 'A snapshot post excerpt.',
    ]);
    Episode::factory()->create([
        'title' => 'Snapshot Episode',
        'slug' => 'snapshot-episode',
        'description' => 'A snapshot episode description.',
    ]);

    $response = get($url);

    $response->assertStatus($status);
    expect(seoTags($response))->toMatchSnapshot();
})->with([
    'home' => [fn (): string => route('home'), 200],
    'blog index' => [fn (): string => route('blog.index'), 200],
    'post' => [fn (): string => route('blog.show', 'snapshot-post'), 200],
    'episodes' => [fn (): string => route('episodes.index'), 200],
    'episode' => [fn (): string => route('episodes.show', 'snapshot-episode'), 200],
    'about' => [fn (): string => route('about'), 200],
    'contact' => [fn (): string => route('contact.create'), 200],
    'search' => [fn (): string => route('search'), 200],
    'search with a query' => [fn (): string => route('search', ['q' => 'snapshot']), 200],
    'missing page' => [fn (): string => '/no-such-page', 404],
]);
