<?php

namespace App\Filament\Resources\Guides\Tables;

use App\Enums\GuideCategory;
use App\Enums\SourceReviewStatus;
use App\Filament\Tables\Actions\SoftDeleteBulkActions;
use App\Filament\Tables\Filters\MissingArtworkFilter;
use App\Filament\Tables\Filters\MissingSeoFilter;
use App\Filament\Tables\Filters\ReviewDueFilter;
use App\Models\Guide;
use App\Support\EditorialReadiness;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GuidesTable
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
                TextColumn::make('category')
                    ->badge(),
                TextColumn::make('last_reviewed_at')
                    ->date()
                    ->sortable()
                    ->placeholder('Not reviewed'),
                TextColumn::make('source_review_status')
                    ->label('Review')
                    ->state(fn (Guide $record): SourceReviewStatus => $record->sourceReviewStatus())
                    ->badge(),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->badge()
                    ->getStateUsing(fn (Guide $record): string => EditorialReadiness::label($record))
                    ->color(fn (Guide $record): string => EditorialReadiness::color($record))
                    ->tooltip(fn (Guide $record): string => EditorialReadiness::summary($record)),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('published_at')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->options(GuideCategory::class),
                SelectFilter::make('authors')
                    ->label('Author')
                    ->relationship('authors', 'name', fn (Builder $query): Builder => $query->where('users.is_author', true)),
                MissingArtworkFilter::make(),
                MissingSeoFilter::make(),
                ReviewDueFilter::make(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                SoftDeleteBulkActions::make(),
            ]);
    }
}
