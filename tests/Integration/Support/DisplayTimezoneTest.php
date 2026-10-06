<?php

use App\Support\DisplayTimezone;
use Illuminate\Support\Facades\Date;

covers(DisplayTimezone::class);

test('the display timezone defaults to Eastern time', function (): void {
    expect(DisplayTimezone::name())->toBe('America/New_York');
});

test('a UTC instant is shown on its Eastern calendar day without changing the original', function (string $utc, string $eastern): void {
    $stored = Date::parse($utc, 'UTC');

    $shown = DisplayTimezone::convert($stored);

    expect($shown?->format('Y-m-d H:i'))->toBe($eastern)
        ->and($shown?->getTimestamp())->toBe($stored->getTimestamp())
        ->and($stored->getTimezone()->getName())->toBe('UTC');
})->with([
    'daylight saving time' => ['2026-10-06 01:00:00', '2026-10-05 21:00'],
    'standard time' => ['2027-01-16 02:00:00', '2027-01-15 21:00'],
]);

test('the label names the timezone for helper text', function (): void {
    expect(DisplayTimezone::label())->toBe('Eastern Time (America/New_York)');
});
