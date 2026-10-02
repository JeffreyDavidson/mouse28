<?php

namespace App\Filament\Resources\ContactInquiries;

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Filament\Resources\ContactInquiries\Pages\ViewContactInquiry;
use App\Models\ContactInquiry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContactInquiryResource extends Resource
{
    #[\Override]
    protected static ?string $model = ContactInquiry::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Names, emails and messages are encrypted at rest, so the database cannot search them.
     */
    #[\Override]
    protected static bool $isGloballySearchable = false;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::store('array')->remember(
            'filament.contact-inquiries.new-count',
            60,
            fn (): int => ContactInquiry::query()->where('status', ContactInquiryStatus::New)->count(),
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
            ->columns([
                TextColumn::make('name')
                    ->weight(fn (ContactInquiry $record): string => $record->status === ContactInquiryStatus::New ? 'bold' : 'normal')
                    ->icon(Heroicon::OutlinedUser),
                TextColumn::make('email')
                    ->copyable()
                    ->icon(Heroicon::OutlinedEnvelope),
                // Name, email and message are encrypted, so the stored type is the only searchable text.
                TextColumn::make('type')
                    ->searchable()
                    ->badge()
                    ->color('warning'),
                TextColumn::make('message')
                    ->limit(60)
                    ->wrap()
                    ->lineClamp(2),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->since(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(ContactInquiryStatus::class),
                SelectFilter::make('type')
                    ->options(ContactType::class),
            ])
            ->recordActions([
                static::markResolvedAction(),
                Action::make('reply')
                    ->label('Reply')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->url(fn (ContactInquiry $record): string => $record->replyMailtoUrl())
                    ->openUrlInNewTab(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function markResolvedAction(): Action
    {
        return Action::make('markResolved')
            ->label('Mark Resolved')
            ->icon(Heroicon::OutlinedCheck)
            ->authorize('update')
            ->action(fn (ContactInquiry $record) => $record->update(['status' => ContactInquiryStatus::Resolved]))
            ->hidden(fn (ContactInquiry $record): bool => $record->status === ContactInquiryStatus::Resolved);
    }

    /**
     * Inquiries arrive only through the public contact form.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContactInquiries::route('/'),
            'view' => ViewContactInquiry::route('/{record}'),
        ];
    }
}
