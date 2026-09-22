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
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class RecentActivity extends Widget
{
    #[\Override]
    protected static ?int $sort = 3;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.widgets.recent-activity';

    /** @return array<int, array{icon: Heroicon, color: string, label: string, type: string, time: Carbon|null, url: string}> */
    public function getActivity(): array
    {
        $items = [];

        foreach (Post::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get() as $post) {
            $items[] = $this->activityItem(
                Heroicon::OutlinedDocumentText,
                'purple',
                $post->title,
                EditorialReadiness::status($post)->getLabel().' post',
                $post->updated_at,
                PostResource::getUrl('edit', ['record' => $post]),
            );
        }

        foreach (Episode::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get() as $episode) {
            $items[] = $this->activityItem(
                Heroicon::OutlinedMicrophone,
                'gold',
                $episode->title,
                EditorialReadiness::status($episode)->getLabel().' episode',
                $episode->updated_at,
                EpisodeResource::getUrl('edit', ['record' => $episode]),
            );
        }

        foreach (Guide::select(['id', 'title', 'is_published', 'published_at', 'updated_at'])->latest('updated_at')->limit(8)->get() as $guide) {
            $items[] = $this->activityItem(
                Heroicon::OutlinedBookOpen,
                'teal',
                $guide->title,
                EditorialReadiness::status($guide)->getLabel().' guide',
                $guide->updated_at,
                GuideResource::getUrl('edit', ['record' => $guide]),
            );
        }

        return collect($items)->sortByDesc('time')->take(8)->values()->all();
    }

    /**
     * @param  'gold'|'purple'|'teal'  $color
     * @return array{icon: Heroicon, color: string, label: string, type: string, time: Carbon|null, url: string}
     */
    private function activityItem(
        Heroicon $icon,
        string $color,
        string $label,
        string $type,
        ?Carbon $time,
        string $url,
    ): array {
        return [
            'icon' => $icon,
            'color' => $color,
            'label' => $label,
            'type' => $type,
            'time' => $time,
            'url' => $url,
        ];
    }
}
