<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * The public storage URL for the social image that editorial content can carry.
 * Cover images moved to `HasFeaturedImage`; this goes when the SEO slice moves
 * `og_image` into the SEO package.
 */
trait HasOgImage
{
    /** @return Attribute<string|null, never> */
    protected function ogImageUrl(): Attribute
    {
        return Attribute::make(get: fn (): ?string => $this->og_image ? "/storage/{$this->og_image}" : null);
    }
}
