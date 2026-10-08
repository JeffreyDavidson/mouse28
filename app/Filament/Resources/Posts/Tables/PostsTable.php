<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\SourceReviewStatus;
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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('seo'))
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('authors.name')
                    ->label('Authors')
                    ->badge()
                    ->color('info')
                    ->placeholder('No authors'),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->sortable(),
                TextColumn::make('last_reviewed_at')
                    ->label('Reviewed')
                    ->date()
                    ->sortable()
                    ->placeholder('Not tracked')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('source_review_status')
                    ->label('Review')
                    ->state(fn (Post $record): SourceReviewStatus => $record->sourceReviewStatus())
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->badge()
                    ->getStateUsing(fn (Post $record): string => EditorialReadiness::label($record))
                    ->color(fn (Post $record): string => EditorialReadiness::color($record))
                    ->tooltip(fn (Post $record): string => EditorialReadiness::summary($record)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('published_at')
                    ->label('Published Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                SelectFilter::make('authors')
                    ->label('Author')
                    ->relationship('authors', 'name', fn (Builder $query): Builder => $query->where('users.is_author', true)),
                Filter::make('missing_artwork')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->whereNull('featured_image_path')
                            ->orWhere('featured_image_path', '');
                    })),
                Filter::make('missing_seo')
                    ->query(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()
                        ->missingSeo()
                        ->select('id'))),
                Filter::make('review_due')
                    ->query(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()
                        ->published()
                        ->reviewDue()
                        ->select('id'))),
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
