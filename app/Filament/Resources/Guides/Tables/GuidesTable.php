<?php

namespace App\Filament\Resources\Guides\Tables;

use App\Models\Guide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GuidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('park')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Guide::PARKS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'magic-kingdom' => 'info',
                        'epcot' => 'success',
                        'hollywood-studios' => 'warning',
                        'animal-kingdom' => 'danger',
                        'general' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Guide::CATEGORIES[$state] ?? $state)
                    ->color('primary'),
                IconColumn::make('is_published')
                    ->label('Published')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->date()
                    ->sortable(),
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
