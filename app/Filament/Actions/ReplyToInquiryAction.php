<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Models\ContactInquiry;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Opens a reply draft to the sender of a contact inquiry in the mail client.
 */
class ReplyToInquiryAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reply';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Reply')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->url(fn (ContactInquiry $record): string => $record->replyMailtoUrl())
            ->openUrlInNewTab();
    }
}
