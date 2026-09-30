<?php

namespace App\Filament\Resources\Subscribers\Tables;

use App\Enums\SubscriberStatus;
use App\Models\Subscriber;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SubscribersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->icon(Heroicon::OutlinedEnvelope),
                TextColumn::make('status')
                    ->state(fn (Subscriber $record): SubscriberStatus => SubscriberStatus::for($record))
                    ->badge(),
                TextColumn::make('subscribed_at')
                    ->label('Subscribed')
                    ->dateTime('M j, Y')
                    ->sortable(),
                TextColumn::make('unsubscribed_at')
                    ->label('Unsubscribed')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->defaultSort('subscribed_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(SubscriberStatus::class)
                    ->query(function (Builder $query, array $data): void {
                        $status = $data['value'] ?? null;

                        if (! is_string($status)) {
                            return;
                        }

                        SubscriberStatus::tryFrom($status)?->scope($query);
                    }),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedEnvelope)
            ->emptyStateHeading('No subscribers yet')
            ->emptyStateDescription('Readers who sign up through the newsletter form appear here.');
    }
}
