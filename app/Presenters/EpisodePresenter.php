<?php

namespace App\Presenters;

use App\Models\Episode;

/**
 * Display formatting for an episode. From The Laravel Architect's presenter; Mouse28
 * shows the duration as minutes and seconds (m:ss), which is its design.
 */
final readonly class EpisodePresenter
{
    public function __construct(private Episode $episode) {}

    public static function from(Episode $episode): self
    {
        return new self($episode);
    }

    public function duration(): string
    {
        $seconds = $this->episode->duration_seconds;

        if (! $seconds) {
            return '';
        }

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    }
}
