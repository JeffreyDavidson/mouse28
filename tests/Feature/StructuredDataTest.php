<?php

namespace Tests\Feature;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_posts_include_article_and_breadcrumb_structured_data(): void
    {
        $post = Post::factory()->create([
            'title' => 'Accessible <Park> Plan',
            'cover_image' => 'posts/plan.jpg',
        ]);

        $data = $this->structuredData($this->get(route('blog.show', $post))->assertOk());

        $this->assertSame('https://schema.org', $data['@context']);
        $this->assertSame('BlogPosting', $data['@graph'][0]['@type']);
        $this->assertSame($post->title, $data['@graph'][0]['headline']);
        $this->assertSame(route('blog.show', $post), $data['@graph'][0]['mainEntityOfPage']);
        $this->assertSame('BreadcrumbList', $data['@graph'][1]['@type']);
        $this->assertSame(['Home', 'Blog', $post->title], array_column($data['@graph'][1]['itemListElement'], 'name'));
    }

    public function test_guides_include_review_date_source_and_breadcrumb_structured_data(): void
    {
        $guide = Guide::factory()->create([
            'source_url' => 'https://disneyworld.disney.go.com/guest-services/',
            'last_reviewed_at' => '2026-08-01',
            'updated_at' => '2026-07-01',
        ]);

        $data = $this->structuredData($this->get(route('guides.show', $guide))->assertOk());
        $article = $data['@graph'][0];

        $this->assertSame('Article', $article['@type']);
        $this->assertSame($guide->source_url, $article['citation']);
        $this->assertStringStartsWith('2026-08-01', $article['dateModified']);
        $this->assertSame('Guides', $data['@graph'][1]['itemListElement'][1]['name']);
    }

    public function test_episodes_include_podcast_media_duration_and_breadcrumb_structured_data(): void
    {
        $episode = Episode::factory()->create([
            'season_number' => 3,
            'duration_seconds' => 3723,
            'audio_url' => 'https://cdn.example.com/episode.mp3',
        ]);

        $data = $this->structuredData($this->get(route('episodes.show', $episode))->assertOk());
        $podcastEpisode = $data['@graph'][0];

        $this->assertSame('PodcastEpisode', $podcastEpisode['@type']);
        $this->assertSame('PT1H2M3S', $podcastEpisode['duration']);
        $this->assertSame($episode->audio_url, $podcastEpisode['associatedMedia']['contentUrl']);
        $this->assertSame('PodcastSeason', $podcastEpisode['partOfSeason']['@type']);
        $this->assertSame(3, $podcastEpisode['partOfSeason']['seasonNumber']);
        $this->assertSame('PodcastSeries', $podcastEpisode['partOfSeries']['@type']);
        $this->assertSame('Podcast', $data['@graph'][1]['itemListElement'][1]['name']);
    }

    private function structuredData(TestResponse $response): array
    {
        $matched = preg_match(
            '/<script type="application\/ld\+json">(.*?)<\/script>/s',
            $response->getContent(),
            $matches,
        );

        $this->assertSame(1, $matched, 'The response did not contain JSON-LD structured data.');

        return json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR);
    }
}
