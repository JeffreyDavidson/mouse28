<?php

namespace App\Console\Commands;

use App\Services\ContentArchive\PublicContentArchiveImporter;
use App\Services\ContentArchive\PublicContentImportGuard;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

#[Signature('content:import-public {path : Absolute path to a JSON archive} {--staging : Permit an import on the Mouse28 staging hostname when APP_ENV is production}')]
#[Description('Import a public Mouse28 content archive into staging or another non-production environment')]
class ImportPublicContent extends Command
{
    public function handle(PublicContentArchiveImporter $archive, PublicContentImportGuard $guard): int
    {
        if (! $guard->allows($this->option('staging') === true)) {
            $this->error('Public content cannot be imported into production.');

            return self::FAILURE;
        }

        $path = $this->argument('path');

        if (! is_string($path) || $path === '') {
            $this->error('A valid public content archive path is required.');

            return self::FAILURE;
        }

        if (! File::isFile($path)) {
            $this->error("Public content archive not found at {$path}.");

            return self::FAILURE;
        }

        try {
            /** @var array<string, mixed> $contents */
            $contents = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
            $counts = $archive->import($contents);
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Imported {$counts['posts']} posts, {$counts['guides']} guides, {$counts['episodes']} episodes, and {$counts['podcast']} podcast record.");

        return self::SUCCESS;
    }
}
