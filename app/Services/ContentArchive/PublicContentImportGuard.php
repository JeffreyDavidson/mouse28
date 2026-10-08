<?php

declare(strict_types=1);

namespace App\Services\ContentArchive;

use Illuminate\Support\Uri;

/**
 * Decides whether public content may be imported here: never into a copy that uses the
 * live site's address, and in production only on the staging host with `--staging`.
 * The Laravel Architect's guard with mouse28's hostnames.
 */
final class PublicContentImportGuard
{
    public function allows(bool $staging): bool
    {
        $url = config('app.url');

        if (! is_string($url)) {
            return false;
        }

        $host = strtolower(rtrim(Uri::of($url)->host() ?? '', '.'));

        if ($host === '' || in_array($host, ['mouse28.com', 'www.mouse28.com'], true)) {
            return false;
        }

        return ! app()->isProduction() || ($staging && $host === 'staging.mouse28.com');
    }
}
