<?php

namespace App\Filament\Resources\CommunityStories\Pages;

use App\Filament\Resources\CommunityStories\CommunityStoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommunityStory extends EditRecord
{
    protected static string $resource = CommunityStoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
