<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Podcast extends Model
{
    protected $fillable = [
        'name',
        'description',
        'cover_image',
        'apple_url',
        'spotify_url',
        'youtube_url',
        'rss_url',
        'instagram_url',
        'tiktok_url',
        'email',
    ];

    public static function info(): static
    {
        return static::query()->first() ?? new static([
            'name' => 'Mouse28',
            'description' => 'Disney parks through the lens of raising a daughter with autism.',
        ]);
    }

    public static function settings(): static
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'name' => 'Mouse28',
            'description' => 'Disney parks through the lens of raising a daughter with autism.',
        ]);
    }
}
