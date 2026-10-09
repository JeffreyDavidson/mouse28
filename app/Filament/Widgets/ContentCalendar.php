<?php

namespace App\Filament\Widgets;

use App\Enums\ContentType;
use App\Enums\PublishStatus;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\DisplayTimezone;
use Carbon\CarbonInterface;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Date;

class ContentCalendar extends Widget
{
    #[\Override]
    protected static ?int $sort = 5;

    #[\Override]
    protected int|string|array $columnSpan = 1;

    #[\Override]
    protected string $view = 'filament.widgets.content-calendar';

    /** @return array<int, array{title: string, type: ContentType, date: CarbonInterface, status: PublishStatus, url: string}> */
    public function getTimeline(): array
    {
        // The seven days are calendar days in the display timezone; the query compares UTC instants.
        $start = Date::now(DisplayTimezone::name())
            ->startOfDay()
            ->utc();
        $end = Date::now(DisplayTimezone::name())
            ->addDays(7)
            ->endOfDay()
            ->utc();

        $posts = Post::whereBetween('published_at', [$start, $end])
            ->select(['id', 'title', 'status', 'published_at'])
            ->orderBy('published_at')
            ->get()
            ->map(fn (Post $post): ?array => $this->item(ContentType::Post, $post))
            ->toBase();

        $episodes = Episode::whereBetween('published_at', [$start, $end])
            ->select(['id', 'title', 'status', 'published_at'])
            ->orderBy('published_at')
            ->get()
            ->map(fn (Episode $episode): ?array => $this->item(ContentType::Episode, $episode))
            ->toBase();

        $guides = Guide::whereBetween('published_at', [$start, $end])
            ->select(['id', 'title', 'status', 'published_at'])
            ->orderBy('published_at')
            ->get()
            ->map(fn (Guide $guide): ?array => $this->item(ContentType::Guide, $guide))
            ->toBase();

        return $posts->merge($episodes)
            ->merge($guides)
            ->filter()
            ->sortBy('date')
            ->values()
            ->all();
    }

    /** @return array{title: string, type: ContentType, date: CarbonInterface, status: PublishStatus, url: string}|null */
    private function item(ContentType $type, Post|Episode|Guide $record): ?array
    {
        if ($record->published_at === null) {
            return null;
        }

        return [
            'title' => $record->title,
            'type' => $type,
            'date' => $record->published_at,
            'status' => $record->publishStatus(),
            'url' => $type->resource()::getUrl('edit', ['record' => $record]),
        ];
    }
}
