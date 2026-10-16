<?php

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\ContactInquiries\ContactInquiryResource;

pest()->use(RefreshDatabase::class);

test('administrators can open, update and delete contact inquiries but never create them', function (): void {
    actingAsAdmin();
    $inquiry = ContactInquiry::factory()->create();

    expect(ContactInquiryResource::canViewAny())->toBeTrue()
        ->and(ContactInquiryResource::canView($inquiry))
        ->toBeTrue()
        ->and(ContactInquiryResource::canDelete($inquiry))
        ->toBeTrue()
        ->and(ContactInquiryResource::canEdit($inquiry))
        ->toBeTrue()
        ->and(ContactInquiryResource::canCreate())
        ->toBeFalse();
});

test('encrypted contact inquiries are left out of global search', function (): void {
    expect(ContactInquiryResource::canGloballySearch())->toBeFalse();
});
