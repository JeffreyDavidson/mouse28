<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\SpatieTagsInput;

/** Tags for posts, guides and episodes, which share the `content` tag type. */
class ContentTagsInput extends SpatieTagsInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->type('content');
    }
}
