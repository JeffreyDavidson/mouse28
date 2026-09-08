<?php

declare(strict_types=1);

use Pest\Rector\Rules\SimplifyToLiteralBooleanRector;
use Pest\Rector\Rules\UseToBeFileRector;
use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/tests',
    ])
    ->withSets([
        PestSetList::CODING_STYLE,
    ])
    ->withSkip([
        // Preserve strict empty-array assertions instead of accepting any empty value.
        SimplifyToLiteralBooleanRector::class,
        // toBeFile() uses file_exists(), which also accepts directories.
        UseToBeFileRector::class,
    ]);
