<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;

/**
 * Cover image upload for posts, guides and episodes. Accepts JPEG, PNG and WebP up to 5 MB,
 * crops to the 1.91:1 share ratio and resizes to 1600 by 840 before the original is stored on
 * the public disk; the model's observer then generates the responsive variants.
 *
 * The Laravel Architect's component of this name stores through its own ImageUploadOptimizer
 * and keeps the whole picture (contain, 1600 square). Mouse28's stored-media workflow crops the
 * cover to a fixed ratio instead, so only the name is shared.
 */
class OptimizedImageUpload extends FileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Cover image')
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(5120)
            ->imageAspectRatio('1200:630')
            ->automaticallyCropImagesToAspectRatio()
            ->automaticallyResizeImagesMode('cover')
            ->automaticallyResizeImagesToWidth('1600')
            ->automaticallyResizeImagesToHeight('840')
            ->disk('public')
            ->helperText('Landscape image (1.91:1), up to 5 MB. Uploads are cropped and resized automatically.');
    }
}
