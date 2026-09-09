<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Episodes\EpisodeResource;
use App\Filament\Resources\Guides\GuideResource;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\EditorialReadiness;
use Carbon\Carbon;
use Filament\Widgets\Widget;

class RecentActivity extends Widget
{
    #[\Override]
    protected static ?int $sort = 3;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.widgets.recent-activity';

    /** @return array<int, array{icon: string, color: string, label: string, type: string, time: Carbon|null, url: string}> */
    public function getActivity(): array
    {
        $items = [];

        foreach (Post::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get() as $post) {
            $items[] = [
                'icon' => 'document-text',
                'color' => '#5b3e9e',
                'label' => $post->title,
                'type' => EditorialReadiness::status($post)->getLabel().' post',
                'time' => $post->updated_at,
                'url' => PostResource::getUrl('edit', ['record' => $post]),
            ];
        }

        foreach (Episode::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get() as $episode) {
            $items[] = [
                'icon' => 'microphone',
                'color' => '#d4a843',
                'label' => $episode->title,
                'type' => EditorialReadiness::status($episode)->getLabel().' episode',
                'time' => $episode->updated_at,
                'url' => EpisodeResource::getUrl('edit', ['record' => $episode]),
            ];
        }

        foreach (Guide::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get() as $guide) {
            $items[] = [
                'icon' => 'book-open',
                'color' => '#4a90a4',
                'label' => $guide->title,
                'type' => EditorialReadiness::status($guide)->getLabel().' guide',
                'time' => $guide->updated_at,
                'url' => GuideResource::getUrl('edit', ['record' => $guide]),
            ];
        }

        return collect($items)->sortByDesc('time')->take(8)->values()->all();
    }
}
