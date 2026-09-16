<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Actions\SendContactEmails;
use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** @property ContactMessage $record */
class ViewContactMessage extends ViewRecord
{
    #[\Override]
    protected static string $resource = ContactMessageResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->record->is_read) {
            $this->record->update(['is_read' => true]);
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
                    TextEntry::make('subject')
                        ->formatStateUsing(fn (ContactMessage $record): string => $record->subjectLabel())
                        ->badge()
                        ->color('warning'),
                    TextEntry::make('created_at')
                        ->dateTime('M j, Y g:i A')
                        ->label('Received'),
                    IconEntry::make('is_read')
                        ->boolean()
                        ->label('Read'),
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
                        ->placeholder(fn (ContactMessage $record): string => $record->email_attempted_at === null ? 'Not tracked' : 'Not sent'),
                    TextEntry::make('confirmation_sent_at')
                        ->label('Sender confirmation sent')
                        ->dateTime('M j, Y g:i A')
                        ->placeholder(fn (ContactMessage $record): string => $record->email_attempted_at === null ? 'Not tracked' : 'Not sent'),
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
                ->visible(fn (ContactMessage $record): bool => $record->email_attempted_at !== null
                    && ($record->notification_sent_at === null || $record->confirmation_sent_at === null))
                ->requiresConfirmation()
                ->modalDescription('Only emails without a recorded successful send will be retried. A provider timeout can leave delivery uncertain; check the provider before retrying.')
                ->action(function (ContactMessage $record, SendContactEmails $sendContactEmails): void {
                    $sendContactEmails($record);
                    $record->refresh();

                    if ($record->notification_sent_at === null || $record->confirmation_sent_at === null) {
                        Notification::make()
                            ->title('Some emails remain unsent')
                            ->body('Another send may be running, or the mail service could not accept the email. Check the mail service before retrying.')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Emails sent to the mail service')
                        ->success()
                        ->send();
                }),
            Action::make('reply')
                ->label('Reply')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->url(fn () => "mailto:{$this->record->email}?subject=".urlencode('Re: '.$this->record->subjectLabel()))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
