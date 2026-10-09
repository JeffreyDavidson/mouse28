<?php

declare(strict_types=1);

namespace App\Filament\Resources\Episodes\Pages;

use App\Enums\ContentType;
use App\Filament\Concerns\ListsContentByStatus;
use App\Filament\Resources\Episodes\EpisodeResource;
use Filament\Resources\Pages\ListRecords;

class ListEpisodes extends ListRecords
{
    use ListsContentByStatus;

    #[\Override]
    protected static string $resource = EpisodeResource::class;

    #[\Override]
    protected ?string $subheading = 'Manage your podcast episodes';

    protected function contentType(): ContentType
    {
        return ContentType::Episode;
    }
}
