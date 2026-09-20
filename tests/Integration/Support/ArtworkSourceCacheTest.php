<?php

use App\Support\ArtworkSourceCache;

test('cache reuses a source hash per instance and recalculates it for a new instance', function (): void {
    $file = tempnam(sys_get_temp_dir(), 'artwork-cache-');
    file_put_contents($file, 'original');
    $cache = new ArtworkSourceCache;

    try {
        $original = $cache->hash($file);
        file_put_contents($file, 'replacement');

        expect($cache->hash($file))->toBe($original)
            ->and((new ArtworkSourceCache)->hash($file))->not->toBe($original);
    } finally {
        unlink($file);
    }
});
