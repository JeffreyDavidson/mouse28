<?php

namespace App\Filament\Pages;

use App\Support\ResendAudience;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscribers extends Page
{
    use WithPagination;

    #[\Override]
    protected string $view = 'filament.pages.newsletter-subscribers';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 2;

    #[\Override]
    protected static ?string $navigationLabel = 'Newsletter Subscribers';

    #[\Override]
    protected static ?string $title = 'Newsletter Subscribers';

    #[\Override]
    protected ?string $heading = '';

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }

    /** @return array{subscribers: list<array{email: mixed, created_at: mixed, subscription_status: string}>, error: ?string, active_count: int} */
    public function getAudience(): array
    {
        $audience = app(ResendAudience::class)->get();
        $subscribers = array_map(static fn (array $subscriber): array => [
            'email' => $subscriber['email'] ?? null,
            'created_at' => $subscriber['created_at'] ?? null,
            'subscription_status' => match ($subscriber['unsubscribed'] ?? null) {
                false => 'Subscribed',
                true => 'Unsubscribed',
                default => 'Unknown',
            },
        ], $audience['subscribers']);

        return [
            'subscribers' => $subscribers,
            'error' => $audience['error'],
            'active_count' => count(array_filter(
                $subscribers,
                static fn (array $subscriber): bool => $subscriber['subscription_status'] === 'Subscribed',
            )),
        ];
    }

    public function refreshSubscribers(): void
    {
        $this->resetPage();
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
        abort_unless(static::canAccess(), 403);
        $audience = $this->getAudience();
        abort_if($audience['error'] !== null, 503, 'Subscriber export is unavailable. Please try again.');
        $subscribers = $audience['subscribers'];

        return response()->streamDownload(function () use ($subscribers): void {
            $handle = fopen('php://output', 'w');

            if ($handle === false) {
                return;
            }

            fputcsv($handle, ['Email', 'Created At', 'Status'], escape: '\\');
            foreach ($subscribers as $sub) {
                fputcsv($handle, [
                    $this->escapeCsvValue($sub['email'] ?? ''),
                    $this->escapeCsvValue($sub['created_at'] ?? ''),
                    $sub['subscription_status'],
                ],
                    escape: '\\');
            }
            fclose($handle);
        }, 'newsletter-subscribers-'.Date::now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function escapeCsvValue(mixed $value): string
    {
        if (! is_scalar($value) && $value !== null && ! $value instanceof \Stringable) {
            throw new \UnexpectedValueException('Subscriber CSV fields must contain scalar values.');
        }

        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'{$value}" : $value;
    }
}
