<?php

namespace App\Filament\Resources\Guides\Pages;

use App\Filament\Resources\Guides\GuideResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGuide extends CreateRecord
{
    #[\Override]
    protected static string $resource = GuideResource::class;
}
