<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

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
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('editorial')
            ->logOnly([
                'name',
                'description',
                'cover_image',
                'apple_url',
                'spotify_url',
                'youtube_url',
                'instagram_url',
                'tiktok_url',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    public static function info(): self
    {
        return once(fn (): self => self::query()->first() ?? new self([
            'name' => 'Mouse28',
            'description' => 'Disney parks through the lens of raising a daughter with autism.',
        ]));
    }

    public static function settings(): self
    {
        return self::query()->firstOrCreate(['id' => 1], [
            'name' => 'Mouse28',
            'description' => 'Disney parks through the lens of raising a daughter with autism.',
        ]);
    }
}
