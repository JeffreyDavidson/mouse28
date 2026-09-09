<?php

namespace App\Filament\Resources\ContactMessages;

use App\Enums\ContactTopic;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Cache;

class ContactMessageResource extends Resource
{
    #[\Override]
    protected static ?string $model = ContactMessage::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'subject'];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::store('array')->remember(
            'filament.contact-messages.unread-count',
            60,
            fn (): int => ContactMessage::query()->where('is_read', false)->count(),
        );

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('All Messages')
            ->description('View and manage messages from site visitors')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(fn (ContactMessage $record) => $record->is_read ? 'normal' : 'bold')
                    ->icon(Heroicon::OutlinedUser),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon(Heroicon::OutlinedEnvelope),
                TextColumn::make('subject')
                    ->formatStateUsing(fn (ContactMessage $record): string => $record->subjectLabel())
                    ->badge()
                    ->color('warning'),
                TextColumn::make('message')
                    ->limit(60)
                    ->wrap(),
                IconColumn::make('is_read')
                    ->boolean()
                    ->label('Read'),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_read')
                    ->label('Read Status'),
                SelectFilter::make('subject')
                    ->options(ContactTopic::class),
            ])
            ->actions([
                Action::make('markRead')
                    ->label('Mark Read')
                    ->icon(Heroicon::OutlinedCheck)
                    ->action(fn (ContactMessage $record) => $record->update(['is_read' => true]))
                    ->hidden(fn (ContactMessage $record) => $record->is_read),
                Action::make('reply')
                    ->label('Reply')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->url(fn (ContactMessage $record) => "mailto:{$record->email}?subject=".urlencode('Re: '.$record->subjectLabel()))
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactMessages::route('/'),
            'view' => Pages\ViewContactMessage::route('/{record}'),
        ];
    }
}
