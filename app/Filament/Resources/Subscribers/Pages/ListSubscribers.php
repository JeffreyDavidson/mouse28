<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Subscriber;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use JeffreyDavidson\CreatorKit\Enums\SubscriberStatus;

class ListSubscribers extends ListRecords
{
    #[\Override]
    protected static string $resource = SubscriberResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.subscribers.header', [
            'active' => $this->countWithStatus(SubscriberStatus::Active),
            'pending' => $this->countWithStatus(SubscriberStatus::Pending),
            'unsubscribed' => $this->countWithStatus(SubscriberStatus::Unsubscribed),
            'suppressed' => $this->countWithStatus(SubscriberStatus::Suppressed),
        ]);
    }

    private function countWithStatus(SubscriberStatus $status): int
    {
        return Subscriber::query()
            ->withStatus($status)
            ->count();
    }
}
