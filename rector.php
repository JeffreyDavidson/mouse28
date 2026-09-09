<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use RectorLaravel\Set\LaravelSetProvider;

$config = RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/bootstrap/providers.php',
        __DIR__.'/config',
        __DIR__.'/database/factories',
        __DIR__.'/database/seeders',
        __DIR__.'/routes',
    ])
    ->withPhpSets();

// Laravel Rector before 2.6 requires explicit provider registration.
if (class_exists(LaravelSetProvider::class)) {
    $config->withSetProviders(LaravelSetProvider::class);
}

return $config->withComposerBased(laravel: true);
