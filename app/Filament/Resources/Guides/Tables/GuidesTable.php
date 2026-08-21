<?php

namespace App\Filament\Resources\Guides\Tables;

use App\Models\Guide;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(function (Guide $record): string {
                        if (! $record->is_published) {
                            return 'Draft';
                        }

                        return $record->published_at?->isFuture() ? 'Scheduled' : 'Published';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Published' => 'success',
                        'Scheduled' => 'warning',
                        'Draft' => 'gray',
                    }),
                TextColumn::make('published_at')->date()->sortable(),
            ])
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
