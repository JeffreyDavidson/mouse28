<?php

namespace App\Http\Controllers;

use App\Models\AssetReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetReviewFileController
{
    /** Serve only a saved raster belonging to this set, behind the panel's authentication. */
    public function __invoke(Request $request, AssetReview $assetReview, int $file): StreamedResponse
    {
        Gate::authorize('view', $assetReview);
        $path = $assetReview->filePath($file);
        abort_if($path === null, 404);
        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);
        $type = $disk->mimeType($path);
        abort_unless(in_array($type, ['image/png', 'image/jpeg', 'image/webp'], true), 404);

        return $disk->response($path, basename($path), [
            'Content-Type' => $type,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'X-Robots-Tag' => 'noindex, nofollow',
        ], $request->boolean('download') ? 'attachment' : 'inline');
    }
}
