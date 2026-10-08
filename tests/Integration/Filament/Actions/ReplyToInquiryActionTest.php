<?php

use App\Filament\Actions\ReplyToInquiryAction;
use App\Models\ContactInquiry;
use Filament\Support\Icons\Heroicon;

test('the reply action opens a mailto draft for the inquiry in a new tab', function (): void {
    $inquiry = ContactInquiry::factory()->make();

    $action = ReplyToInquiryAction::make()
        ->record($inquiry);

    expect($action->getName())->toBe('reply')
        ->and($action->getLabel())
        ->toBe('Reply')
        ->and($action->getIcon())
        ->toBe(Heroicon::OutlinedPaperAirplane)
        ->and($action->getUrl())
        ->toBe($inquiry->replyMailtoUrl())
        ->and($action->shouldOpenUrlInNewTab())
        ->toBeTrue();
});
