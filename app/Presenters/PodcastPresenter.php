<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Models\Podcast;
use App\Services\ResponsiveImageVariants;

/**
 * Presents the show's cover artwork. The cover is the uploaded image, or the bundled
 * artwork for a show without one. Modelled on The Laravel Architect's presenter, which
 * serves many shows; Mouse28 has one, so the bundled artwork is a constant.
 */
final readonly class PodcastPresenter
{
    private const string FALLBACK_COVER = '/images/podcast/mouse28-cover.webp';

    private const string FALLBACK_SHARE_IMAGE = '/images/podcast/mouse28-cover.jpg';

    private const string FALLBACK_COVER_SRCSET = '/images/podcast/mouse28-cover-640.webp 640w, /images/podcast/mouse28-cover-768.webp 768w, /images/podcast/mouse28-cover.webp 1200w';

    public function __construct(
        private Podcast $podcast,
        private ResponsiveImageVariants $images,
    ) {}

    public static function from(Podcast $podcast): self
    {
        return app()->make(self::class, ['podcast' => $podcast]);
    }

    public function coverImageUrl(): string
    {
        return $this->uploadedCoverUrl() ?? self::FALLBACK_COVER;
    }

    /** The cover as the social share image, which the bundled artwork serves as JPEG. */
    public function shareImageUrl(): string
    {
        return $this->uploadedCoverUrl() ?? self::FALLBACK_SHARE_IMAGE;
    }

    /** The uploaded cover's WebP variants, or the bundled artwork's sizes. Null when the upload has no variants. */
    public function coverSrcset(): ?string
    {
        if ($this->podcast->cover_image_path) {
            return $this->images->srcset($this->podcast->cover_image_path);
        }

        return self::FALLBACK_COVER_SRCSET;
    }

    private function uploadedCoverUrl(): ?string
    {
        if (! $this->podcast->cover_image_path) {
            return null;
        }

        return '/storage/'.ltrim($this->podcast->cover_image_path, '/');
    }
}
