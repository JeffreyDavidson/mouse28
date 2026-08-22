<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'description',
    'cover_image',
    'apple_url',
    'spotify_url',
    'youtube_url',
    'instagram_url',
    'tiktok_url',
    'email',
])]
class Podcast extends Model
{
    public static function info(): self
    {
        return self::query()->first() ?? new self([
            'name' => 'Mouse28',
            'description' => 'Disney parks through the lens of raising a daughter with autism.',
        ]);
    }

    public static function settings(): self
    {
        return self::query()->firstOrCreate(['id' => 1], [
            'name' => 'Mouse28',
            'description' => 'Disney parks through the lens of raising a daughter with autism.',
        ]);
    }

    /** @return list<array{label: string, url: string}> */
    public function distributionLinks(): array
    {
        return array_values(array_filter([
            $this->apple_url ? ['label' => 'Apple Podcasts', 'url' => $this->apple_url] : null,
            $this->spotify_url ? ['label' => 'Spotify', 'url' => $this->spotify_url] : null,
            $this->youtube_url ? ['label' => 'YouTube', 'url' => $this->youtube_url] : null,
            ['label' => 'RSS Feed', 'url' => route('rss.podcast')],
        ]));
    }
}
