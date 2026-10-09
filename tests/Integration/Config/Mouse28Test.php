<?php

afterEach(function (): void {
    unset($_SERVER['GUIDES_ENABLED'], $_ENV['GUIDES_ENABLED']);
});

test('the guides flag is parsed as a boolean', function (string $value, bool $expected): void {
    $_SERVER['GUIDES_ENABLED'] = $value;
    $_ENV['GUIDES_ENABLED'] = $value;

    /** @var array{guides_enabled: mixed} $config */
    $config = require config_path('mouse28.php');

    expect($config['guides_enabled'])->toBe($expected);
})->with([
    'true' => ['true', true],
    'one' => ['1', true],
    'yes' => ['yes', true],
    'false' => ['false', false],
    'zero' => ['0', false],
    'no' => ['no', false],
    'empty' => ['', false],
]);

test('the homepage planning posts come from the three planning categories', function (): void {
    expect(config('mouse28.home_planning_category_slugs'))->toBe(['park-accessibility', 'disney-tips', 'autism-awareness']);
});

test('post artwork styles cover the bundled categories and keep a general fallback', function (): void {
    /** @var array<string, array{wash: string, ink: string, stamp: string}> $styles */
    $styles = config('mouse28.post_artwork_styles');

    expect(array_keys($styles))->toBe([
        'disney-tips',
        'park-accessibility',
        'episode-recap',
        'family-life',
        'autism-awareness',
        'disney-news',
        'food-reviews',
        'resort-reviews',
        'disney-plus',
        'merchandise',
        'general',
    ])
        ->and($styles)
        ->each->toHaveKeys(['wash', 'ink', 'stamp']);
});

test('the legacy search page size setting matches the search page size', function (): void {
    expect(config('mouse28.search_results_per_page'))->toBe(config('search.per_page'));
});
