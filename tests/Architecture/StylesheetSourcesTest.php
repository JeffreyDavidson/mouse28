<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

test('public CSS scans source files instead of runtime storage', function (): void {
    $path = dirname(__DIR__, 2).'/resources/css/app.css';

    $stylesheet = file_get_contents($path);

    expect($stylesheet)->toContain("@source not '../../storage';")
        ->toContain("@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';")
        ->toContain("@source '../**/*.blade.php';")
        ->toContain("@source '../**/*.js';")
        ->not->toContain("@source '../../storage/framework/views/*.php';");
});

test('views only use site palette colours the theme defines', function (): void {
    $root = dirname(__DIR__, 2);
    $stylesheets = file_get_contents("{$root}/resources/css/app.css").file_get_contents("{$root}/resources/css/filament/admin/theme.css");
    preg_match_all('/--color-([a-z0-9-]+)\s*:/', $stylesheets, $tokens);
    $defined = array_unique($tokens[1]);
    $undefined = [];

    foreach (Finder::create()
        ->files()
        ->in("{$root}/resources/views")
        ->name('*.blade.php') as $file) {
        preg_match_all('/\b(?:bg|text|border|ring|outline|decoration|from|via|to|fill|stroke|divide|placeholder|accent|caret|shadow)-((?:[a-z]+-)?(?:navy|cream|gold|purple)(?:-[a-z]+)?)\b/', $file->getContents(), $matches);

        foreach (array_diff(array_unique($matches[1]), $defined) as $colour) {
            $undefined[] = "{$file->getRelativePathname()}: {$colour}";
        }
    }

    expect($undefined)->toBeEmpty();
});
