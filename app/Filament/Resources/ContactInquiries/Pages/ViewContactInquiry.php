<?php

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Jobs\SendContactInquiryEmails;
use App\Models\ContactInquiry;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** @property ContactInquiry $record */
class ViewContactInquiry extends ViewRecord
{
    #[\Override]
    protected static string $resource = ContactInquiryResource::class;

    /**
     * Opening a new inquiry moves it to in progress, as opening a message used to mark it read.
     */
    public function mount(int|string $record): void
    {
        parent::mount($record);

        if ($this->record->status === ContactInquiryStatus::New) {
            $this->record->update(['status' => ContactInquiryStatus::InProgress]);
        }
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist->schema([
            Section::make('Message Details')
                ->schema([
                    TextEntry::make('name')
                        ->icon(Heroicon::OutlinedUser),
                    TextEntry::make('email')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->copyable(),
                    TextEntry::make('type')
                        ->badge()
                        ->color('warning'),
                    TextEntry::make('created_at')
                        ->dateTime('M j, Y g:i A')
                        ->label('Received'),
                    TextEntry::make('status')
                        ->badge(),
                ])
                ->columns(2),
            Section::make('Message')
                ->schema([
                    TextEntry::make('message')
                        ->prose()
                        ->hiddenLabel(),
                ]),
            Section::make('Email status')
                ->description('Sent means handed to the mail service, not confirmed inbox delivery. Older messages have no recorded status.')
                ->schema([
                    TextEntry::make('notification_sent_at')
                        ->label('Administrator notification sent')
                        ->dateTime('M j, Y g:i A')
                        ->placeholder(fn (ContactInquiry $record): string => $record->email_attempted_at === null ? 'Not tracked' : 'Not sent'),
                    TextEntry::make('confirmation_sent_at')
                        ->label('Sender confirmation sent')
                        ->dateTime('M j, Y g:i A')
                        ->placeholder(fn (ContactInquiry $record): string => $record->email_attempted_at === null ? 'Not tracked' : 'Not sent'),
                ])
                ->columns(2),
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryEmails')
                ->label('Retry unsent emails')
                ->icon(Heroicon::OutlinedArrowPath)
                ->authorize('update')
                ->visible(fn (ContactInquiry $record): bool => $record->email_attempted_at !== null
                    && ($record->notification_sent_at === null || $record->confirmation_sent_at === null))
                ->requiresConfirmation()
                ->modalDescription('Only emails without a recorded successful send will be retried. A provider timeout can leave delivery uncertain; check the provider before retrying.')
                ->disabled(fn (ContactInquiry $record): bool => ! $record->canRetryEmails())
                ->tooltip('Retries are available for 23 hours after submission. Older messages require manual review with the mail provider.')
                ->action(function (ContactInquiry $record): void {
                    dispatch(new SendContactInquiryEmails($record->id));

                    Notification::make()
                        ->title('Email delivery queued')
                        ->body('Refresh this page shortly to check the delivery status.')
                        ->success()
                        ->send();
                }),
            ContactInquiryResource::markResolvedAction(),
            Action::make('reply')
                ->label('Reply')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->url(fn (): string => $this->record->replyMailtoUrl())
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
