<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Post;
use Filament\Widgets\Widget;

class RecentActivity extends Widget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 1;

    protected string $view = 'filament.widgets.recent-activity';

    public function getActivity(): array
    {
        $items = collect();

        Post::latest('updated_at')->limit(5)->get()->each(function ($post) use ($items) {
            $items->push([
                'icon' => 'document-text',
                'color' => '#5b3e9e',
                'label' => $post->title,
                'type' => $post->is_published ? 'Published post' : 'Draft post',
                'time' => $post->updated_at,
                'url' => route('filament.admin.resources.posts.edit', $post),
            ]);
        });

        Episode::latest('updated_at')->limit(5)->get()->each(function ($episode) use ($items) {
            $items->push([
                'icon' => 'microphone',
                'color' => '#d4a843',
                'label' => $episode->title,
                'type' => $episode->is_published ? 'Published episode' : 'Draft episode',
                'time' => $episode->updated_at,
                'url' => route('filament.admin.resources.episodes.edit', $episode),
            ]);
        });

        return $items->sortByDesc('time')->take(8)->values()->toArray();
    }
}
