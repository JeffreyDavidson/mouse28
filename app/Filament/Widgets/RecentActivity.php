<?php

namespace App\Filament\Widgets;

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Carbon\CarbonInterface;
use Filament\Widgets\Widget;

class RecentActivity extends Widget
{
    #[\Override]
    protected static ?int $sort = 3;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.widgets.recent-activity';

    /** @return array<int, array{type: ContentType, status: PublishStatus, label: string, time: CarbonInterface, url: string}> */
    public function getActivity(): array
    {
        $items = [];

        foreach (Post::select(['id', 'title', 'status', 'published_at', 'updated_at'])
            ->latest('updated_at')
            ->limit(8)
            ->get() as $post) {
            $items[] = $this->activityItem(ContentType::Post, $post);
        }

        foreach (Episode::select(['id', 'title', 'status', 'published_at', 'updated_at'])
            ->latest('updated_at')
            ->limit(8)
            ->get() as $episode) {
            $items[] = $this->activityItem(ContentType::Episode, $episode);
        }

        foreach (Guide::select(['id', 'title', 'status', 'published_at', 'updated_at'])
            ->latest('updated_at')
            ->limit(8)
            ->get() as $guide) {
            $items[] = $this->activityItem(ContentType::Guide, $guide);
        }

        return collect($items)->filter()
            ->sortByDesc('time')
            ->take(8)
            ->values()
            ->all();
    }

    /** @return array{type: ContentType, status: PublishStatus, label: string, time: CarbonInterface, url: string}|null */
    private function activityItem(ContentType $type, Post|Episode|Guide $record): ?array
    {
        if ($record->updated_at === null) {
            return null;
        }

        return [
            'type' => $type,
            'status' => $record->publishStatus(),
            'label' => $record->title,
            'time' => $record->updated_at,
            'url' => $type->resource()::getUrl('edit', ['record' => $record]),
        ];
    }
}
