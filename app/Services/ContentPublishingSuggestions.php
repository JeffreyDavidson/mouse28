<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Support\EditorialReadiness;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\Publishable;
use JeffreyDavidson\CreatorKit\Contracts\PublishingSuggestions;

/**
 * The optional details creator-kit's readiness column lists under "Ready": Mouse28's editorial
 * checklist (EditorialReadiness) minus what already blocks publishing, such as a cover image or
 * an SEO title. Content Mouse28 has no checklist for gets no suggestions.
 */
final class ContentPublishingSuggestions implements PublishingSuggestions
{
    public function suggestions(Model&Publishable $content): array
    {
        if (! $content instanceof Post && ! $content instanceof Guide && ! $content instanceof Episode) {
            return [];
        }

        return array_values(array_diff(EditorialReadiness::issues($content), $content->publishingIssues()));
    }
}
