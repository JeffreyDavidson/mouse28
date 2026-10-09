<?php

namespace App\Filament\Resources\ContactInquiries;

use App\Enums\ContactInquiryStatus;
use App\Enums\NavigationGroup;
use App\Filament\Resources\ContactInquiries\Pages\ListContactInquiries;
use App\Filament\Resources\ContactInquiries\Pages\ViewContactInquiry;
use App\Filament\Resources\ContactInquiries\Tables\ContactInquiriesTable;
use App\Models\ContactInquiry;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContactInquiryResource extends Resource
{
    /**
     * The badge colour of an inquiry's type, shared by the table and the view page.
     */
    public const string TYPE_BADGE_COLOR = 'warning';

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
    protected static string|\UnitEnum|null $navigationGroup = NavigationGroup::Communication;

    #[\Override]
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $count = Cache::store('array')->remember(
            'filament.contact-inquiries.new-count',
            60,
            fn (): int => ContactInquiry::query()
                ->new()
                ->count(),
        );

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function table(Table $table): Table
    {
        return ContactInquiriesTable::configure($table);
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
