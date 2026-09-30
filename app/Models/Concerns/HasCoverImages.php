<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Public storage URLs for the cover and social images that editorial content can carry.
 */
trait HasCoverImages
{
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
