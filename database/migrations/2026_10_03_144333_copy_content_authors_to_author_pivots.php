<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * The two author users, keyed by the legacy `ContentAuthor` value that credits
     * each alone. Written out here so this migration keeps working after the enum is
     * removed. The emails use the reserved `.invalid` domain so nothing can ever be
     * delivered to them.
     *
     * @var array<string, array{name: string, email: string, bio: string}>
     */
    private const array AUTHORS = [
        'jeffrey' => [
            'name' => 'Jeffrey Davidson',
            'email' => 'jeffrey@authors.mouse28.invalid',
            'bio' => 'Mouse28 co-host, theme park enthusiast, and candid chronicler of Disney family life.',
        ],
        'cassie' => [
            'name' => 'Cassie Davidson',
            'email' => 'cassie@authors.mouse28.invalid',
            'bio' => 'Mouse28 co-host, accessibility advocate, and the planner behind the family\'s park days.',
        ],
    ];

    /**
     * The authors (in byline order) credited by each legacy `author` value.
     *
     * @var array<string, list<string>>
     */
    private const array CREDITS = [
        'jeffrey' => ['jeffrey'],
        'cassie' => ['cassie'],
        'both' => ['jeffrey', 'cassie'],
    ];

    /** @var array<string, string> content table => author pivot table */
    private const array PIVOTS = ['posts' => 'post_user', 'guides' => 'guide_user'];

    /**
     * Create the two dedicated, non-admin author users when no author with that
     * name exists yet (an existing admin account with the same name is never
     * reused), then credit every post and guide, soft-deleted ones included, to the
     * authors its legacy `author` value names. Pairs already in a pivot are skipped,
     * so the migration can safely run again. `posts.author` and `guides.author` stay
     * until a later release drops them.
     */
    public function up(): void
    {
        $authorIds = array_map($this->authorId(...), self::AUTHORS);

        foreach (self::PIVOTS as $contentTable => $pivotTable) {
            $this->creditAuthors($contentTable, $pivotTable, $authorIds);
            $this->assertEveryAuthorWasCredited($contentTable, $pivotTable);
        }
    }

    /** @param array{name: string, email: string, bio: string} $author */
    private function authorId(array $author): int
    {
        $existing = DB::table('users')
            ->where('name', $author['name'])
            ->where('is_author', true)
            ->orderBy('id')
            ->first(['id', 'bio']);

        if ($existing !== null) {
            if ($existing->bio === null) {
                DB::table('users')->where('id', $existing->id)->update(['bio' => $author['bio']]);
            }

            return (int) $existing->id;
        }

        $now = Date::now();

        return DB::table('users')->insertGetId([
            'name' => $author['name'],
            'email' => $author['email'],
            // Inserted through the query builder, so the model's hashed cast does not run.
            'password' => Hash::make(Str::random(64)),
            'is_admin' => false,
            'is_author' => true,
            'bio' => $author['bio'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /** @param array<string, int> $authorIds author user ids keyed by legacy value */
    private function creditAuthors(string $contentTable, string $pivotTable, array $authorIds): void
    {
        $foreignKey = $this->foreignKey($pivotTable);

        foreach (self::CREDITS as $legacyAuthor => $authors) {
            foreach ($authors as $position => $author) {
                $userId = $authorIds[$author];

                DB::table($pivotTable)->insertUsing(
                    [$foreignKey, 'user_id', 'position'],
                    DB::table($contentTable)
                        ->selectRaw('id, ?, ?', [$userId, $position])
                        ->where('author', $legacyAuthor)
                        ->whereNotExists(function (Builder $query) use ($contentTable, $pivotTable, $foreignKey, $userId): void {
                            $query->selectRaw('1')
                                ->from($pivotTable)
                                ->whereColumn("{$pivotTable}.{$foreignKey}", "{$contentTable}.id")
                                ->where("{$pivotTable}.user_id", $userId);
                        }),
                );
            }
        }
    }

    /**
     * Refuse to finish while any post or guide credits an author only in the legacy
     * column, so `author` can never be dropped while it holds a credit the pivot lacks.
     */
    private function assertEveryAuthorWasCredited(string $contentTable, string $pivotTable): void
    {
        $foreignKey = $this->foreignKey($pivotTable);

        $uncredited = DB::table($contentTable)
            ->whereNotNull('author')
            ->whereNotExists(function (Builder $query) use ($contentTable, $pivotTable, $foreignKey): void {
                $query->selectRaw('1')
                    ->from($pivotTable)
                    ->whereColumn("{$pivotTable}.{$foreignKey}", "{$contentTable}.id");
            })
            ->count();

        if ($uncredited > 0) {
            throw new RuntimeException("{$uncredited} {$contentTable} row(s) still have an author with no {$pivotTable} row after the backfill.");
        }
    }

    private function foreignKey(string $pivotTable): string
    {
        return Str::before($pivotTable, '_user').'_id';
    }
};
