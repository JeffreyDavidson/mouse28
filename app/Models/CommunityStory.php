<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CommunityStory extends Model
{
    protected $guarded = [];

    protected $casts = [
        'photo_consent' => 'boolean',
        'is_approved' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
}
