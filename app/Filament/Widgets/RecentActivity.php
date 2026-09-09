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

class RecentActivity extends Widget
{
    #[\Override]
    protected static ?int $sort = 3;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.widgets.recent-activity';

    public function getActivity(): array
    {
        $items = collect();

        Post::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get()->each(function (Post $post) use ($items): void {
            $items->push([
                'icon' => 'document-text',
                'color' => '#5b3e9e',
                'label' => $post->title,
                'type' => EditorialReadiness::status($post)->getLabel().' post',
                'time' => $post->updated_at,
                'url' => PostResource::getUrl('edit', ['record' => $post]),
            ]);
        });

        Episode::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get()->each(function (Episode $episode) use ($items): void {
            $items->push([
                'icon' => 'microphone',
                'color' => '#d4a843',
                'label' => $episode->title,
                'type' => EditorialReadiness::status($episode)->getLabel().' episode',
                'time' => $episode->updated_at,
                'url' => EpisodeResource::getUrl('edit', ['record' => $episode]),
            ]);
        });

        Guide::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get()->each(function (Guide $guide) use ($items): void {
            $items->push([
                'icon' => 'book-open',
                'color' => '#4a90a4',
                'label' => $guide->title,
                'type' => EditorialReadiness::status($guide)->getLabel().' guide',
                'time' => $guide->updated_at,
                'url' => GuideResource::getUrl('edit', ['record' => $guide]),
            ]);
        });

        return $items->sortByDesc('time')->take(8)->values()->toArray();
    }
}
