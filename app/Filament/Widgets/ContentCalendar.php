<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\EditorialReadiness;
use Filament\Widgets\Widget;

class ContentCalendar extends Widget
{
    #[\Override]
    protected static ?int $sort = 4;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.widgets.content-calendar';

    public function getTimeline(): array
    {
        $start = now()->startOfDay();
        $end = now()->addDays(7)->endOfDay();

        $posts = Post::whereBetween('published_at', [$start, $end])
            ->select(['id', 'title', 'is_published', 'published_at'])
            ->orderBy('published_at')
            ->get()
            ->map(fn (Post $post): array => [
                'title' => $post->title,
                'type' => 'Post',
                'date' => $post->published_at,
                'status' => EditorialReadiness::status($post)->getLabel(),
                'url' => PostResource::getUrl('edit', ['record' => $post]),
            ])
            ->toBase();

        $episodes = Episode::whereBetween('published_at', [$start, $end])
            ->select(['id', 'title', 'is_published', 'published_at'])
            ->orderBy('published_at')
            ->get()
            ->map(fn (Episode $episode): array => [
                'title' => $episode->title,
                'type' => 'Episode',
                'date' => $episode->published_at,
                'status' => EditorialReadiness::status($episode)->getLabel(),
                'url' => EpisodeResource::getUrl('edit', ['record' => $episode]),
            ])
            ->toBase();

        $guides = Guide::whereBetween('published_at', [$start, $end])
            ->select(['id', 'title', 'is_published', 'published_at'])
            ->orderBy('published_at')
            ->get()
            ->map(fn (Guide $guide): array => [
                'title' => $guide->title,
                'type' => 'Guide',
                'date' => $guide->published_at,
                'status' => EditorialReadiness::status($guide)->getLabel(),
                'url' => GuideResource::getUrl('edit', ['record' => $guide]),
            ])
            ->toBase();

        return $posts->merge($episodes)->merge($guides)->sortBy('date')->values()->toArray();
    }
}
