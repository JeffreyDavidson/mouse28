<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Enums\ContentType;
use App\Filament\Concerns\ListsContentByStatus;
use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use ListsContentByStatus;

    #[\Override]
    protected static string $resource = PostResource::class;

    #[\Override]
    protected ?string $subheading = 'Create and manage your blog content';

    protected function contentType(): ContentType
    {
        return ContentType::Post;
    }
}
