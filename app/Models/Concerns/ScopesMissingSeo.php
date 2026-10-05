<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Finds records whose saved SEO row lacks a title or a description. Use it on
 * models that use the SEO package's `HasSEO` trait.
 */
trait ScopesMissingSeo
{
    /** @param Builder<static> $query */
    #[Scope]
    protected function missingSeo(Builder $query): void
    {
        $query->whereDoesntHave('seo', function (Builder $seo): void {
            $seo->whereNotNull('title')
                ->where('title', '<>', '')
                ->whereNotNull('description')
                ->where('description', '<>', '');
        });
    }
}
