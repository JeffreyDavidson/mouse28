<?php

namespace App\Filament\Resources\Episodes\Tables;

use App\Models\Episode;
use App\Support\EditorialReadiness;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EpisodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('episode_number', 'desc')
            ->columns([
                TextColumn::make('episode_number')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('season_number')
                    ->label('Season')
                    ->sortable(),
                TextColumn::make('transistor_url')
                    ->label('Player')
                    ->badge()
                    ->getStateUsing(fn (Episode $record): string => $record->transistor_embed_url ? 'Available' : 'Not available')
                    ->color(fn (string $state): string => $state === 'Available' ? 'success' : 'gray'),
                TextColumn::make('transcript')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => filled($state) ? 'Available' : 'Not available')
                    ->color(fn (?string $state): string => filled($state) ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->badge()
                    ->getStateUsing(fn (Episode $record): string => EditorialReadiness::label($record))
                    ->color(fn (Episode $record): string => EditorialReadiness::color($record))
                    ->tooltip(fn (Episode $record): string => EditorialReadiness::summary($record)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Episode $record): string => EditorialReadiness::status($record))
                    ->color(fn (Episode $record): string => EditorialReadiness::statusColor($record)),
                TextColumn::make('published_at')
                    ->label('Published Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(function (?int $state): string {
                        if (! $state) {
                            return '—';
                        }

                        return sprintf('%d:%02d', floor($state / 60), $state % 60);
                    }),
            ])
            ->filters([
                Filter::make('missing_artwork')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->whereNull('cover_image')->orWhere('cover_image', '');
                    })),
                Filter::make('missing_seo')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->whereNull('meta_title')->orWhere('meta_title', '')
                            ->orWhereNull('meta_description')->orWhere('meta_description', '');
                    })),
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
