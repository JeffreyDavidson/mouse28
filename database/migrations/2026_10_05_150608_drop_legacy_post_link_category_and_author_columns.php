<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var array<string, string> content table => author pivot table */
    private const array AUTHOR_PIVOTS = ['posts' => 'post_user', 'guides' => 'guide_user'];

    /**
     * Drop the legacy `posts.episode_id` (replaced by the `episode_post` pivot),
     * `posts.category` (replaced by `category_id`) and `author` on posts and guides
     * (replaced by the author pivots). Every column is checked before any column is
     * dropped, so a row that still holds information only in a legacy column stops
     * the migration with nothing changed. Each drop is skipped when the column is
     * already gone, so the migration can safely run again. Dropping the foreign key
     * column rebuilds `posts` on SQLite; `posts` has no CHECK constraint to lose.
     */
    public function up(): void
    {
        $this->assertEveryEpisodeLinkWasCopied();
        $this->assertEveryCategoryWasLinked();

        foreach (self::AUTHOR_PIVOTS as $tableName => $pivotTable) {
            $this->assertEveryAuthorWasCredited($tableName, $pivotTable);
        }

        $this->dropEpisodeId();
        $this->dropColumn('posts', 'category');

        foreach (array_keys(self::AUTHOR_PIVOTS) as $tableName) {
            $this->dropColumn($tableName, 'author');
        }
    }

    private function assertEveryEpisodeLinkWasCopied(): void
    {
        if (! Schema::hasColumn('posts', 'episode_id')) {
            return;
        }

        $uncopied = DB::table('posts')
            ->whereNotNull('episode_id')
            ->whereNotExists(function (Builder $query): void {
                $query->selectRaw('1')
                    ->from('episode_post')
                    ->whereColumn('episode_post.post_id', 'posts.id')
                    ->whereColumn('episode_post.episode_id', 'posts.episode_id');
            })
            ->count();

        if ($uncopied > 0) {
            throw new RuntimeException("{$uncopied} post(s) still have an episode_id with no matching episode_post row.");
        }
    }

    private function assertEveryCategoryWasLinked(): void
    {
        if (! Schema::hasColumn('posts', 'category')) {
            return;
        }

        $unlinked = DB::table('posts')
            ->whereNotNull('category')
            ->whereNull('category_id')
            ->count();

        if ($unlinked > 0) {
            throw new RuntimeException("{$unlinked} post(s) still have a category with no category_id.");
        }
    }

    private function assertEveryAuthorWasCredited(string $tableName, string $pivotTable): void
    {
        if (! Schema::hasColumn($tableName, 'author')) {
            return;
        }

        $foreignKey = str($tableName)->singular()->append('_id')->toString();

        $uncredited = DB::table($tableName)
            ->whereNotNull('author')
            ->whereNotExists(function (Builder $query) use ($tableName, $pivotTable, $foreignKey): void {
                $query->selectRaw('1')
                    ->from($pivotTable)
                    ->whereColumn("{$pivotTable}.{$foreignKey}", "{$tableName}.id");
            })
            ->count();

        if ($uncredited > 0) {
            throw new RuntimeException("{$uncredited} {$tableName} row(s) still have an author with no {$pivotTable} row.");
        }
    }

    private function dropEpisodeId(): void
    {
        if (! Schema::hasColumn('posts', 'episode_id')) {
            return;
        }

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('episode_id');
        });
    }

    private function dropColumn(string $tableName, string $column): void
    {
        if (! Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($column): void {
            $table->dropColumn($column);
        });
    }
};
