<?php

namespace App\Filament\Resources\ContactInquiries\Pages;

use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListContactInquiries extends ListRecords
{
    #[\Override]
    protected static string $resource = ContactInquiryResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.contact-inquiries.header', [
            'total' => ContactInquiry::query()->count(),
            'new' => ContactInquiry::query()
                ->where('status', ContactInquiryStatus::New)
                ->count(),
        ]);
    }
}
