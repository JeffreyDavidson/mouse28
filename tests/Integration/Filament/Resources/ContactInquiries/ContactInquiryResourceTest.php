<?php

use App\Enums\ContactInquiryStatus;
use App\Filament\Resources\ContactInquiries\ContactInquiryResource;
use App\Models\ContactInquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

test('the new-inquiry badge reuses its count including zero within the request', function (ContactInquiryStatus $status, ?string $badge): void {
    ContactInquiry::factory()->create(['status' => $status]);
    Cache::store('array')->flush();
    DB::enableQueryLog();
    DB::flushQueryLog();

    $first = ContactInquiryResource::getNavigationBadge();
    $second = ContactInquiryResource::getNavigationBadge();

    expect($first)->toBe($badge)
        ->and($second)
        ->toBe($badge)
        ->and(DB::getQueryLog())
        ->toHaveCount(1);
    DB::disableQueryLog();
})->with([
    'new' => [ContactInquiryStatus::New, '1'],
    'in progress' => [ContactInquiryStatus::InProgress, null],
    'resolved' => [ContactInquiryStatus::Resolved, null],
]);
