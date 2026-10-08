<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

/**
 * Exports the live posts, guides and episodes, and the podcast's public display
 * metadata. Drafts, scheduled content and private fields (such as review notes) are
 * never included.
 */
class PublicContentArchiveExporter
{
    /**
     * @return array{
     *     version: int,
     *     exported_at: string,
     *     episodes: list<array<string, mixed>>,
     *     posts: list<array<string, mixed>>,
     *     guides: list<array<string, mixed>>,
     *     podcast: array<string, mixed>|null
     * }
     */
    public function export(): array
    {
        $episodes = Episode::query()
            ->with(['tags', 'seo'])
            ->published()
            ->orderBy('published_at')
            ->get(['id', ...PublicContentArchiveSchema::EPISODE_FIELDS])
            ->map(fn (Episode $episode): array => [...$this->attributes($episode, PublicContentArchiveSchema::EPISODE_FIELDS), ...$this->seoAttributes($episode), 'tags' => $episode->tagsWithType('content')
                ->pluck('name')
                ->all()])
            ->values()
            ->all();

        $posts = Post::query()
            ->with(['tags', 'episodes', 'category', 'authors', 'seo'])
            ->published()
            ->orderBy('published_at')
            ->get(['id', 'category_id', ...PublicContentArchiveSchema::POST_FIELDS])
            ->map(fn (Post $post): array => [
                ...$this->attributes($post, PublicContentArchiveSchema::POST_FIELDS),
                ...$this->seoAttributes($post),
                'category' => $post->category?->slug,
                'category_name' => $post->category?->name,
                'authors' => $this->authorNames($post),
                'episode_slugs' => $this->publishedEpisodeSlugs($post),
                'tags' => $post->tagsWithType('content')
                    ->pluck('name')
                    ->all(),
            ])
            ->values()
            ->all();

        $guides = Guide::query()
            ->with(['tags', 'authors', 'seo'])
            ->published()
            ->orderBy('published_at')
            ->get(['id', ...PublicContentArchiveSchema::GUIDE_FIELDS])
            ->map(fn (Guide $guide): array => [
                ...$this->attributes($guide, PublicContentArchiveSchema::GUIDE_FIELDS),
                ...$this->seoAttributes($guide),
                'authors' => $this->authorNames($guide),
                'tags' => $guide->tagsWithType('content')
                    ->pluck('name')
                    ->all(),
            ])
            ->values()
            ->all();

        $podcast = Podcast::query()->first();

        return [
            'version' => PublicContentArchiveSchema::VERSION,
            'exported_at' => Date::now()->toAtomString(),
            'episodes' => array_values($episodes),
            'posts' => array_values($posts),
            'guides' => array_values($guides),
            'podcast' => $podcast ? $this->attributes($podcast, PublicContentArchiveSchema::PODCAST_FIELDS) : null,
        ];
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, mixed>
     */
    private function attributes(Model $model, array $fields): array
    {
        return PublicContentArchiveSchema::only($model->getAttributes(), $fields);
    }

    /**
     * @return array{meta_title: ?string, meta_description: ?string}
     */
    private function seoAttributes(Episode|Post|Guide $record): array
    {
        return [
            'meta_title' => $record->seo?->title,
            'meta_description' => $record->seo?->description,
        ];
    }

    /** @return array<int, string> the names of the record's authors, in byline order */
    private function authorNames(Post|Guide $record): array
    {
        return $record->authors->map(fn (User $author): string => $author->name)
            ->all();
    }

    /** @return list<string> the slugs of the post's live related episodes, in episode number order */
    private function publishedEpisodeSlugs(Post $post): array
    {
        return array_values($post->episodes
            ->filter(fn (Episode $episode): bool => $episode->isPublished())
            ->sortBy('episode_number')
            ->map(fn (Episode $episode): string => $episode->slug)
            ->all());
    }
}
