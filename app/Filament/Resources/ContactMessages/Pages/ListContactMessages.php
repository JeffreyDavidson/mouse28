<?php

namespace App\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\ContactMessageResource;
use App\Models\ContactMessage;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListContactMessages extends ListRecords
{
    protected static string $resource = ContactMessageResource::class;

    public function getHeader(): ?View
    {
        $total = ContactMessage::count();
        $unread = ContactMessage::where('is_read', false)->count();

        return view('filament.resources.contact-messages.header', [
            'total' => $total,
            'unread' => $unread,
        ]);
    }
}
