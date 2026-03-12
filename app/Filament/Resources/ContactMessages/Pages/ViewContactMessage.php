<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Actions\DeleteAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewContactMessage extends ViewRecord
{
    protected static string $resource = ContactMessageResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        if (! $this->record->is_read) {
            $this->record->update(['is_read' => true]);
        }
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Message Details')
                ->schema([
                    TextEntry::make('name')
                        ->icon('heroicon-o-user'),
                    TextEntry::make('email')
                        ->icon('heroicon-o-envelope')
                        ->copyable()
                        ->url(fn (ContactMessage $record) => "mailto:{$record->email}"),
                    TextEntry::make('subject')
                        ->formatStateUsing(fn (string $state) => ContactMessage::SUBJECTS[$state] ?? ucfirst($state))
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
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('reply')
                ->label('Reply')
                ->icon('heroicon-o-paper-airplane')
                ->url(fn () => "mailto:{$this->record->email}?subject=" . urlencode("Re: {$this->record->subject_label}"))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }
}
