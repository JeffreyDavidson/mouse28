<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Episode;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Support\Content\PreviewUrlGenerator;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\ContentUrls as ContentUrlsContract;
use LogicException;

/**
 * Where creator-kit's "View on site" goes on the Posts, Episodes and Newsletter Issues screens:
 * the public page once the content is live, otherwise its signed preview. Guides keep Mouse28's
 * own Preview action.
 */
final readonly class ContentUrls implements ContentUrlsContract
{
    public function __construct(private PreviewUrlGenerator $previewUrls) {}

    public function publicOrPreviewUrl(Model $record): string
    {
        if (! $record instanceof Post && ! $record instanceof Episode && ! $record instanceof NewsletterIssue) {
            throw new LogicException('Mouse28 has no "View on site" link for '.$record::class.'.');
        }

        if (! $record->isPublished()) {
            return $this->previewUrls->for($record);
        }

        return match (true) {
            $record instanceof Post => route('blog.show', $record),
            $record instanceof Episode => route('episodes.show', $record),
            $record instanceof NewsletterIssue => route('newsletter.issue', $record),
        };
    }
}
