<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\NewsletterIssue;
use App\Models\Post;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Opens the record's signed preview link in a new tab, so editors can share or check
 * unpublished content without signing anyone in.
 */
class PreviewContentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'preview';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::OutlinedEye)
            ->authorize('view')
            ->url(fn (Post|Guide|Episode|NewsletterIssue $record, PreviewUrlGenerator $previewUrls): string => $previewUrls->for($record))
            ->openUrlInNewTab();
    }
}
