<?php

use JeffreyDavidson\CreatorKit\Enums\PublishStatus;

test('publish statuses keep their stored values', function (): void {
    expect(array_column(PublishStatus::cases(), 'value'))->toBe(['draft', 'in_review', 'published', 'scheduled']);
});

test('publish statuses expose a label and badge color', function (PublishStatus $status, string $label, string $color): void {
    expect($status->getLabel())->toBe($label)
        ->and($status->getColor())
        ->toBe($color);
})->with([
    'draft' => [PublishStatus::Draft, 'Draft', 'gray'],
    'in review' => [PublishStatus::InReview, 'In Review', 'info'],
    'published' => [PublishStatus::Published, 'Published', 'success'],
    'scheduled' => [PublishStatus::Scheduled, 'Scheduled', 'warning'],
]);
