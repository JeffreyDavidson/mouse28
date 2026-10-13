<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetReviews\Pages;

use App\Filament\Resources\AssetReviews\AssetReviewResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAssetReviews extends ListRecords
{
    #[\Override]
    protected static string $resource = AssetReviewResource::class;

    #[\Override]
    protected ?string $subheading = 'Review saved artwork and record approval before publication.';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()
            ->label('New review set')];
    }
}
