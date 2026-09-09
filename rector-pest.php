<?php

declare(strict_types=1);

use Pest\Rector\Rules\SimplifyToLiteralBooleanRector;
use Pest\Rector\Rules\UseToBeFileRector;
use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;
use RectorLaravel\Set\LaravelSetProvider;

$config = RectorConfig::configure()
    ->withPaths([
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withSets([
        PestSetList::CODING_STYLE,
    ])
    ->withSkip([
        // Preserve exact empty-array and empty-string assertions.
        SimplifyToLiteralBooleanRector::class,
        // Preserve regular-file checks; toBeFile() also accepts directories.
        UseToBeFileRector::class,
    ]);

// Required by older Laravel Rector versions.
if (class_exists(LaravelSetProvider::class)) {
    $config->withSetProviders(LaravelSetProvider::class);
}

return $config->withComposerBased(laravel: true);
