<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\get;

uses(RefreshDatabase::class);

test('blog posts include article and breadcrumb structured data', function (): void {
    $post = Post::factory()->create([
        'title' => 'Accessible <Park> Plan',
        'cover_image' => 'posts/plan.jpg',
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/disability-access-service/',
        'last_reviewed_at' => '2026-08-01',
        'updated_at' => '2026-07-01',
    ]);

    $data = structuredData(get(route('blog.show', $post))->assertOk());

    expect($data['@context'])->toBe('https://schema.org')
        ->and($data['@graph'][0]['@type'])->toBe('BlogPosting')
        ->and($data['@graph'][0]['headline'])->toBe($post->title)
        ->and($data['@graph'][0]['mainEntityOfPage'])->toBe(route('blog.show', $post))
        ->and($data['@graph'][0]['citation'])->toBe($post->source_url)
        ->and($data['@graph'][0]['dateModified'])->toStartWith('2026-08-01')
        ->and($data['@graph'][1]['@type'])->toBe('BreadcrumbList')
        ->and(array_column($data['@graph'][1]['itemListElement'], 'name'))
        ->toBe(['Home', 'Blog', $post->title]);
});

test('guides include review date source and breadcrumb structured data', function (): void {
    $guide = Guide::factory()->create([
        'source_url' => 'https://disneyworld.disney.go.com/guest-services/',
        'last_reviewed_at' => '2026-08-01',
        'updated_at' => '2026-07-01',
    ]);

    $data = structuredData(get(route('guides.show', $guide))->assertOk());
    $article = $data['@graph'][0];

    expect($article['@type'])->toBe('Article')
        ->and($article['citation'])->toBe($guide->source_url)
        ->and($article['dateModified'])->toStartWith('2026-08-01')
        ->and($data['@graph'][1]['itemListElement'][1]['name'])->toBe('Guides');
});

test('episodes include podcast media duration and breadcrumb structured data', function (): void {
    $episode = Episode::factory()->create([
        'season_number' => 3,
        'duration_seconds' => 3723,
        'audio_url' => 'https://cdn.example.com/episode.mp3',
    ]);

    $data = structuredData(get(route('episodes.show', $episode))->assertOk());
    $podcastEpisode = $data['@graph'][0];

    expect($podcastEpisode['@type'])->toBe('PodcastEpisode')
        ->and($podcastEpisode['duration'])->toBe('PT1H2M3S')
        ->and($podcastEpisode['associatedMedia']['contentUrl'])->toBe($episode->audio_url)
        ->and($podcastEpisode['partOfSeason']['@type'])->toBe('PodcastSeason')
        ->and($podcastEpisode['partOfSeason']['seasonNumber'])->toBe(3)
        ->and($podcastEpisode['partOfSeries']['@type'])->toBe('PodcastSeries')
        ->and($data['@graph'][1]['itemListElement'][1]['name'])->toBe('Podcast');
});

function structuredData(TestResponse $response): array
{
    $matched = preg_match(
        '/<script type="application\/ld\+json">(.*?)<\/script>/s',
        $response->getContent(),
        $matches,
    );

    expect($matched)->toBe(1, 'The response did not contain JSON-LD structured data.');

    return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
}
