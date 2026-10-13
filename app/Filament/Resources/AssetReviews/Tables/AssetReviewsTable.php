<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetReviews\Tables;

use App\Enums\AssetReviewStatus;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AssetReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('version'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('reviewer.name')
                    ->label('Reviewed by')
                    ->placeholder('Not reviewed'),
                TextColumn::make('reviewed_at')
                    ->label('Reviewed')
                    ->dateTime()
                    ->placeholder('Not reviewed'),
            ])
            ->filters([SelectFilter::make('status')
                ->options(AssetReviewStatus::class)])
            ->recordActions([EditAction::make()
                ->label('Review')]);
    }
}
