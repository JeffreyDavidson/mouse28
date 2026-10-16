<?php

use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffreyDavidson\CreatorKit\Filament\Resources\ContactInquiries\ContactInquiryResource;

use function Pest\Laravel\get;

pest()->use(RefreshDatabase::class);

test('an inquiry link from an email sent before the move opens the inquiry page', function (): void {
    // Arrange
    $inquiry = ContactInquiry::factory()->create();

    // Act
    $response = get(route('legacyContactInquiry', ['record' => $inquiry->id]));

    // Assert
    $response->assertRedirect(ContactInquiryResource::getUrl('edit', ['record' => $inquiry]));
});

test('the old inquiry address still lives under the admin path', function (): void {
    // Arrange
    $inquiry = ContactInquiry::factory()->create();

    // Act
    $url = route('legacyContactInquiry', ['record' => $inquiry->id]);

    // Assert
    expect(parse_url($url, PHP_URL_PATH))->toBe("/admin/contact-inquiries/{$inquiry->id}");
});
