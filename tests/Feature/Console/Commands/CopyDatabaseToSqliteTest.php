<?php

use App\Console\Commands\CopyDatabaseToSqlite;
use App\Models\ContactInquiry;
use App\Models\Episode;
use App\Models\Post;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

covers(CopyDatabaseToSqlite::class);

pest()->use(RefreshDatabase::class);

beforeEach(fn () => File::ensureDirectoryExists(storage_path('framework/testing')));

afterEach(function (): void {
    DB::purge('copied');
    foreach (['', '-wal', '-shm'] as $suffix) {
        File::delete(copiedDatabasePath().$suffix);
    }
});

/** One target per test process; each test deletes it afterwards. */
function copiedDatabasePath(): string
{
    return storage_path('framework/testing/copied-database-'.getmypid().'.sqlite');
}

/** Opens the copied file as its own connection. */
function copiedDatabase(): Connection
{
    config()->set('database.connections.copied', [
        'driver' => 'sqlite',
        'database' => copiedDatabasePath(),
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    return DB::connection('copied');
}

test('every table is copied into a new sqlite file with the same rows', function (): void {
    Post::factory()
        ->credited()
        ->count(2)
        ->create();
    Episode::factory()->create();
    ContactInquiry::factory()->create();

    $exitCode = pendingCommand('db:copy-to-sqlite', ['target' => copiedDatabasePath()])
        ->expectsOutputToContain('match')
        ->run();

    $copy = copiedDatabase();
    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($copy->table('posts')
            ->orderBy('id')
            ->pluck('slug')
            ->all())
        ->toBe(Post::query()
            ->orderBy('id')
            ->pluck('slug')
            ->all())
        ->and($copy->table('post_user')
            ->count())
        ->toBe(DB::table('post_user')->count())
        ->and($copy->table('episodes')
            ->value('title'))
        ->toBe(Episode::query()->value('title'))
        ->and($copy->table('contact_inquiries')
            ->count())
        ->toBe(1)
        ->and($copy->table('migrations')
            ->pluck('migration')
            ->sort()
            ->values()
            ->all())
        ->toBe(DB::table('migrations')
            ->pluck('migration')
            ->sort()
            ->values()
            ->all())
        ->and($copy->select('PRAGMA foreign_key_check'))
        ->toBeEmpty()
        ->and($copy->selectOne('PRAGMA integrity_check'))
        ->toEqual((object) ['integrity_check' => 'ok']);
});

test('rows the migrations seed are replaced by the source rows, edits included', function (): void {
    DB::table('categories')
        ->where('slug', 'disney-tips')
        ->update(['name' => 'Renamed Tips']);

    pendingCommand('db:copy-to-sqlite', ['target' => copiedDatabasePath()])->assertSuccessful();

    expect(copiedDatabase()->table('categories')
        ->where('slug', 'disney-tips')
        ->value('name'))->toBe('Renamed Tips')
        ->and(copiedDatabase()->table('categories')
            ->count())
        ->toBe(DB::table('categories')->count());
});

test('the cache tables are left out of the copy', function (): void {
    DB::table('cache')->insert(['key' => 'cached', 'value' => 'value', 'expiration' => time() + 60]);

    pendingCommand('db:copy-to-sqlite', ['target' => copiedDatabasePath()])->assertSuccessful();

    expect(copiedDatabase()->table('cache')
        ->count())->toBe(0);
});

test('telescope debug entries are left out of the copy', function (): void {
    DB::table('telescope_entries')->insert([
        'uuid' => (string) Str::uuid(),
        'batch_id' => (string) Str::uuid(),
        'family_hash' => null,
        'should_display_on_index' => true,
        'type' => 'request',
        'content' => '{}',
        'created_at' => now(),
    ]);

    pendingCommand('db:copy-to-sqlite', ['target' => copiedDatabasePath()])->assertSuccessful();

    expect(copiedDatabase()->table('telescope_entries')
        ->count())->toBe(0);
})->skip(fn (): bool => getenv('MOUSE28_TEST_MYSQL') === '1', 'In the MySQL lane Telescope keeps its tables on the SQLite test connection.');

test('the copy refuses a target that already exists', function (): void {
    File::put(copiedDatabasePath(), 'existing');

    $exitCode = pendingCommand('db:copy-to-sqlite', ['target' => copiedDatabasePath()])
        ->expectsOutputToContain('already exists')
        ->run();

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(File::get(copiedDatabasePath()))
        ->toBe('existing');
});

test('the copy refuses a relative target path', function (): void {
    $exitCode = pendingCommand('db:copy-to-sqlite', ['target' => 'database/copy.sqlite'])
        ->expectsOutputToContain('absolute')
        ->run();

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(File::exists(base_path('database/copy.sqlite')))
        ->toBeFalse();
});

test('the copy refuses to run in production without --force', function (): void {
    app()->detectEnvironment(fn (): string => 'production');

    $exitCode = pendingCommand('db:copy-to-sqlite', ['target' => copiedDatabasePath()])
        ->expectsOutputToContain('--force')
        ->run();

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(File::exists(copiedDatabasePath()))
        ->toBeFalse();
});

test('an empty source table the migrations no longer create is left behind and named', function (): void {
    Schema::create('retired_table', fn (Blueprint $table) => $table->id());

    $exitCode = pendingCommand('db:copy-to-sqlite', ['target' => copiedDatabasePath()])
        ->expectsOutputToContain('retired_table')
        ->run();

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and(copiedDatabase()->getSchemaBuilder()
            ->hasTable('retired_table'))
        ->toBeFalse();
})->skip(fn (): bool => getenv('MOUSE28_TEST_MYSQL') === '1', 'Creating a table commits the MySQL test transaction.');

test('the copy stops when a table the migrations no longer create still holds rows', function (): void {
    Schema::create('retired_table', fn (Blueprint $table) => $table->id());
    DB::table('retired_table')->insert(['id' => 1]);

    $exitCode = pendingCommand('db:copy-to-sqlite', ['target' => copiedDatabasePath()])
        ->expectsOutputToContain('retired_table')
        ->run();

    expect($exitCode)->toBe(Command::FAILURE)
        ->and(File::exists(copiedDatabasePath()))
        ->toBeFalse();
})->skip(fn (): bool => getenv('MOUSE28_TEST_MYSQL') === '1', 'Creating a table commits the MySQL test transaction.');
