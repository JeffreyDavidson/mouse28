<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Orders records by publication date, newest first, breaking ties by the newest id
 * so records published at the same moment keep a stable order between loads.
 */
trait ScopesNewestFirst
{
    /** @param Builder<static> $query */
    #[Scope]
    protected function newestFirst(Builder $query): void
    {
        $query->latest($query->qualifyColumn('published_at'))
            ->latest($query->qualifyColumn($this->getKeyName()));
    }
}
