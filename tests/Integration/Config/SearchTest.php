<?php

afterEach(function (): void {
    unset($_SERVER['MOUSE28_SEARCH_RESULTS_PER_PAGE'], $_ENV['MOUSE28_SEARCH_RESULTS_PER_PAGE']);
});

test('the search page size reads the existing mouse28 environment name and stays positive', function (?string $value, int $expected): void {
    if ($value !== null) {
        $_SERVER['MOUSE28_SEARCH_RESULTS_PER_PAGE'] = $value;
        $_ENV['MOUSE28_SEARCH_RESULTS_PER_PAGE'] = $value;
    }

    /** @var array{per_page: mixed} $config */
    $config = require config_path('search.php');

    expect($config['per_page'])->toBe($expected);
})->with([
    'unset' => [null, 6],
    'configured' => ['9', 9],
    'zero' => ['0', 1],
]);
