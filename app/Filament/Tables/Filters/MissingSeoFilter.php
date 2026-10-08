<?php

declare(strict_types=1);

namespace App\Filament\Tables\Filters;

use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shows content whose saved SEO row lacks a title or a description, using the model's
 * `missingSeo` scope (see `ScopesMissingSeo`).
 */
class MissingSeoFilter extends Filter
{
    public static function getDefaultName(): ?string
    {
        return 'missing_seo';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query(fn (Builder $query): Builder => $query->whereIn($query->qualifyColumn('id'), $query->getModel()
            ->newQuery()
            ->select('id')
            ->scopes(['missingSeo'])));
    }
}
