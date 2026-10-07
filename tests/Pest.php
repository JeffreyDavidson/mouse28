<?php

use App\Models\Podcast;
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
