<?php

declare(strict_types=1);

namespace App\Filament\Tables\Filters;

use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shows content that has no featured image path saved.
 */
class MissingArtworkFilter extends Filter
{
    public static function getDefaultName(): ?string
    {
        return 'missing_artwork';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->query(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
            $query->whereNull('featured_image_path')
                ->orWhere('featured_image_path', '');
        }));
    }
}
