<?php

declare(strict_types=1);

namespace App\Filament\Tables\Filters;

use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shows published content whose source review is due, using the model's `published`
 * and `reviewDue` scopes. Drafts and scheduled content are not counted, like the
 * review due tab and the dashboard.
 */
class ReviewDueFilter extends Filter
{
    public static function getDefaultName(): ?string
    {
        return 'review_due';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query(fn (Builder $query): Builder => $query->whereIn($query->qualifyColumn('id'), $query->getModel()
            ->newQuery()
            ->select('id')
            ->scopes(['published', 'reviewDue'])));
    }
}
