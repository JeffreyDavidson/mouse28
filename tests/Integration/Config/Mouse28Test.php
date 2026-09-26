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
