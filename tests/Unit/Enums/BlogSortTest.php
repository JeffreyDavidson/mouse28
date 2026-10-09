<?php

use App\Enums\BlogSort;

test('blog sorts keep their query string values', function (): void {
    expect(array_column(BlogSort::cases(), 'value'))->toBe(['newest', 'oldest']);
});

test('blog sorts expose their label and publication date direction', function (BlogSort $sort, string $label, string $direction): void {
    expect($sort->label())->toBe($label)
        ->and($sort->direction())
        ->toBe($direction);
})->with([
    'newest' => [BlogSort::Newest, 'Newest first', 'desc'],
    'oldest' => [BlogSort::Oldest, 'Oldest first', 'asc'],
]);

test('blog sort input falls back to newest first for an unknown value', function (string $input, BlogSort $expected): void {
    expect(BlogSort::fromInput($input))->toBe($expected);
})->with([
    'newest' => ['newest', BlogSort::Newest],
    'oldest' => ['oldest', BlogSort::Oldest],
    'empty' => ['', BlogSort::Newest],
    'unknown' => ['not-a-sort', BlogSort::Newest],
    'wrong case' => ['Oldest', BlogSort::Newest],
]);
