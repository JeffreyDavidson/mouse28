<?php

declare(strict_types=1);

/**
 * @return array<mixed>
 */
function composerScripts(): array
{
    $manifest = json_decode((string) file_get_contents(dirname(__DIR__, 2).'/composer.json'), true, flags: JSON_THROW_ON_ERROR);

    if (! is_array($manifest) || ! is_array($manifest['scripts'] ?? null)) {
        throw new RuntimeException('composer.json does not define scripts.');
    }

    return $manifest['scripts'];
}

test('composer scripts use the shared cross-site names', function (string $script): void {
    expect(composerScripts())->toHaveKey($script);
})->with([
    'lint', 'test:lint', 'rector', 'rector:pest', 'test:rector', 'test:rector:pest', 'test:types', 'test:types:pest',
    'test:filament', 'test', 'test:browser', 'test:architecture', 'test:type-coverage', 'check',
]);

test('composer scripts no longer define the retired names', function (string $script): void {
    expect(composerScripts())->not->toHaveKey($script);
})->with(['analyse', 'analyse:pest', 'test:filacheck', 'lint:check', 'test:parallel']);

test('composer check runs the shared gates in contract order', function (): void {
    $steps = composerScripts()['check'];

    if (! is_array($steps)) {
        throw new RuntimeException('The check script must list its steps.');
    }

    $contractOrder = ['@composer validate', '@composer audit', 'npm audit', '@test:lint', '@test:filament', '@test:types', '@test:types:pest',
        '@test:rector', '@test:rector:pest', '@test', '@test:type-coverage', 'npm run build', '@test:browser'];
    $positions = array_map(function (string $gate) use ($steps): int|false {
        foreach ($steps as $index => $step) {
            if (is_string($step) && ($step === $gate || str_starts_with($step, "{$gate} ") || str_starts_with($step, "{$gate}:smoke"))) {
                return $index;
            }
        }

        return false;
    }, $contractOrder);

    expect($positions)->not->toContain(false);

    $sorted = $positions;
    sort($sorted);

    expect($positions)->toBe($sorted);
});
