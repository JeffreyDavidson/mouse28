<?php

declare(strict_types=1);

namespace App\Filament\Resources\ContactInquiries\Tables;

use App\Enums\ContactInquiryStatus;
use App\Enums\ContactType;
use App\Filament\Actions\ReplyToInquiryAction;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ContactInquiriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->weight(fn (ContactInquiry $record): string => $record->isNew() ? 'bold' : 'normal')
                    ->icon(Heroicon::OutlinedUser),
                TextColumn::make('email')
                    ->copyable()
                    ->icon(Heroicon::OutlinedEnvelope),
                // Name, email and message are encrypted, so the stored type is the only searchable text.
                TextColumn::make('type')
                    ->searchable()
                    ->badge()
                    ->color(ContactInquiryResource::TYPE_BADGE_COLOR),
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
                ContactInquiryResource::markResolvedAction(),
                ReplyToInquiryAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
