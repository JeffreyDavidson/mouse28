<?php

declare(strict_types=1);

namespace App\Filament\Forms\Components;

use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use RalphJSmit\Filament\SEO\SEO;

/** Collapsed "SEO" section holding the SEO package's fields for posts, guides and episodes. */
class SeoSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->heading('SEO')
            ->icon(Heroicon::OutlinedMagnifyingGlass)
            ->description('Search engine optimization')
            ->collapsed()
            ->schema([
                SEO::make(),
            ]);
    }
}
