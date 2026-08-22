<?php

namespace App\Filament\Resources\Posts\Tables;

use App\Models\Post;
use App\Support\EditorialReadiness;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

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
                    ->formatStateUsing(fn ($state) => Post::AUTHORS[$state] ?? 'Team')
                    ->color('info'),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'disney-tips' => 'info',
                        'park-accessibility' => 'success',
                        'episode-recap' => 'warning',
                        'family-life' => 'danger',
                        'autism-awareness' => 'primary',
                        default => 'gray',
                    }),
                TextColumn::make('episode.title')
                    ->label('Episode')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('readiness')
                    ->label('Readiness')
                    ->badge()
                    ->getStateUsing(fn (Post $record): string => EditorialReadiness::label($record))
                    ->color(fn (Post $record): string => EditorialReadiness::color($record))
                    ->tooltip(fn (Post $record): string => EditorialReadiness::summary($record)),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->getStateUsing(fn (Post $record): string => EditorialReadiness::status($record))
                    ->color(fn (Post $record): string => EditorialReadiness::statusColor($record)),
                TextColumn::make('published_at')
                    ->label('Published Date')
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
