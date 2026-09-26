<?php

namespace App\Models\Concerns;

use App\Support\ContentPermalink;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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

    public function isLive(): bool
    {
        return $this->is_published && ($this->published_at?->lte(Date::now()) ?? false);
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

    /** @return Attribute<string|null, never> */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(get: fn (): ?string => $this->cover_image ? "/storage/{$this->cover_image}" : null);
    }

    /** @return Attribute<string|null, never> */
    protected function ogImageUrl(): Attribute
    {
        return Attribute::make(get: fn (): ?string => $this->og_image ? "/storage/{$this->og_image}" : null);
    }
}
