<?php

use App\Enums\ContactInquiryStatus;

test('contact inquiry statuses keep their stored values, labels and colors', function (ContactInquiryStatus $status, string $value, string $label, string $color): void {
    expect($status->value)->toBe($value)
        ->and($status->getLabel())->toBe($label)
        ->and($status->getColor())->toBe($color);
})->with([
    'new' => [ContactInquiryStatus::New, 'new', 'New', 'warning'],
    'in progress' => [ContactInquiryStatus::InProgress, 'in_progress', 'In progress', 'info'],
    'resolved' => [ContactInquiryStatus::Resolved, 'resolved', 'Resolved', 'success'],
]);
