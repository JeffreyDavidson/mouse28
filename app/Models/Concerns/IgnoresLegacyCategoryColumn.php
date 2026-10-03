<?php

namespace App\Models\Concerns;

/**
 * Temporary bridge: the legacy `posts.category` string shares its name with the
 * `category()` relation, and Eloquent returns a loaded column before a relation.
 * Dropping the column from each loaded post keeps `$post->category` the relation
 * and leaves the column itself untouched on save. Delete this trait with the column.
 */
trait IgnoresLegacyCategoryColumn
{
    protected static function bootIgnoresLegacyCategoryColumn(): void
    {
        static::retrieved(function (self $record): void {
            $record->offsetUnset('category');
        });
    }
}
