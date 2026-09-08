<?php

declare(strict_types=1);

test('public CSS scans source files instead of runtime storage', function (): void {
    $path = dirname(__DIR__, 2).'/resources/css/app.css';

    $stylesheet = file_get_contents($path);

    expect($stylesheet)->toContain("@source not '../../storage';")
        ->toContain("@source '../../vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php';")
        ->toContain("@source '../**/*.blade.php';")
        ->toContain("@source '../**/*.js';")
        ->not->toContain("@source '../../storage/framework/views/*.php';");
});
