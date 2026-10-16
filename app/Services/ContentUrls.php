<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NewsletterIssue;
use App\Support\Content\PreviewUrlGenerator;
use Illuminate\Database\Eloquent\Model;
use JeffreyDavidson\CreatorKit\Contracts\ContentUrls as ContentUrlsContract;
use LogicException;

/**
 * Where creator-kit's "View on site" goes: the public page once the content is live, otherwise
 * its signed preview. Only newsletter issues use the package's screens; posts, episodes and guides
 * keep Mouse28's own Preview action.
 */
final readonly class ContentUrls implements ContentUrlsContract
{
    public function __construct(private PreviewUrlGenerator $previewUrls) {}

    public function publicOrPreviewUrl(Model $record): string
    {
        if (! $record instanceof NewsletterIssue) {
            throw new LogicException('Mouse28 has no "View on site" link for '.$record::class.'.');
        }

        return $record->isPublished()
            ? route('newsletter.issue', $record)
            : $this->previewUrls->for($record);
    }
}
