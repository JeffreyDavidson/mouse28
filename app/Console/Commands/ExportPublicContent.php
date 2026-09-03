<?php

namespace App\Console\Commands;

use App\Support\PublicContentArchive;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Throwable;

#[Signature('content:export-public {path : Absolute path for the JSON archive}')]
#[Description('Export only currently published Mouse28 content and public podcast metadata')]
class ExportPublicContent extends Command
{
    public function handle(PublicContentArchive $archive): int
    {
        $path = (string) $this->argument('path');

        try {
            File::ensureDirectoryExists(dirname($path));
            $written = File::put($path, json_encode($archive->export(), JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ($written === false) {
            $this->error("Unable to write the public content archive to {$path}.");

            return self::FAILURE;
        }

        $this->info("Public content archive written to {$path}.");

        return self::SUCCESS;
    }
}
