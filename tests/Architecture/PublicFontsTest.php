<?php

declare(strict_types=1);

test('external public fonts match the stylesheet and contain WOFF2 data', function (): void {
    $root = dirname(__DIR__, 2);
    $stylesheet = file_get_contents($root.'/resources/css/fonts.css');
    $vite = file_get_contents($root.'/vite.config.js');

    if ($stylesheet === false || $vite === false) {
        throw new RuntimeException('Unable to read public font configuration.');
    }

    preg_match_all('~/fonts/mouse28/[a-z0-9-]+\.woff2~', $stylesheet, $stylesheetMatches);
    preg_match('/external:\s*\[([^\]]+)\]/', $vite, $externalDeclaration);
    preg_match_all('~/fonts/mouse28/[a-z0-9-]+\.woff2~', $externalDeclaration[1] ?? '', $viteMatches);
    $stylesheetFonts = $stylesheetMatches[0];
    $externalFonts = $viteMatches[0];
    sort($stylesheetFonts);
    sort($externalFonts);

    expect($stylesheetFonts)->not->toBeEmpty()
        ->and($externalFonts)
        ->toBe($stylesheetFonts);

    foreach ($externalFonts as $font) {
        expect($root.'/public'.$font)->toBeFile()
            ->and(file_get_contents($root.'/public'.$font, false, null, 0, 4))
            ->toBe('wOF2');
    }
});

test('every public font family ships its open font licence text', function (): void {
    $directory = dirname(__DIR__, 2).'/public/fonts/mouse28';
    $fonts = glob($directory.'/*.woff2');

    if ($fonts === false) {
        throw new RuntimeException('Unable to list the public fonts.');
    }

    $families = array_unique(array_map(
        static fn (string $font): string => ucfirst(explode('-', basename($font))[0]),
        $fonts,
    ));

    expect($families)->not->toBeEmpty();

    foreach ($families as $family) {
        $licence = "{$directory}/OFL-{$family}.txt";

        expect($licence)->toBeFile()
            ->and(file_get_contents($licence))
            ->toContain("The {$family} Project Authors")
            ->toContain('SIL Open Font License, Version 1.1');
    }
});
