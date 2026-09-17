<?php

declare(strict_types=1);

use Pest\Rector\Rules\SimplifyToLiteralBooleanRector;
use Pest\Rector\Rules\UseToBeFileRector;
use Pest\Rector\Set\PestSetList;
use Rector\Config\RectorConfig;
use RectorLaravel\Rector\MethodCall\AssertSeeToAssertSeeHtmlRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/tests',
    ])
    ->withPhpSets()
    ->withSets([
        PestSetList::CODING_STYLE,
    ])
    ->withComposerBased(laravel: true)
    ->withSkip([
        SimplifyToLiteralBooleanRector::class,
        UseToBeFileRector::class,
    ]);
