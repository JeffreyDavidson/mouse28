<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Throwable;

/**
 * One-off move from MySQL to SQLite: builds a new SQLite file from the migrations,
 * copies every row of the current database into it (keeping ids), then proves the copy
 * by comparing each table's row count and a content checksum. The cache tables and
 * Telescope's debug tables are left behind; they rebuild themselves. So are empty tables
 * the migrations no longer create (leftovers of removed features); one that still holds
 * rows stops the copy.
 */
#[Signature('db:copy-to-sqlite {target : Absolute path for the new SQLite database file} {--force : Allow running in production}')]
#[Description('Copy the current database into a new, freshly migrated SQLite file and verify every table')]
class CopyDatabaseToSqlite extends Command
{
    private const string TARGET_CONNECTION = 'sqlite_copy_target';

    /** @var list<string> */
    private const array SKIPPED_TABLES = ['migrations', 'cache', 'cache_locks', 'telescope_entries', 'telescope_entries_tags', 'telescope_monitoring'];

    private const int INSERT_CHUNK = 200;

    public function handle(): int
    {
        if (app()->isProduction() && $this->option('force') !== true) {
            $this->error('Copying the database in production needs --force.');

            return self::FAILURE;
        }

        $target = $this->argument('target');

        if (! is_string($target) || ! str_starts_with($target, '/')) {
            $this->error('The target path must be absolute.');

            return self::FAILURE;
        }

        if (File::exists($target)) {
            $this->error("The target {$target} already exists. Remove it or choose a new path.");

            return self::FAILURE;
        }

        if (! File::isDirectory(dirname($target))) {
            $this->error('The target folder does not exist.');

            return self::FAILURE;
        }

        File::put($target, '');

        try {
            $copy = $this->migratedCopy($target);
            $source = DB::connection();
            $this->assertSameMigrations($source, $copy);
            $tables = $this->tablesToCopy($source, $copy);

            $copy->transaction(function () use ($source, $copy, $tables): void {
                foreach ($tables as $table) {
                    $this->copyTable($source, $copy, $table);
                }
            });

            $results = $this->verify($source, $copy, $tables);
            $copy->statement('PRAGMA wal_checkpoint(TRUNCATE)');
        } catch (Throwable $exception) {
            DB::purge(self::TARGET_CONNECTION);
            File::delete([$target, "{$target}-wal", "{$target}-shm"]);
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            DB::purge(self::TARGET_CONNECTION);
        }

        $this->table(['Table', 'Source rows', 'Copied rows', 'Checksum'], $results);

        $mismatches = array_filter($results, fn (array $row): bool => $row[3] !== 'match' || $row[1] !== $row[2]);

        if ($mismatches !== []) {
            $this->error(count($mismatches).' tables do not match. Do not switch to this file.');

            return self::FAILURE;
        }

        $this->info(count($results)." tables copied; every count and checksum match. The copy is at {$target}.");

        return self::SUCCESS;
    }

    /** A connection to the new file, built from the migrations with foreign keys off for the copy. */
    private function migratedCopy(string $target): Connection
    {
        config()->set('database.connections.'.self::TARGET_CONNECTION, [
            ...config()->array('database.connections.sqlite'),
            'url' => null,
            'database' => $target,
            'foreign_key_constraints' => false,
        ]);

        // Telescope's migration creates its tables on Telescope's own connection and ignores
        // --database, so point Telescope at the new file while it is being migrated.
        $telescopeConnection = config('telescope.storage.database.connection');
        config()->set('telescope.storage.database.connection', self::TARGET_CONNECTION);

        try {
            $this->callSilently('migrate', ['--database' => self::TARGET_CONNECTION, '--force' => true]);
        } finally {
            config()->set('telescope.storage.database.connection', $telescopeConnection);
        }

        return DB::connection(self::TARGET_CONNECTION);
    }

    private function assertSameMigrations(Connection $source, Connection $copy): void
    {
        $missing = array_diff($this->migrationNames($source), $this->migrationNames($copy));
        $extra = array_diff($this->migrationNames($copy), $this->migrationNames($source));

        if ($missing !== [] || $extra !== []) {
            throw new RuntimeException('The source and the new file have run different migrations: '.implode(', ', [...$missing, ...$extra]).'. Run the pending migrations on the source first.');
        }
    }

    /** @return list<string> */
    private function migrationNames(Connection $connection): array
    {
        return array_values($connection->table('migrations')
            ->pluck('migration')
            ->map(fn (mixed $name): string => $this->text($name) ?? '')
            ->all());
    }

    /**
     * The tables to copy: every table both databases have, with the same columns.
     *
     * @return list<string>
     */
    private function tablesToCopy(Connection $source, Connection $copy): array
    {
        $sourceTables = array_diff($this->tableNames($source), self::SKIPPED_TABLES);
        $copyTables = array_diff($this->tableNames($copy), self::SKIPPED_TABLES);
        $retired = array_diff($sourceTables, $copyTables);
        $retiredWithRows = array_filter($retired, fn (string $table): bool => $source->table($table)
            ->exists());

        if ($retiredWithRows !== []) {
            throw new RuntimeException('These tables are not created by the migrations but still hold rows, so copying would lose them: '.implode(', ', $retiredWithRows).'.');
        }

        if ($retired !== []) {
            $this->warn('Left behind (empty, no longer created by the migrations): '.implode(', ', $retired).'.');
        }

        $sourceTables = array_diff($sourceTables, $retired);

        foreach ($sourceTables as $table) {
            $sourceColumns = $source->getSchemaBuilder()
                ->getColumnListing($table);
            $copyColumns = $copy->getSchemaBuilder()
                ->getColumnListing($table);
            sort($sourceColumns);
            sort($copyColumns);

            if ($sourceColumns !== $copyColumns) {
                throw new RuntimeException("The {$table} table has different columns in the source and the new file.");
            }
        }

        return array_values($sourceTables);
    }

    /** @return list<string> */
    private function tableNames(Connection $connection): array
    {
        $schema = $connection->getSchemaBuilder();

        return $schema->getTableListing($schema->getCurrentSchemaName(), false);
    }

    /**
     * Some migrations seed rows (the categories, the author users), so the new file is
     * emptied first: the source's rows, edits included, are the ones that count.
     */
    private function copyTable(Connection $source, Connection $copy, string $table): void
    {
        $copy->table($table)
            ->delete();

        $source->table($table)
            ->get()
            ->map(fn (object $row): array => (array) $row)
            ->chunk(self::INSERT_CHUNK)
            ->each(fn (Collection $rows) => $copy->table($table)
                ->insert($rows->values()
                    ->all()));
    }

    /**
     * Each table's row counts and whether the contents match.
     *
     * @param  list<string>  $tables
     * @return list<array{0: string, 1: int, 2: int, 3: string}>
     */
    private function verify(Connection $source, Connection $copy, array $tables): array
    {
        $copy->statement('PRAGMA foreign_keys = ON');

        if ($copy->select('PRAGMA foreign_key_check') !== []) {
            throw new RuntimeException('The copy has rows that point at missing records (PRAGMA foreign_key_check).');
        }

        $integrity = $copy->selectOne('PRAGMA integrity_check');

        if (! is_object($integrity) || ($integrity->integrity_check ?? null) !== 'ok') {
            throw new RuntimeException('The copied file failed PRAGMA integrity_check.');
        }

        return array_map(fn (string $table): array => [
            $table,
            $source->table($table)
                ->count(),
            $copy->table($table)
                ->count(),
            $this->checksum($source, $table) === $this->checksum($copy, $table) ? 'match' : 'DIFFERENT',
        ], $tables);
    }

    /**
     * A fingerprint of a table's contents that ignores row order and the drivers'
     * value types: every value is compared as text.
     */
    private function checksum(Connection $connection, string $table): string
    {
        $rowHashes = $connection->table($table)
            ->get()
            ->map(function (object $row): string {
                $values = array_map($this->text(...), (array) $row);
                ksort($values);

                return md5(json_encode($values, JSON_THROW_ON_ERROR));
            })
            ->sort()
            ->values()
            ->all();

        return md5(implode('', $rowHashes));
    }

    /** A database value as text, so values compare the same whichever driver returned them. */
    private function text(mixed $value): ?string
    {
        return is_scalar($value) ? (string) $value : null;
    }
}
