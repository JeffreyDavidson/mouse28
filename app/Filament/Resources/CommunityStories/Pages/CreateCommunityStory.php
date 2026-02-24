<?php

namespace App\Filament\Resources\CommunityStories\Pages;

use App\Filament\Resources\CommunityStories\CommunityStoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCommunityStory extends CreateRecord
{
    protected static string $resource = CommunityStoryResource::class;
}
