<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Guide extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public const PARKS = [
        'magic-kingdom' => 'Magic Kingdom',
        'epcot' => 'EPCOT',
        'hollywood-studios' => 'Hollywood Studios',
        'animal-kingdom' => 'Animal Kingdom',
        'general' => 'General',
    ];

    public const CATEGORIES = [
        'sensory-tips' => 'Sensory Tips',
        'das-guide' => 'DAS Guide',
        'quiet-spots' => 'Quiet Spots',
        'dining' => 'Dining',
        'rides' => 'Rides',
        'planning' => 'Planning',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopePark(Builder $query, string $park): Builder
    {
        return $query->where('park', $park);
    }

    public function scopeCategory(Builder $query, string $cat): Builder
    {
        return $query->where('category', $cat);
    }

    public function getParkLabelAttribute(): string
    {
        return self::PARKS[$this->park] ?? $this->park;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
