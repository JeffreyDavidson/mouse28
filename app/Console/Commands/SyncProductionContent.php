<?php

namespace App\Console\Commands;

use App\Support\PublicContentArchive;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

#[Signature('content:sync-production')]
#[Description('Replace local published content and media with the public content currently on Mouse28 production')]
class SyncProductionContent extends Command
{
    private const SSH_OPTIONS = [
        '-o',
        'BatchMode=yes',
        '-o',
        'ConnectTimeout=10',
    ];

    public function handle(PublicContentArchive $archive): int
    {
        if (app()->isProduction()) {
            $this->error('Production content sync may only run in a non-production environment.');

            return self::FAILURE;
        }

        $host = (string) config('mouse28.production_sync.ssh_host');
        $sitePath = rtrim((string) config('mouse28.production_sync.site_path'), '/');

        if (! $this->sourceIsSafe($host, $sitePath)) {
            $this->error('Production sync source configuration is invalid.');

            return self::FAILURE;
        }

        $syncId = (string) Str::uuid();
        $remoteArchivePath = "/tmp/mouse28-public-content-{$syncId}.json";
        $localDirectory = storage_path("framework/content-sync/{$syncId}");
        $localArchivePath = "{$localDirectory}/content.json";
        $mediaManifestPath = "{$localDirectory}/media.txt";
        $remoteArchiveCreated = false;

        File::ensureDirectoryExists($localDirectory);

        try {
            $export = $this->runProcess([
                'ssh',
                ...self::SSH_OPTIONS,
                $host,
                'php',
                "{$sitePath}/artisan",
                'content:export-public',
                $remoteArchivePath,
                '--no-interaction',
            ]);

            if ($export->failed()) {
                return $this->processFailure('Production content export failed.', $export->errorOutput());
            }

            $remoteArchiveCreated = true;
            $download = $this->runProcess([
                'scp',
                ...self::SSH_OPTIONS,
                "{$host}:{$remoteArchivePath}",
                $localArchivePath,
            ]);

            if ($download->failed()) {
                return $this->processFailure('Production content download failed.', $download->errorOutput());
            }

            /** @var array<string, mixed> $contents */
            $contents = json_decode(File::get($localArchivePath), true, flags: JSON_THROW_ON_ERROR);
            $mediaPaths = $archive->mediaPaths($contents);

            if ($mediaPaths !== []) {
                File::put($mediaManifestPath, implode(PHP_EOL, $mediaPaths).PHP_EOL);
                File::ensureDirectoryExists(Storage::disk('public')->path(''));

                $mediaDownload = $this->runProcess([
                    'rsync',
                    '--archive',
                    '--relative',
                    '--rsh=ssh -o BatchMode=yes -o ConnectTimeout=10',
                    "--files-from={$mediaManifestPath}",
                    "{$host}:{$sitePath}/storage/app/public/",
                    Storage::disk('public')->path(''),
                ]);

                if ($mediaDownload->failed()) {
                    return $this->processFailure('Production media download failed.', $mediaDownload->errorOutput());
                }
            }

            $counts = $archive->sync($contents);

            $this->info(
                "Synced {$counts['posts']} posts, {$counts['guides']} guides, {$counts['episodes']} episodes, "
                ."{$counts['podcast']} podcast record, and ".count($mediaPaths).' media files.',
            );
            $this->info(
                "Removed {$counts['removed_posts']} stale published posts, {$counts['removed_guides']} stale published guides, "
                ."and {$counts['removed_episodes']} stale published episodes. Local drafts were preserved.",
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if ($remoteArchiveCreated) {
                try {
                    $cleanup = $this->runProcess([
                        'ssh',
                        ...self::SSH_OPTIONS,
                        $host,
                        'rm',
                        '-f',
                        '--',
                        $remoteArchivePath,
                    ]);

                    if ($cleanup->failed()) {
                        $this->warn("Unable to remove temporary production archive {$remoteArchivePath}.");
                    }
                } catch (Throwable) {
                    $this->warn("Unable to remove temporary production archive {$remoteArchivePath}.");
                }
            }

            File::deleteDirectory($localDirectory);
        }
    }

    /** @param list<string> $command */
    private function runProcess(array $command): ProcessResult
    {
        return Process::timeout(60)->run($command);
    }

    private function processFailure(string $message, string $errorOutput): int
    {
        $this->error($message);

        if (filled(trim($errorOutput))) {
            $this->line(trim($errorOutput));
        }

        return self::FAILURE;
    }

    private function sourceIsSafe(string $host, string $sitePath): bool
    {
        return ! str_starts_with($host, '-')
            && preg_match('/\A[a-zA-Z0-9._@-]+\z/', $host) === 1
            && preg_match('/\A\/[a-zA-Z0-9._\/-]+\z/', $sitePath) === 1
            && ! in_array('..', explode('/', $sitePath), true);
    }
}
