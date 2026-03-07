<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewsletterSubscribers extends Page
{
    protected static string $view = 'filament.pages.newsletter-subscribers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Newsletter Subscribers';

    protected static ?string $title = 'Newsletter Subscribers';

    public function getSubscribers(): array
    {
        try {
            return Cache::remember('newsletter_subscribers', 300, function () {
                $response = Http::withToken(config('services.resend.key'))
                    ->get('https://api.resend.com/audiences/05c28c48-d37a-4429-9fdf-6ee261c023f4/contacts');

                if ($response->successful()) {
                    return $response->json('data', []);
                }

                return [];
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    public function getErrorMessage(): ?string
    {
        try {
            $response = Http::withToken(config('services.resend.key'))
                ->get('https://api.resend.com/audiences/05c28c48-d37a-4429-9fdf-6ee261c023f4/contacts');

            if (!$response->successful()) {
                return 'Failed to fetch subscribers from Resend API (HTTP ' . $response->status() . ')';
            }
        } catch (\Exception $e) {
            return 'Could not connect to Resend API: ' . $e->getMessage();
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
        }, 'newsletter-subscribers-' . now()->format('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
