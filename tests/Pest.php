<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use Tests\BrowserTestCase;
use Tests\TestCase;

use function Pest\Laravel\artisan;

/** @param array<string, mixed> $parameters */
function pendingCommand(string $command, array $parameters = []): PendingCommand
{
    $pendingCommand = artisan($command, $parameters);

    if (! $pendingCommand instanceof PendingCommand) {
        throw new LogicException('The Artisan command did not return a pending command.');
    }

    return $pendingCommand;
}

pest()->extend(TestCase::class)
    ->in('Feature', 'Integration');

pest()->extend(BrowserTestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Browser');
