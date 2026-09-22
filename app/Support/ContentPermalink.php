<?php

namespace App\Support;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;

class ContentPermalink
{
    public static function isLocked(Post|Guide|Episode $content): bool
    {
        return $content->slug_locked_at !== null
            || ($content->is_published && ($content->published_at?->lte(Date::now()) ?? false));
    }

    public static function rememberPublication(Post|Guide|Episode $content): void
    {
        $originalLock = $content->getOriginal('slug_locked_at');
        $originalDate = $content->getOriginal('published_at');

        if ($originalLock instanceof CarbonInterface) {
            $content->slug_locked_at = $originalLock;

            return;
        }

        // A scheduled record may have gone live since its last save.
        if ($content->getOriginal('is_published') === true
            && $originalDate instanceof CarbonInterface && $originalDate->lte(Date::now())) {
            $content->slug_locked_at = $originalDate;

            return;
        }

        if ($content->is_published && ($content->published_at?->lte(Date::now()) ?? false)) {
            $content->slug_locked_at = $content->published_at;
        }
    }
}
