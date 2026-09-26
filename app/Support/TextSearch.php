<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TextSearch
{
    /**
     * Portable across SQLite and MySQL; a backslash escape is not.
     */
    private const string ESCAPE = '!';

    /**
     * Match records where any column contains the term as literal text, so `%` and `_` never act as wildcards.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<literal-string>  $columns  hard-coded column names, never user input
     */
    public static function constrain(Builder $query, array $columns, string $term): void
    {
        $escape = self::ESCAPE;
        $escapedTerm = str_replace([$escape, '%', '_'], ["{$escape}{$escape}", "{$escape}%", "{$escape}_"], $term);

        $query->where(function (Builder $query) use ($columns, $escape, $escapedTerm): void {
            foreach ($columns as $column) {
                $query->orWhereRaw("{$column} like ? escape '{$escape}'", ["%{$escapedTerm}%"]);
            }
        });
    }
}
