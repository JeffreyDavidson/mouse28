<?php

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use App\Support\StructuredData;
use Illuminate\Support\Carbon;

test('post structured data prioritizes metadata and the newest content timestamp', function (): void {
    $post = Post::factory()->make([
        'title' => 'Park Tips',
        'slug' => 'park-tips',
        'meta_title' => 'Accessible Park Tips',
        'meta_description' => 'Practical planning guidance.',
        'cover_image' => 'posts/cover.jpg',
        'og_image' => 'posts/social.jpg',
        'source_url' => 'https://source.example/park-tips',
        'published_at' => Carbon::parse('2026-08-01 12:00:00 UTC'),
    ]);
    $post->updated_at = Carbon::parse('2026-08-02 12:00:00 UTC');
    $post->last_reviewed_at = Carbon::parse('2026-08-03 12:00:00 UTC');

    $data = StructuredData::forPost($post);
    $article = $data['@graph'][0];

    expect($article)->toMatchArray([
        '@type' => 'BlogPosting',
        'headline' => 'Accessible Park Tips',
        'description' => 'Practical planning guidance.',
        'mainEntityOfPage' => route('blog.show', $post),
        'datePublished' => '2026-08-01T12:00:00+00:00',
        'dateModified' => '2026-08-03T00:00:00+00:00',
        'image' => url($post->og_image_url),
        'citation' => 'https://source.example/park-tips',
    ])->and($article['@id'])->toBe(route('blog.show', $post).'#blog-posting')
        ->and($data['@graph'][1]['itemListElement'])->toContain([
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Blog',
            'item' => route('blog.index'),
        ]);
});

test('post and guide structured data use content fallbacks without optional metadata', function (): void {
    $post = Post::factory()->make([
        'title' => 'Fallback Post',
        'slug' => 'fallback-post',
        'meta_description' => null,
        'excerpt' => null,
        'body' => '<p>'.str_repeat('Useful planning advice. ', 20).'</p>',
        'cover_image' => 'posts/cover.jpg',
        'og_image' => null,
        'source_url' => null,
        'published_at' => Carbon::parse('2026-08-01 12:00:00 UTC'),
    ]);
    $post->updated_at = Carbon::parse('2026-08-02 12:00:00 UTC');

    $guide = Guide::factory()->make([
        'title' => 'Fallback Guide',
        'slug' => 'fallback-guide',
        'meta_description' => null,
        'excerpt' => 'Guide excerpt.',
        'cover_image' => null,
        'og_image' => null,
        'source_url' => null,
        'published_at' => Carbon::parse('2026-08-01 12:00:00 UTC'),
    ]);
    $guide->updated_at = Carbon::parse('2026-08-02 12:00:00 UTC');
    $guide->last_reviewed_at = Carbon::parse('2026-08-01 12:00:00 UTC');

    $postArticle = StructuredData::forPost($post)['@graph'][0];
    $guideArticle = StructuredData::forGuide($guide)['@graph'][0];

    expect($postArticle['description'])->not->toContain('<')
        ->and($postArticle['description'])->toHaveLength(203)
        ->and($postArticle['description'])->toEndWith('...')
        ->and($postArticle['dateModified'])->toBe('2026-08-02T12:00:00+00:00')
        ->and($postArticle['image'])->toBe(url($post->cover_image_url))
        ->and($postArticle)->not->toHaveKeys(['citation'])
        ->and($guideArticle)->toMatchArray([
            '@type' => 'Article',
            'headline' => 'Fallback Guide',
            'description' => 'Guide excerpt.',
            'dateModified' => '2026-08-02T12:00:00+00:00',
        ])
        ->and($guideArticle)->not->toHaveKeys(['image', 'citation']);
});

test('episode structured data includes configured podcast and optional media metadata', function (): void {
    $episode = Episode::factory()->make([
        'title' => 'Episode 42',
        'slug' => 'episode-42',
        'meta_title' => 'Accessible Episode 42',
        'meta_description' => 'Episode metadata.',
        'episode_number' => 42,
        'season_number' => 3,
        'duration_seconds' => 3661,
        'audio_url' => 'https://audio.example/episode-42.mp3',
        'cover_image' => 'episodes/cover.jpg',
        'og_image' => 'episodes/social.jpg',
        'published_at' => Carbon::parse('2026-08-01 12:00:00 UTC'),
    ]);
    $podcast = new Podcast(['name' => 'Mouse28 Weekly']);

    $data = StructuredData::forEpisode($episode, $podcast);
    $podcastEpisode = $data['@graph'][0];

    expect($podcastEpisode)->toMatchArray([
        '@type' => 'PodcastEpisode',
        'name' => 'Accessible Episode 42',
        'description' => 'Episode metadata.',
        'episodeNumber' => 42,
        'partOfSeries' => [
            '@type' => 'PodcastSeries',
            'name' => 'Mouse28 Weekly',
            'url' => route('episodes.index'),
        ],
        'partOfSeason' => [
            '@type' => 'PodcastSeason',
            'seasonNumber' => 3,
        ],
        'duration' => 'PT1H1M1S',
        'associatedMedia' => [
            '@type' => 'MediaObject',
            'contentUrl' => 'https://audio.example/episode-42.mp3',
        ],
        'image' => url($episode->og_image_url),
    ]);
});

test('episode structured data omits unavailable media metadata and uses the default podcast name', function (): void {
    $episode = Episode::factory()->make([
        'title' => 'Episode 43',
        'slug' => 'episode-43',
        'meta_description' => null,
        'description' => null,
        'show_notes' => 'Episode notes.',
        'season_number' => null,
        'duration_seconds' => null,
        'audio_url' => null,
        'cover_image' => null,
        'og_image' => null,
        'published_at' => Carbon::parse('2026-08-01 12:00:00 UTC'),
    ]);

    $podcastEpisode = StructuredData::forEpisode($episode)['@graph'][0];

    expect($podcastEpisode['name'])->toBe('Episode 43')
        ->and($podcastEpisode['description'])->toBe('Episode notes.')
        ->and($podcastEpisode['partOfSeries']['name'])->toBe('Mouse28')
        ->and($podcastEpisode)->not->toHaveKeys([
            'partOfSeason',
            'duration',
            'associatedMedia',
            'image',
        ]);
});
