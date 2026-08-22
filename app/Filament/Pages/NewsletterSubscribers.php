<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
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

    public function getSubscribers(): array
    {
        try {
            return Cache::remember('newsletter_subscribers', 300, function () {
                $response = Http::withToken(config('services.resend.key'))
                    ->timeout(10)
                    ->get($this->contactsUrl());

                if ($response->successful()) {
                    return $response->json('data', []);
                }

                return [];
            });
        } catch (\Throwable) {
            return [];
        }
    }

    public function getErrorMessage(): ?string
    {
        try {
            $response = Http::withToken(config('services.resend.key'))
                ->timeout(10)
                ->get($this->contactsUrl());

            if (! $response->successful()) {
                return 'Failed to fetch subscribers from Resend API (HTTP '.$response->status().')';
            }
        } catch (\Throwable) {
            return 'Could not connect to the Resend API.';
        }

        return null;
    }

    public function exportCsv(): StreamedResponse
    {
        $subscribers = $this->getSubscribers();

        return response()->streamDownload(function () use ($subscribers) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Email', 'Created At']);
            foreach ($subscribers as $sub) {
                fputcsv($handle, [
                    $sub['email'] ?? '',
                    $sub['created_at'] ?? '',
                ]);
            }
            fclose($handle);
        }, 'newsletter-subscribers-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function contactsUrl(): string
    {
        return 'https://api.resend.com/audiences/'.config('services.resend.audience_id').'/contacts';
    }
}
