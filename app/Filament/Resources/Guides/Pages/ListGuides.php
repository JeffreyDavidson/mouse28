<?php

declare(strict_types=1);

namespace App\Filament\Resources\Guides\Pages;

use App\Enums\ContentType;
use App\Filament\Concerns\ListsContentByStatus;
use App\Filament\Resources\Guides\GuideResource;
use Filament\Resources\Pages\ListRecords;

class ListGuides extends ListRecords
{
    use ListsContentByStatus;

    #[\Override]
    protected static string $resource = GuideResource::class;

    #[\Override]
    protected ?string $subheading = 'Manage your accessibility guides';

    protected function contentType(): ContentType
    {
        return ContentType::Guide;
    }
}
