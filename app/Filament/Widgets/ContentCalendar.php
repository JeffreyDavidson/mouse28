<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Post;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;

class ContentCalendar extends Widget
{
    protected static ?int $sort = 4;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.content-calendar';

    public function getTimeline(): array
    {
        $start = now()->startOfDay();
        $end = now()->addDays(7)->endOfDay();

        $posts = Post::whereBetween('published_at', [$start, $end])
            ->orderBy('published_at')
            ->get()
            ->map(fn ($post) => [
                'title' => $post->title,
                'type' => 'Post',
                'date' => $post->published_at,
                'status' => !$post->is_published ? 'Draft' : ($post->published_at->isFuture() ? 'Scheduled' : 'Published'),
                'url' => route('filament.admin.resources.posts.edit', $post),
            ]);

        $episodes = Episode::whereBetween('published_at', [$start, $end])
            ->orderBy('published_at')
            ->get()
            ->map(fn ($episode) => [
                'title' => $episode->title,
                'type' => 'Episode',
                'date' => $episode->published_at,
                'status' => !$episode->is_published ? 'Draft' : ($episode->published_at->isFuture() ? 'Scheduled' : 'Published'),
                'url' => route('filament.admin.resources.episodes.edit', $episode),
            ]);

        return $posts->merge($episodes)->sortBy('date')->values()->toArray();
    }
}
