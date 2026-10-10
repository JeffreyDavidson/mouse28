<?php

declare(strict_types=1);

test('public site icons are real images at their stated sizes', function (string $path, int $size): void {
    $icon = dirname(__DIR__, 2).'/public'.$path;

    expect($icon)->toBeFile();

    $dimensions = getimagesize($icon);

    expect($dimensions)->toBeArray()
        ->and($dimensions[0] ?? null)
        ->toBe($size)
        ->and($dimensions[1] ?? null)
        ->toBe($size)
        ->and($dimensions['mime'] ?? null)
        ->toBe('image/png');
})->with([
    'tab icon' => ['/images/favicon-32.png', 32],
    'small tab icon' => ['/images/favicon-16.png', 16],
    'phone icon' => ['/images/apple-touch-icon.png', 180],
]);

test('the legacy favicon is a real icon file', function (): void {
    $favicon = file_get_contents(dirname(__DIR__, 2).'/public/favicon.ico');

    expect($favicon)->toBeString()
        ->and(substr((string) $favicon, 0, 4))
        ->toBe("\x00\x00\x01\x00");
});
