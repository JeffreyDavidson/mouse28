<?php

namespace App\Console\Commands;

use App\Services\ContentArchive\ProductionContentSource;
use App\Services\ContentArchive\PublicContentArchiveImporter;
use App\Services\ContentArchive\PublicContentImportGuard;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

#[Signature('content:sync-production', aliases: ['content:sync-from-production'])]
#[Description('Replace local published content and media with the public content currently on Mouse28 production')]
class SyncProductionContent extends Command implements Isolatable
{
    public function handle(PublicContentArchiveImporter $archive, ProductionContentSource $source, PublicContentImportGuard $guard): int
    {
        if (! $guard->allows(staging: false)) {
            $this->error('Production content sync may only run in a non-production environment.');

            return self::FAILURE;
        }

        $localDirectory = storage_path('framework/content-sync/'.Str::uuid());
        $localArchivePath = "{$localDirectory}/content.json";

        File::ensureDirectoryExists($localDirectory);

        try {
            $source->exportTo($localArchivePath);

            /** @var array<string, mixed> $contents */
            $contents = json_decode(File::get($localArchivePath), true, flags: JSON_THROW_ON_ERROR);
            $mediaPaths = $archive->mediaPaths($contents);
            $archive->assertSafeToSync($contents);
            $source->copyMedia($mediaPaths);
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
            File::deleteDirectory($localDirectory);
        }
    }
}
