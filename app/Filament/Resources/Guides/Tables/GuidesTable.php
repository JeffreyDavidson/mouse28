<?php

namespace App\Filament\Resources\Guides\Tables;

use App\Models\Guide;
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

class GuidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->limit(50),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Guide::CATEGORIES[$state] ?? $state),
                TextColumn::make('last_reviewed_at')->date()->sortable()->placeholder('Not reviewed'),
                TextColumn::make('review_status')
                    ->label('Review')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Current' ? 'success' : 'warning'),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->badge()
                    ->getStateUsing(fn (Guide $record): string => EditorialReadiness::label($record))
                    ->color(fn (Guide $record): string => EditorialReadiness::color($record))
                    ->tooltip(fn (Guide $record): string => EditorialReadiness::summary($record)),
                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(fn (Guide $record): string => EditorialReadiness::status($record))
                    ->color(fn (Guide $record): string => EditorialReadiness::statusColor($record)),
                TextColumn::make('published_at')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')->options(Guide::CATEGORIES),
                SelectFilter::make('author')->options(Post::AUTHORS),
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
                    ->query(fn (Builder $query): Builder => $query->whereIn('guides.id', Guide::query()->reviewDue()->select('id'))),
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
