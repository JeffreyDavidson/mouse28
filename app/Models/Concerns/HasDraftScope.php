<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Finds records still being written: Draft or In Review. Unlike `unpublished()`, it
 * leaves out published and scheduled records whose publication date has not arrived.
 */
trait HasDraftScope
{
    /** @param Builder<static> $query */
    #[Scope]
    protected function drafts(Builder $query): void
    {
        $query->whereIn($query->qualifyColumn('status'), [PublishStatus::Draft, PublishStatus::InReview]);
    }
}
