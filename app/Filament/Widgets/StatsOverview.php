<?php

namespace App\Filament\Widgets;

use App\Models\Episode;
use App\Models\Post;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class StatsOverview extends Widget
{
    protected static ?int $sort = -2;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.widgets.stats-overview';

    protected function getSubscriberCount(): int
    {
        try {
            return Cache::remember('newsletter_subscriber_count', 300, function () {
                $response = Http::withToken(config('services.resend.key'))
                    ->get('https://api.resend.com/audiences/05c28c48-d37a-4429-9fdf-6ee261c023f4/contacts');

                if ($response->successful()) {
                    return count($response->json('data', []));
                }

                return 0;
            });
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function getStats(): array
    {
        $publishedPosts = Post::where('is_published', true)->count();
        $publishedEpisodes = Episode::where('is_published', true)->count();
        $drafts = Post::where('is_published', false)->count() + Episode::where('is_published', false)->count();

        return [
            [
                'label' => 'Blog Posts',
                'value' => $publishedPosts,
                'icon' => 'heroicon-o-document-text',
                'description' => 'Published',
                'color' => '#5b3e9e',
            ],
            [
                'label' => 'Episodes',
                'value' => $publishedEpisodes,
                'icon' => 'heroicon-o-microphone',
                'description' => 'Published',
                'color' => '#d4a843',
            ],
            [
                'label' => 'Drafts',
                'value' => $drafts,
                'icon' => 'heroicon-o-pencil-square',
                'description' => 'Posts & episodes',
                'color' => '#e8a838',
            ],
            [
                'label' => 'Subscribers',
                'value' => $this->getSubscriberCount(),
                'icon' => 'heroicon-o-users',
                'description' => 'Newsletter',
                'color' => '#7b5eb5',
            ],
        ];
    }
}
