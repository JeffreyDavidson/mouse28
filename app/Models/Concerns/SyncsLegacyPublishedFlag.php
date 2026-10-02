<?php

namespace App\Models\Concerns;

use App\Enums\PublishStatus;

/**
 * Temporary bridge: mirrors the persisted publish status into the legacy
 * `is_published` column, so rolling back to a release that still reads the flag
 * sees correct data. Published and Scheduled count as published, as they did when
 * the flag was the source of truth. Delete this trait with the column.
 */
trait SyncsLegacyPublishedFlag
{
    protected static function bootSyncsLegacyPublishedFlag(): void
    {
        static::saving(function (self $content): void {
            $content->setAttribute('is_published', in_array(
                $content->getAttribute('status'),
                [PublishStatus::Published, PublishStatus::Scheduled],
                true,
            ));
        });
    }
}
