<?php

use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;

pest()->use(RefreshDatabase::class);

test('administrators can read and delete contact inquiries but never create or edit them', function (): void {
    actingAs(User::factory()
        ->admin()
        ->create());
    $inquiry = ContactInquiry::factory()->create();

    expect(ContactInquiryResource::canViewAny())->toBeTrue()
        ->and(ContactInquiryResource::canView($inquiry))
        ->toBeTrue()
        ->and(ContactInquiryResource::canDelete($inquiry))
        ->toBeTrue()
        ->and(ContactInquiryResource::canCreate())
        ->toBeFalse()
        ->and(ContactInquiryResource::canEdit($inquiry))
        ->toBeFalse();
});

test('encrypted contact inquiries are left out of global search', function (): void {
    expect(ContactInquiryResource::canGloballySearch())->toBeFalse();
});
