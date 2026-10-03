<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Copy each post's legacy single episode link (`posts.episode_id`) into the
     * `episode_post` pivot, soft-deleted posts included. Only pairs not already
     * in the pivot are inserted, so the migration can safely run again.
     * `posts.episode_id` stays until a later release drops it.
     */
    public function up(): void
    {
        DB::table('episode_post')->insertUsing(
            ['post_id', 'episode_id'],
            $this->legacyLinksMissingFromPivot()->select(['id', 'episode_id']),
        );

        $this->assertEveryLegacyLinkWasCopied();
    }

    /** @return Builder posts whose `episode_id` has no matching pivot row */
    private function legacyLinksMissingFromPivot(): Builder
    {
        return DB::table('posts')
            ->whereNotNull('episode_id')
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('episode_post')
                    ->whereColumn('episode_post.post_id', 'posts.id')
                    ->whereColumn('episode_post.episode_id', 'posts.episode_id');
            });
    }

    /**
     * Refuse to finish while any post's legacy episode link is missing from the
     * pivot, so the column can never be dropped while it holds a link the pivot lacks.
     */
    private function assertEveryLegacyLinkWasCopied(): void
    {
        $uncopied = $this->legacyLinksMissingFromPivot()->count();

        if ($uncopied > 0) {
            throw new RuntimeException("{$uncopied} post(s) still have an episode_id with no matching episode_post row after the backfill.");
        }
    }
};
