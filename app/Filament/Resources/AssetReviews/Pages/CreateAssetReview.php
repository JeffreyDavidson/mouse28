<?php

declare(strict_types=1);

namespace App\Filament\Resources\AssetReviews\Pages;

use App\Filament\Resources\AssetReviews\AssetReviewResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAssetReview extends CreateRecord
{
    #[\Override]
    protected static string $resource = AssetReviewResource::class;

    protected function getRedirectUrl(): string
    {
        return AssetReviewResource::getUrl('edit', ['record' => $this->record]);
    }
}
