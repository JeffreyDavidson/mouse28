<?php

declare(strict_types=1);

test('external public fonts match the stylesheet and contain WOFF2 data', function (): void {
    $root = dirname(__DIR__, 2);
    $stylesheet = file_get_contents($root.'/resources/css/app.css');
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
        ->and($externalFonts)->toBe($stylesheetFonts);

    foreach ($externalFonts as $font) {
        expect(is_file($root.'/public'.$font))->toBeTrue()
            ->and(file_get_contents($root.'/public'.$font, false, null, 0, 4))->toBe('wOF2');
    }
});
