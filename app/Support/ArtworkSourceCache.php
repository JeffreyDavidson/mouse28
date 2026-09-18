<?php

namespace App\Support;

class ArtworkSourceCache
{
    /** @var array<string, string|false> */
    private array $hashes = [];

    public function hash(string $path): string|false
    {
        return $this->hashes[$path] ??= hash_file('sha256', $path);
    }
}
