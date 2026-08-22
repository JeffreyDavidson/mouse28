<?php

namespace App\Filament\Pages;

use App\Support\ResendAudience;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscribers extends Page
{
    protected string $view = 'filament.pages.newsletter-subscribers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Newsletter Subscribers';

    protected static ?string $title = 'Newsletter Subscribers';

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    /** @return array{subscribers: list<array<string, mixed>>, error: ?string} */
    public function getAudience(): array
    {
        return app(ResendAudience::class)->get();
    }

    public function refreshSubscribers(): void
    {
        $audience = app(ResendAudience::class)->refresh();

        $notification = Notification::make();

        if ($audience['error']) {
            $notification
                ->danger()
                ->title('Subscriber refresh failed')
                ->body($audience['error']);
        } else {
            $notification
                ->success()
                ->title('Subscribers refreshed');
        }

        $notification->send();
    }

    public function exportCsv(): StreamedResponse
    {
        $subscribers = $this->getAudience()['subscribers'];

        return response()->streamDownload(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Email', 'Created At']);
            foreach ($subscribers as $sub) {
                fputcsv($handle, [
                    $this->escapeCsvValue($sub['email'] ?? ''),
                    $this->escapeCsvValue($sub['created_at'] ?? ''),
                ]);
            }
            fclose($handle);
        }, 'newsletter-subscribers-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function escapeCsvValue(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'{$value}" : $value;
    }
}
