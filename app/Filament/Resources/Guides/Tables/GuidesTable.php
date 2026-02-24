<?php

namespace App\Filament\Resources\Guides\Tables;

use App\Models\Guide;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class GuidesTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('icon')->label(''),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('park')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Guide::PARKS[$state] ?? $state),
                TextColumn::make('category')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Guide::CATEGORIES[$state] ?? $state),
                TextColumn::make('sort_order')->sortable(),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('sort_order');
    }
}
