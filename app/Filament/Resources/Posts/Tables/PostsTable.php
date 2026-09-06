<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\ContentAuthor;
use App\Enums\PostCategory;
use App\Enums\PublicationStatus;
use App\Models\Post;
use App\Support\EditorialReadiness;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('author')
                    ->badge()
                    ->color('info'),
                TextColumn::make('category')
                    ->badge(),
                TextColumn::make('episode.title')
                    ->label('Episode')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('last_reviewed_at')
                    ->label('Reviewed')
                    ->date()
                    ->sortable()
                    ->placeholder('Not tracked')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('review_status')
                    ->label('Review')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Current' => 'success',
                        'Review due' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->badge()
                    ->getStateUsing(fn (Post $record): string => EditorialReadiness::label($record))
                    ->color(fn (Post $record): string => EditorialReadiness::color($record))
                    ->tooltip(fn (Post $record): string => EditorialReadiness::summary($record)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Post $record): PublicationStatus => EditorialReadiness::status($record)),
                TextColumn::make('published_at')
                    ->label('Published Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->options(PostCategory::class),
                SelectFilter::make('author')->options(ContentAuthor::class),
                Filter::make('missing_artwork')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->whereNull('cover_image')->orWhere('cover_image', '');
                    })),
                Filter::make('missing_seo')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->whereNull('meta_title')->orWhere('meta_title', '')
                            ->orWhereNull('meta_description')->orWhere('meta_description', '');
                    })),
                Filter::make('review_due')
                    ->query(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()->reviewDue()->select('id'))),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
