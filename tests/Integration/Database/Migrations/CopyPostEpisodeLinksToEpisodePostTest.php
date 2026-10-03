<?php

use App\Models\Episode;
use App\Models\Post;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

pest()->use(RefreshDatabase::class);

function runPostEpisodeLinkCopyMigration(): void
{
    $migration = require database_path('migrations/2026_10_02_235306_copy_post_episode_links_to_episode_post.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The post episode link copy migration could not be loaded.');
    }

    $migration->up();
}

/**
 * Creates a post through the factory, then writes its legacy single episode
 * link directly, as rows written before the pivot existed hold it.
 */
function legacyEpisodeLinkPost(?int $episodeId, bool $trashed = false): int
{
    $post = Post::factory()->create();

    DB::table('posts')->where('id', $post->id)->update([
        'episode_id' => $episodeId,
        'deleted_at' => $trashed ? '2026-09-01 08:00:00' : null,
    ]);

    return $post->id;
}

/** @return list<array<mixed>> */
function episodePostRows(): array
{
    return array_values(DB::table('episode_post')
        ->orderBy('post_id')
        ->orderBy('episode_id')
        ->get(['post_id', 'episode_id'])
        ->map(fn (object $row): array => (array) $row)
        ->all());
}

test('the backfill copies every legacy episode link into the pivot', function (bool $trashedPost, bool $trashedEpisode): void {
    $episode = Episode::factory()->create();
    $postId = legacyEpisodeLinkPost($episode->id, $trashedPost);

    if ($trashedEpisode) {
        $episode->delete();
    }

    runPostEpisodeLinkCopyMigration();

    expect(episodePostRows())->toBe([['post_id' => $postId, 'episode_id' => $episode->id]]);
})->with([
    'a live post' => [false, false],
    'a trashed post' => [true, false],
    'a trashed episode' => [false, true],
]);

test('the backfill skips posts without an episode link', function (): void {
    legacyEpisodeLinkPost(null);
    legacyEpisodeLinkPost(null, trashed: true);

    runPostEpisodeLinkCopyMigration();

    expect(episodePostRows())->toBeEmpty();
});

test('the backfill keeps links already in the pivot', function (): void {
    $legacyEpisode = Episode::factory()->create();
    $extraEpisode = Episode::factory()->create();
    $postId = legacyEpisodeLinkPost($legacyEpisode->id);
    DB::table('episode_post')->insert([
        ['post_id' => $postId, 'episode_id' => $legacyEpisode->id],
        ['post_id' => $postId, 'episode_id' => $extraEpisode->id],
    ]);

    runPostEpisodeLinkCopyMigration();

    expect(episodePostRows())->toBe([
        ['post_id' => $postId, 'episode_id' => min($legacyEpisode->id, $extraEpisode->id)],
        ['post_id' => $postId, 'episode_id' => max($legacyEpisode->id, $extraEpisode->id)],
    ]);
});

test('running the backfill again adds nothing', function (): void {
    legacyEpisodeLinkPost(Episode::factory()->create()->id);
    legacyEpisodeLinkPost(Episode::factory()->create()->id, trashed: true);
    legacyEpisodeLinkPost(null);
    runPostEpisodeLinkCopyMigration();
    $afterFirstRun = episodePostRows();

    runPostEpisodeLinkCopyMigration();

    expect(episodePostRows())->toBe($afterFirstRun)
        ->toHaveCount(2);
});

test('the backfill leaves the legacy episode link column in place', function (): void {
    $episode = Episode::factory()->create();
    $postId = legacyEpisodeLinkPost($episode->id);

    runPostEpisodeLinkCopyMigration();

    expect(DB::table('posts')->where('id', $postId)->value('episode_id'))->toBe($episode->id);
});

test('the backfill refuses to finish while a legacy episode link is missing from the pivot', function (): void {
    $postId = legacyEpisodeLinkPost(Episode::factory()->create()->id);
    // Simulates the link disappearing between the copy and the check.
    DB::listen(function (QueryExecuted $query) use ($postId): void {
        if (str_starts_with($query->sql, 'insert') && str_contains($query->sql, 'episode_post')) {
            DB::table('episode_post')->where('post_id', $postId)->delete();
        }
    });

    expect(fn () => runPostEpisodeLinkCopyMigration())
        ->toThrow(RuntimeException::class, '1 post(s) still have an episode_id with no matching episode_post row after the backfill.');
});
