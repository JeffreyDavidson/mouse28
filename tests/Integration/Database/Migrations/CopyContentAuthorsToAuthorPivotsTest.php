<?php

use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use Database\Factories\GuideFactory;
use Database\Factories\PostFactory;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

pest()->use(RefreshDatabase::class);

// A later migration drops the legacy `author` column; put it back so rows look like they did before the copy.
beforeEach(function (): void {
    foreach (['posts', 'guides'] as $table) {
        if (Schema::hasColumn($table, 'author')) {
            continue;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->string('author')
                ->nullable();
        });
    }
});

function runContentAuthorCopyMigration(): void
{
    $migration = require database_path('migrations/2026_10_03_144333_copy_content_authors_to_author_pivots.php');

    if (! $migration instanceof Migration || ! method_exists($migration, 'up')) {
        throw new UnexpectedValueException('The content author copy migration could not be loaded.');
    }

    $migration->up();
}

/**
 * Creates a row through the factory, then writes its legacy author string directly,
 * as rows written before the author pivots existed hold it.
 */
function legacyAuthorRow(PostFactory|GuideFactory $factory, ?string $author, bool $trashed = false): int
{
    $record = $factory->createOne();

    DB::table($record->getTable())
        ->where('id', $record->id)
        ->update([
            'author' => $author,
            'deleted_at' => $trashed ? '2026-09-01 08:00:00' : null,
        ]);

    return $record->id;
}

/** @return list<mixed> the names credited on the row, in position order */
function creditedAuthorNames(string $pivot, string $contentKey, int $id): array
{
    return array_values(DB::table($pivot)
        ->join('users', 'users.id', '=', "{$pivot}.user_id")
        ->where("{$pivot}.{$contentKey}", $id)
        ->orderBy("{$pivot}.position")
        ->pluck('users.name')
        ->all());
}

/** @return array<mixed> */
function authorPivotRows(string $pivot): array
{
    return DB::table($pivot)
        ->orderBy('user_id')
        ->get()
        ->map(fn (object $row): array => (array) $row)
        ->values()
        ->all();
}

/** Removes the author users the migration created when the test database was built. */
function forgetMigratedAuthors(): void
{
    DB::table('users')
        ->where('is_author', true)
        ->delete();
}

dataset('author content tables', [
    'posts' => [fn () => Post::factory(), 'post_user', 'post_id'],
    'guides' => [fn () => Guide::factory(), 'guide_user', 'guide_id'],
]);

dataset('legacy author credits', [
    'jeffrey' => ['jeffrey', ['Jeffrey Davidson']],
    'cassie' => ['cassie', ['Cassie Davidson']],
    'both, Jeffrey first' => ['both', ['Jeffrey Davidson', 'Cassie Davidson']],
]);

test('the migration creates the two authors as non-admin users with bios, placeholder emails and unusable passwords', function (): void {
    forgetMigratedAuthors();

    runContentAuthorCopyMigration();

    $authors = User::query()
        ->where('is_author', true)
        ->orderBy('id')
        ->get();

    expect($authors->map(fn (User $author): array => [$author->name, $author->email, $author->bio, $author->is_admin])
        ->all())->toBe([
            ['Jeffrey Davidson', 'jeffrey@authors.mouse28.invalid', 'Mouse28 co-host, theme park enthusiast, and candid chronicler of Disney family life.', false],
            ['Cassie Davidson', 'cassie@authors.mouse28.invalid', 'Mouse28 co-host, accessibility advocate, and the planner behind the family\'s park days.', false],
        ]);

    foreach ($authors as $author) {
        expect(Hash::isHashed($author->getAuthPassword()))->toBeTrue()
            ->and(Hash::check('password', $author->getAuthPassword()))
            ->toBeFalse()
            ->and($author->email_verified_at)
            ->toBeNull();
    }
});

test('the migration reuses an existing author with the same name and keeps a bio it already has', function (?string $bio, string $expectedBio): void {
    forgetMigratedAuthors();
    $existing = User::factory()
        ->author()
        ->create(['name' => 'Jeffrey Davidson', 'bio' => $bio]);

    runContentAuthorCopyMigration();

    expect(User::query()
        ->where('name', 'Jeffrey Davidson')
        ->pluck('id')
        ->all())->toBe([$existing->id])
        ->and($existing->refresh()
            ->bio)
        ->toBe($expectedBio);
})->with([
    'an edited bio' => ['Edited sample bio.', 'Edited sample bio.'],
    'no bio' => [null, 'Mouse28 co-host, theme park enthusiast, and candid chronicler of Disney family life.'],
]);

test('the migration never reuses a same-named user who is not an author', function (): void {
    forgetMigratedAuthors();
    $admin = User::factory()
        ->admin()
        ->create(['name' => 'Jeffrey Davidson', 'email' => 'sample-admin@example.test']);
    $postId = legacyAuthorRow(Post::factory(), 'jeffrey');

    runContentAuthorCopyMigration();

    $author = User::query()
        ->where('name', 'Jeffrey Davidson')
        ->where('is_author', true)
        ->sole();

    expect($author->id)->not->toBe($admin->id)
        ->and($admin->refresh())
        ->is_author->toBeFalse()
        ->bio->toBeNull()
        ->is_admin->toBeTrue()
        ->and(DB::table('post_user')
            ->where('post_id', $postId)
            ->pluck('user_id')
            ->all())
        ->toBe([$author->id]);
});

test('the backfill credits each legacy author value in byline order', function (PostFactory|GuideFactory $factory, string $pivot, string $contentKey, string $legacyAuthor, array $names, bool $trashed): void {
    $id = legacyAuthorRow($factory, $legacyAuthor, $trashed);

    runContentAuthorCopyMigration();

    expect(creditedAuthorNames($pivot, $contentKey, $id))->toBe($names)
        ->and(DB::table($pivot)
            ->where($contentKey, $id)
            ->orderBy('position')
            ->pluck('position')
            ->all())
        ->toEqual(array_keys($names));
})->with('author content tables')
    ->with('legacy author credits')
    ->with([
        'a live row' => false,
        'a trashed row' => true,
    ]);

test('the backfill leaves a post without a legacy author uncredited', function (bool $trashed): void {
    $postId = legacyAuthorRow(Post::factory(), null, $trashed);

    runContentAuthorCopyMigration();

    expect(DB::table('post_user')
        ->where('post_id', $postId)
        ->count())->toBe(0);
})->with([
    'a live post' => false,
    'a trashed post' => true,
]);

test('the backfill keeps a credit that is already present', function (): void {
    $postId = legacyAuthorRow(Post::factory(), 'both');
    [$jeffreyId, $cassieId] = User::authorIds();
    DB::table('post_user')->insert(['post_id' => $postId, 'user_id' => $jeffreyId, 'position' => 5]);

    runContentAuthorCopyMigration();

    expect(DB::table('post_user')
        ->where('post_id', $postId)
        ->orderBy('position')
        ->get(['user_id', 'position'])
        ->map(fn (object $row): array => (array) $row)
        ->all())
        ->toEqual([['user_id' => $cassieId, 'position' => 1], ['user_id' => $jeffreyId, 'position' => 5]]);
});

test('the backfill leaves the legacy author column in place', function (PostFactory|GuideFactory $factory): void {
    $record = $factory->createOne();
    DB::table($record->getTable())
        ->where('id', $record->id)
        ->update(['author' => 'cassie']);

    runContentAuthorCopyMigration();

    expect(DB::table($record->getTable())
        ->where('id', $record->id)
        ->value('author'))->toBe('cassie');
})->with('author content tables');

test('running the author backfill again changes nothing', function (): void {
    forgetMigratedAuthors();
    legacyAuthorRow(Post::factory(), 'jeffrey');
    legacyAuthorRow(Post::factory(), 'both', trashed: true);
    legacyAuthorRow(Post::factory(), null);
    legacyAuthorRow(Guide::factory(), 'cassie');
    runContentAuthorCopyMigration();
    $users = DB::table('users')
        ->orderBy('id')
        ->get()
        ->map(fn (object $row): array => (array) $row)
        ->all();
    $postCredits = authorPivotRows('post_user');
    $guideCredits = authorPivotRows('guide_user');

    runContentAuthorCopyMigration();

    expect(DB::table('users')
        ->orderBy('id')
        ->get()
        ->map(fn (object $row): array => (array) $row)
        ->all())->toBe($users)
        ->and(authorPivotRows('post_user'))
        ->toBe($postCredits)
        ->and(authorPivotRows('guide_user'))
        ->toBe($guideCredits)
        ->and($postCredits)
        ->toHaveCount(3)
        ->and($guideCredits)
        ->toHaveCount(1);
});

test('the backfill refuses to finish while a legacy author value names no author', function (PostFactory|GuideFactory $factory, string $pivot): void {
    $table = $factory->newModel()
        ->getTable();
    legacyAuthorRow($factory, 'someone-else');

    expect(fn () => runContentAuthorCopyMigration())
        ->toThrow(RuntimeException::class, "1 {$table} row(s) still have an author with no {$pivot} row after the backfill.");
})->with('author content tables');

test('the backfill refuses to finish while a copied credit goes missing', function (): void {
    $postId = legacyAuthorRow(Post::factory(), 'cassie');
    // Simulates the credit disappearing between the copy and the check.
    DB::listen(function (QueryExecuted $query) use ($postId): void {
        if (str_starts_with($query->sql, 'insert') && str_contains($query->sql, 'post_user')) {
            DB::table('post_user')
                ->where('post_id', $postId)
                ->delete();
        }
    });

    expect(fn () => runContentAuthorCopyMigration())
        ->toThrow(RuntimeException::class, '1 posts row(s) still have an author with no post_user row after the backfill.');
});
