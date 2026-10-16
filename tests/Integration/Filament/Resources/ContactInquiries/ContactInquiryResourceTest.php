<?php

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Enums\ContactInquiryStatus;
use JeffreyDavidson\CreatorKit\Filament\Resources\ContactInquiries\ContactInquiryResource;

pest()->use(RefreshDatabase::class);

test('the sidebar badge counts only inquiries nobody has opened', function (ContactInquiryStatus $status, ?string $badge): void {
    // Arrange
    ContactInquiry::factory()->create(['status' => $status]);

    // Act
    $count = ContactInquiryResource::getNavigationBadge();

    // Assert
    expect($count)->toBe($badge);
})->with([
    'new' => [ContactInquiryStatus::New, '1'],
    'in progress' => [ContactInquiryStatus::InProgress, null],
    'resolved' => [ContactInquiryStatus::Resolved, null],
]);
