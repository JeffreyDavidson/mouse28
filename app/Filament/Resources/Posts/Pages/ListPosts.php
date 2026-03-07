<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        $total = Post::count();
        $published = Post::where('is_published', true)->count();
        $drafts = Post::where('is_published', false)->count();

        return view('filament.resources.posts.header', [
            'total' => $total,
            'published' => $published,
            'drafts' => $drafts,
            'createUrl' => PostResource::getUrl('create'),
        ]);
    }
}
