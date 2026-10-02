<?php

namespace App\Models\Concerns;

/**
 * Temporary bridge: mirrors `content` into the legacy NOT NULL `body` column, so
 * inserts still satisfy it and rolling back to a release that still reads `body`
 * sees the current text. A record loaded without its content leaves `body` alone.
 * Delete this trait with the column.
 */
trait SyncsLegacyBody
{
    protected static function bootSyncsLegacyBody(): void
    {
        static::saving(function (self $record): void {
            if ($record->exists && ! array_key_exists('content', $record->getAttributes())) {
                return;
            }

            $record->setAttribute('body', $record->getAttribute('content') ?? '');
        });
    }
}
