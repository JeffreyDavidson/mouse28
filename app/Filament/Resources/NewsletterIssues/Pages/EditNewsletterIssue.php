<?php

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Actions\SendNewsletterIssue;
use App\Actions\SendNewsletterIssueTestEmail;
use App\Filament\Actions\PublishContentAction;
use App\Filament\Actions\UnpublishContentAction;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Models\NewsletterIssue;
use App\Models\Subscriber;
use App\Support\Content\PreviewUrlGenerator;
use App\Support\DisplayTimezone;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

/** @property NewsletterIssue $record */
class EditNewsletterIssue extends EditRecord
{
    #[\Override]
    protected static string $resource = NewsletterIssueResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.newsletter-issues.form-header', [
            'title' => 'Edit Issue',
            'subtitle' => implode(' · ', array_filter([$this->record->title, $this->deliverySummary()])),
        ]);
    }

    private function deliverySummary(): ?string
    {
        if (! $this->record->wasSent()) {
            return null;
        }

        $total = $this->record->deliveries()->count();
        $delivered = $this->record->deliveries()->whereNotNull('sent_at')->count();

        $sentOn = DisplayTimezone::convert($this->record->sent_at)?->format('M j, Y');

        return "Sent {$sentOn}. Delivered to {$delivered} of {$total} ".Str::plural('subscriber', $total).'.';
    }

    protected function getHeaderActions(): array
    {
        return [
            PublishContentAction::make(),
            UnpublishContentAction::make(),
            Action::make('preview')
                ->icon(Heroicon::OutlinedEye)
                ->authorize('view')
                ->url(fn (PreviewUrlGenerator $previewUrls): string => $previewUrls->for($this->record))
                ->openUrlInNewTab(),
            Action::make('sendTestEmail')
                ->label('Send test email')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->authorize('update')
                ->action(function (SendNewsletterIssueTestEmail $sendTestEmail): void {
                    $this->saveBeforeSending();
                    $recipients = implode(', ', $sendTestEmail->handle($this->record));

                    Notification::make()
                        ->success()
                        ->title("Test email sent to {$recipients}")
                        ->send();
                }),
            Action::make('sendToSubscribers')
                ->label('Send to subscribers')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->authorize('update')
                ->visible(fn (): bool => $this->record->isPublished() && ! $this->record->wasSent())
                ->requiresConfirmation()
                ->modalHeading('Send this issue to subscribers?')
                ->modalDescription(function (): string {
                    $count = Subscriber::query()->active()->count();

                    return "This emails the issue to {$count} active ".Str::plural('subscriber', $count).'. It cannot be undone.';
                })
                ->modalSubmitActionLabel('Send')
                ->action(function (SendNewsletterIssue $sendIssue): void {
                    $this->saveBeforeSending();

                    if (! Subscriber::query()->active()->exists()) {
                        Notification::make()
                            ->warning()
                            ->title('There are no active subscribers to send to')
                            ->send();

                        return;
                    }

                    $queued = $sendIssue->handle($this->record);

                    $this->refreshFormData(['sent_at']);

                    Notification::make()
                        ->success()
                        ->title("Queued for {$queued} ".Str::plural('subscriber', $queued))
                        ->send();
                }),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    /**
     * Save the form so the email matches what is on screen. Invalid data throws a
     * validation exception, which stops the action before anything is sent.
     */
    private function saveBeforeSending(): void
    {
        $this->save(shouldRedirect: false, shouldSendSavedNotification: false);
    }
}
