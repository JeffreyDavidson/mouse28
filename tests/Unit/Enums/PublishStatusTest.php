<?php

use App\Enums\PublishStatus;

test('publish statuses keep their stored values', function (): void {
    expect(array_column(PublishStatus::cases(), 'value'))->toBe(['draft', 'in_review', 'published', 'scheduled']);
});

test('publish statuses expose a label and badge color', function (PublishStatus $status, string $label, string $color): void {
    expect($status->label())->toBe($label)
        ->and($status->getLabel())
        ->toBe($label)
        ->and($status->getColor())
        ->toBe($color);
})->with([
    'draft' => [PublishStatus::Draft, 'Draft', 'gray'],
    'in review' => [PublishStatus::InReview, 'In Review', 'info'],
    'published' => [PublishStatus::Published, 'Published', 'success'],
    'scheduled' => [PublishStatus::Scheduled, 'Scheduled', 'warning'],
]);

test('publish status labels can leave out the review step', function (bool $includeInReview, array $labels): void {
    expect(PublishStatus::labels($includeInReview))->toBe($labels);
})->with([
    'with review' => [true, ['draft' => 'Draft', 'in_review' => 'In Review', 'published' => 'Published', 'scheduled' => 'Scheduled']],
    'without review' => [false, ['draft' => 'Draft', 'published' => 'Published', 'scheduled' => 'Scheduled']],
]);
