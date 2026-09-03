<?php

namespace App\Console\Commands;

use App\Support\PublicContentArchive;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

#[Signature('content:import-public {path : Absolute path to a JSON archive}')]
#[Description('Import a public Mouse28 content archive into a non-production environment')]
class ImportPublicContent extends Command
{
    public function handle(PublicContentArchive $archive): int
    {
        if (app()->isProduction()) {
            $this->error('Public content cannot be imported into production.');

            return self::FAILURE;
        }

        $path = (string) $this->argument('path');

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
