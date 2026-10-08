<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\Guide;
use App\Services\ResponsiveImageVariants;

/** Display formatting for a guide's artwork: its own cover, or its category's bundled image. */
final readonly class GuidePresenter
{
    public function __construct(
        private Guide $guide,
        private ResponsiveImageVariants $images,
    ) {}

    public static function from(Guide $guide): self
    {
        return app()->make(self::class, ['guide' => $guide]);
    }

    public function artworkUrl(): string
    {
        return $this->guide->featured_image_url ?: $this->guide->category->artworkUrl();
    }

    /** The cover's WebP variants; null for category artwork or a cover without variants yet. */
    public function artworkSrcset(): ?string
    {
        if (! $this->guide->featured_image_url) {
            return null;
        }

        return $this->images->srcset($this->guide->featured_image_path);
    }
}
