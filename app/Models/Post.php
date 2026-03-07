<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public const AUTHORS = [
        'jeffrey' => 'Jeffrey Davidson',
        'cassie' => 'Cassie Davidson',
        'both' => 'Jeffrey & Cassie',
    ];

    public const CATEGORIES = [
        'disney-tips' => 'Disney Tips',
        'park-accessibility' => 'Park Accessibility',
        'episode-recap' => 'Episode Recap',
        'family-life' => 'Family Life',
        'autism-awareness' => 'Autism Awareness',
        'disney-news' => 'Disney News',
        'food-reviews' => 'Food Reviews',
        'resort-reviews' => 'Resort Reviews',
        'disney-plus' => 'Disney+',
        'merchandise' => 'Merchandise',
        'general' => 'General',
    ];

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getAuthorNameAttribute(): string
    {
        return self::AUTHORS[$this->author] ?? 'Mouse28 Team';
    }

    public function getReadingTimeAttribute(): int
    {
        $words = str_word_count(strip_tags($this->body ?? ''));
        return max(1, (int) ceil($words / 200));
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? ucwords(str_replace('-', ' ', $this->category ?? ''));
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? '/storage/' . $this->cover_image : null;
    }

    public function getCategoryColorAttribute(): string
    {
        return match ($this->category) {
            'disney-tips' => 'bg-gold/20 text-gold',
            'park-accessibility' => 'bg-purple/20 text-purple',
            'episode-recap' => 'bg-emerald-500/20 text-emerald-600',
            'family-life' => 'bg-blue-500/20 text-blue-600',
            'autism-awareness' => 'bg-pink-500/20 text-pink-600',
            'disney-news' => 'bg-orange-500/20 text-orange-600',
            'food-reviews' => 'bg-amber-500/20 text-amber-600',
            'resort-reviews' => 'bg-teal-500/20 text-teal-600',
            'disney-plus' => 'bg-indigo-500/20 text-indigo-600',
            'merchandise' => 'bg-rose-500/20 text-rose-600',
            'general' => 'bg-slate-500/20 text-slate-600',
            default => 'bg-navy/10 text-navy',
        };
    }
}
