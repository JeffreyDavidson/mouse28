<?php

namespace App\Presenters;

use App\Models\Episode;
use App\Services\SquareResponsiveImageVariants;
use Illuminate\Support\Str;

/**
 * Display formatting for an episode. From The Laravel Architect's presenter; Mouse28
 * shows the duration as minutes and seconds (m:ss), which is its design.
 */
final readonly class EpisodePresenter
{
    /** An episode with no player or transcript and show notes shorter than this gets the compact layout. */
    private const int SPARSE_SHOW_NOTES_LENGTH = 160;

    public function __construct(
        private Episode $episode,
        private SquareResponsiveImageVariants $images,
    ) {}

    public static function from(Episode $episode): self
    {
        return app()->make(self::class, ['episode' => $episode]);
    }

    public function duration(): string
    {
        $seconds = $this->episode->duration_seconds;

        if (! $seconds) {
            return '';
        }

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }

    /** Whether the page has too little to read or hear to carry the full listening layout. */
    public function isSparse(): bool
    {
        return blank($this->episode->transistorEmbedUrl())
            && blank($this->episode->transcript)
            && $this->showNotesLength() < self::SPARSE_SHOW_NOTES_LENGTH;
    }

    /** The episode's own artwork, or the show's cover when it has none. */
    public function coverImageUrl(PodcastPresenter $podcast): string
    {
        return $this->episode->featured_image_url ?: $podcast->coverImageUrl();
    }

    /** The episode's own artwork as the social share image, or the show's when it has none. */
    public function shareImageUrl(PodcastPresenter $podcast): string
    {
        return $this->episode->featured_image_url ?: $podcast->shareImageUrl();
    }

    /** The artwork's square WebP variants; null when the episode has none. */
    public function coverSrcset(): ?string
    {
        return $this->images->srcset($this->episode->featured_image_path);
    }

    private function showNotesLength(): int
    {
        return Str::of(strip_tags($this->episode->show_notes ?? ''))
            ->squish()
            ->length();
    }
}
