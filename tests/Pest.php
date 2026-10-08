<?php

use App\Models\Guide;
use App\Models\Podcast;
use App\Models\Post;
use App\Support\PrimaryPodcast;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\PendingCommand;
use Tests\BrowserTestCase;
use Tests\TestCase;

use function Pest\Laravel\artisan;

require_once __DIR__.'/Browser/helpers.php';

/** @param array<string, mixed> $parameters */
function pendingCommand(string $command, array $parameters = []): PendingCommand
{
    $pendingCommand = artisan($command, $parameters);

    if (! $pendingCommand instanceof PendingCommand) {
        throw new LogicException('The Artisan command did not return a pending command.');
    }

    return $pendingCommand;
}

/** The site's saved podcast show, created with the configured defaults when missing. */
function primaryPodcast(): Podcast
{
    return app(PrimaryPodcast::class)->findOrCreate();
}

/**
 * The ORDER BY clause of every query the callback runs that orders by publish time.
 *
 * @return list<string>
 */
function publishTimeOrderings(Closure $callback): array
{
    $orderings = [];
    DB::listen(function (QueryExecuted $query) use (&$orderings): void {
        if (preg_match('/order by (.*?)(?: limit| offset|$)/i', $query->sql, $match) === 1 && str_contains($match[1], 'published_at')) {
            $orderings[] = $match[1];
        }
    });

    $callback();

    return $orderings;
}

/** Publish-time order with an id tie-breaker in the same direction, so equal times keep a stable order on MySQL. */
const STABLE_PUBLISH_TIME_ORDER = '/"published_at" (asc|desc), "(?:\w+"\.")?id" \1/';

pest()->extend(TestCase::class)
    ->in('Feature', 'Integration');

pest()->extend(BrowserTestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Browser');

/**
 * @param  array<string, mixed>  $archive
 * @return array<array-key, mixed>
 */
function firstArchivedRecord(array $archive, string $type): array
{
    $records = $archive[$type] ?? null;

    if (! is_array($records) || ! is_array($records[0] ?? null)) {
        throw new UnexpectedValueException("The archive has no {$type}.");
    }

    return $records[0];
}

/**
 * Returns the archive with its first record of a type changed, optionally dropping keys.
 *
 * @param  array<string, mixed>  $archive
 * @param  array<string, mixed>  $changes
 * @param  list<string>  $without
 * @return array<string, mixed>
 */
function withFirstArchivedRecord(array $archive, string $type, array $changes, array $without = []): array
{
    $record = firstArchivedRecord($archive, $type);
    foreach ($without as $key) {
        unset($record[$key]);
    }

    return [...$archive, $type => [[...$record, ...$changes]]];
}

dataset('archived written content', [
    'posts' => [fn () => Post::factory(), 'posts'],
    'guides' => [fn () => Guide::factory(), 'guides'],
]);
