<?php

namespace App\Filament\Resources\Episodes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label('Published Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('duration_seconds')
                    ->label('Duration')
                    ->formatStateUsing(function (?int $state): string {
                        if (! $state) return '—';
                        return sprintf('%d:%02d', floor($state / 60), $state % 60);
                    }),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
