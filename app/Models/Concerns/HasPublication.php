<?php

namespace App\Models\Concerns;

use App\Support\ContentPermalink;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Date;

/**
 * Shared publication rules for editorial content: a record is live once it is
 * published and its publication date has arrived.
 */
trait HasPublication
{
    protected static function bootHasPublication(): void
    {
        static::saving(function (self $content): void {
            ContentPermalink::rememberPublication($content);
        });
    }

    public function isPublished(): bool
    {
        return $this->is_published && ($this->published_at?->lte(Date::now()) ?? false);
    }

    public function isScheduled(): bool
    {
        return $this->is_published && ($this->published_at?->isAfter(Date::now()) ?? false);
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => $this->published_at ?? Date::now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update(['is_published' => false]);
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Date::now());
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function drafts(Builder $query): void
    {
        $query->where('is_published', false);
    }

    /** @param Builder<static> $query */
    #[Scope]
    protected function scheduled(Builder $query): void
    {
        $query->where('is_published', true)
            ->where('published_at', '>', Date::now());
    }
}
