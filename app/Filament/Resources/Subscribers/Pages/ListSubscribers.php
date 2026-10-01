<?php

namespace App\Filament\Resources\Subscribers\Pages;

use App\Enums\SubscriberStatus;
use App\Filament\Resources\Subscribers\SubscriberResource;
use App\Models\Subscriber;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

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
        ]);
    }

    private function countWithStatus(SubscriberStatus $status): int
    {
        $query = Subscriber::query();
        $status->scope($query);

        return $query->count();
    }
}
