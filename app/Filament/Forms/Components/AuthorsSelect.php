<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;

/**
 * Credits a post or guide to one or more authors. Only users flagged as authors are
 * offered or accepted (never the admin login accounts), every author is credited by
 * default, and the chosen order is saved as the byline order.
 */
class AuthorsSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Authors')
            ->relationship('authors', 'name', fn (Builder $query): Builder => $query->where('users.is_author', true))
            ->multiple()
            ->searchable()
            ->preload()
            ->required()
            ->default(fn (): array => User::authorIds())
            ->saveRelationshipsUsing(fn (Post|Guide $record, ?array $state) => $record->syncAuthors(array_filter($state ?? [], fn (mixed $id): bool => is_int($id) || is_string($id))))
            ->extraAlpineAttributes(['data-mouse28-accessible-select' => true]);
    }
}
