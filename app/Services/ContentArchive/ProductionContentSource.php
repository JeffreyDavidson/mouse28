<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Fetches production's public content for a local sync over SSH: `exportTo()` runs the
 * export on production, downloads the archive and removes the remote copy;
 * `copyMedia()` copies the listed public media files into local public storage. The
 * host and site come from `mouse28.production_sync`. The Laravel Architect's source
 * copies from the same server instead, because its sync runs on staging.
 */
class ProductionContentSource
{
    private const array SSH_OPTIONS = [
        '-o',
        'BatchMode=yes',
        '-o',
        'ConnectTimeout=10',
    ];

    public function exportTo(string $archivePath): void
    {
        [$host, $sitePath] = $this->validatedConfiguration();
        $remoteArchivePath = '/tmp/mouse28-public-content-'.Str::uuid().'.json';

        $this->run('Production content export failed.', [
            'ssh',
            ...self::SSH_OPTIONS,
            $host,
            'php',
            "{$sitePath}/artisan",
            'content:export-public',
            $remoteArchivePath,
            '--no-interaction',
        ]);

        try {
            $this->run('Production content download failed.', [
                'scp',
                ...self::SSH_OPTIONS,
                "{$host}:{$remoteArchivePath}",
                $archivePath,
            ]);
        } finally {
            $this->removeRemoteArchive($host, $remoteArchivePath);
        }
    }

    /** @param list<string> $paths */
    public function copyMedia(array $paths): void
    {
        [$host, $sitePath] = $this->validatedConfiguration();

        if ($paths === []) {
            return;
        }

        $directory = storage_path('framework/content-sync/'.Str::uuid());
        $manifestPath = "{$directory}/media.txt";

        File::ensureDirectoryExists($directory);
        File::put($manifestPath, implode(PHP_EOL, $paths).PHP_EOL);
        File::ensureDirectoryExists(Storage::disk('public')->path(''));

        try {
            $this->run('Production media download failed.', [
                'rsync',
                '--archive',
                '--relative',
                '--rsh=ssh -o BatchMode=yes -o ConnectTimeout=10',
                "--files-from={$manifestPath}",
                "{$host}:{$sitePath}/storage/app/public/",
                Storage::disk('public')->path(''),
            ]);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    /** @param list<string> $command */
    private function run(string $failure, array $command): ProcessResult
    {
        $result = Process::timeout(60)->run($command);

        if ($result->failed()) {
            throw new RuntimeException(trim("{$failure} ".trim($result->errorOutput())));
        }

        return $result;
    }

    private function removeRemoteArchive(string $host, string $remoteArchivePath): void
    {
        try {
            Process::timeout(60)->run(['ssh', ...self::SSH_OPTIONS, $host, 'rm', '-f', '--', $remoteArchivePath]);
        } catch (Throwable) {
            // The export lives in /tmp on production; a leftover copy is harmless.
        }
    }

    /** @return array{string, string} the SSH host and the production site path */
    private function validatedConfiguration(): array
    {
        $host = Config::string('mouse28.production_sync.ssh_host');
        $sitePath = rtrim(Config::string('mouse28.production_sync.site_path'), '/');

        if (str_starts_with($host, '-')
            || preg_match('/\A[a-zA-Z0-9._@-]+\z/', $host) !== 1
            || preg_match('/\A\/[a-zA-Z0-9._\/-]+\z/', $sitePath) !== 1
            || in_array('..', explode('/', $sitePath), true)) {
            throw new RuntimeException('Production sync source configuration is invalid.');
        }

        return [$host, $sitePath];
    }
}
