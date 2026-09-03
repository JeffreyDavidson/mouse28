<?php

namespace App\Console\Commands;

use App\Support\PublicContentArchive;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Uri;
use Throwable;

#[Signature('content:import-public {path : Absolute path to a JSON archive} {--staging : Permit an import on the configured Mouse28 staging hostname}')]
#[Description('Import a public Mouse28 content archive into staging or another non-production environment')]
class ImportPublicContent extends Command
{
    private const STAGING_HOST = 'staging.mouse28.com';

    public function handle(PublicContentArchive $archive): int
    {
        if ($this->importIsBlocked()) {
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

    private function importIsBlocked(): bool
    {
        if (! app()->isProduction()) {
            return false;
        }

        return ! $this->option('staging')
            || Uri::of((string) config('app.url'))->host() !== self::STAGING_HOST;
    }
}
