<?php

use App\Models\Concerns\HasAuthors;
use App\Models\Guide;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

covers(HasAuthors::class);

pest()->use(RefreshDatabase::class);

dataset('authored content', [
    'post' => [fn (): Post => Post::factory()->create(), 'post_user', 'post_id'],
    'guide' => [fn (): Guide => Guide::factory()->create(), 'guide_user', 'guide_id'],
]);

dataset('authored records', [
    'post' => [fn (): Post => Post::factory()->create()],
    'guide' => [fn (): Guide => Guide::factory()->create()],
]);

test('content credits several authors in position order', function (Post|Guide $record): void {
    [$first, $second, $third] = User::factory()
        ->author()
        ->count(3)
        ->create()
        ->all();

    $record->authors()
        ->attach([$third->id => ['position' => 2], $first->id => ['position' => 0], $second->id => ['position' => 1]]);

    expect($record->refresh()
        ->authors->modelKeys())->toBe([$first->id, $second->id, $third->id])
        ->and(DB::table($record->authors()
            ->getTable())
            ->orderBy('position')
            ->pluck('position')
            ->all())
        ->toEqual([0, 1, 2]);
})->with('authored content');

test('syncing authors keeps the given order as the byline order and replaces earlier credits', function (Post|Guide $record): void {
    [$first, $second, $third] = User::factory()
        ->author()
        ->count(3)
        ->create()
        ->all();
    $record->syncAuthors([$first->id]);

    $record->syncAuthors([$third->id, $second->id, $third->id]);

    expect($record->refresh()
        ->authors->modelKeys())->toBe([$third->id, $second->id]);
})->with('authored content');

test('content cannot credit the same author twice', function (Post|Guide $record, string $pivot, string $contentKey): void {
    $author = User::factory()
        ->author()
        ->create();
    $record->authors()
        ->attach($author);

    expect(fn () => DB::table($pivot)->insert([$contentKey => $record->id, 'user_id' => $author->id, 'position' => 1]))
        ->toThrow(QueryException::class);
})->with('authored content');

test('force deleting content removes its author credits but keeps the authors', function (Post|Guide $record, string $pivot): void {
    $author = User::factory()
        ->author()
        ->create();
    $record->authors()
        ->attach($author);

    $record->forceDelete();

    expect(DB::table($pivot)->count())->toBe(0)
        ->and($author->exists())
        ->toBeTrue();
})->with('authored content');

test('deleting an author removes their credits but keeps the content', function (Post|Guide $record, string $pivot): void {
    $author = User::factory()
        ->author()
        ->create();
    $record->authors()
        ->attach($author);

    $author->delete();

    expect(DB::table($pivot)->count())->toBe(0)
        ->and($record->refresh()
            ->authors)
        ->toBeEmpty();
})->with('authored content');

test('the byline names one author in full, several by first name in order, and none as the team', function (Post|Guide $record, array $names, string $byline, string $initials): void {
    $record->syncAuthors(array_map(fn (mixed $name): int => User::factory()
        ->author()
        ->create(['name' => $name])
        ->id, $names));

    expect($record->refresh()
        ->author_name)->toBe($byline)
        ->and($record->author_initials)
        ->toBe($initials);
})->with('authored records')
    ->with([
        'Jeffrey' => [['Jeffrey Sample'], 'Jeffrey Sample', 'JS'],
        'Cassie' => [['Cassie Sample'], 'Cassie Sample', 'CS'],
        'both, Jeffrey first' => [['Jeffrey Sample', 'Cassie Sample'], 'Jeffrey & Cassie', 'J&C'],
        'both, Cassie first' => [['Cassie Sample', 'Jeffrey Sample'], 'Cassie & Jeffrey', 'C&J'],
        'three authors' => [['Jeffrey Sample', 'Cassie Sample', 'Avery Sample'], 'Jeffrey & Cassie & Avery', 'J&C&A'],
        'no authors' => [[], 'Mouse28 Team', 'MT'],
    ]);

test('the byline keeps today\'s text for the migrated authors', function (Post|Guide $record, array $names, string $byline): void {
    $record->syncAuthors(User::authors()
        ->whereIn('name', $names)
        ->get()
        ->map(fn (User $author): int => $author->id));

    expect($record->refresh()
        ->author_name)->toBe($byline);
})->with('authored records')
    ->with([
        'jeffrey' => [['Jeffrey Davidson'], 'Jeffrey Davidson'],
        'cassie' => [['Cassie Davidson'], 'Cassie Davidson'],
        'both' => [['Jeffrey Davidson', 'Cassie Davidson'], 'Jeffrey & Cassie'],
    ]);
