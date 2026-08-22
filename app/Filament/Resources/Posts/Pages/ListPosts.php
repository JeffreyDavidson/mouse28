<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'attention' => Tab::make('Needs attention')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()->needsAttention()->select('id'))),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()->drafts()->select('id'))),
            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()->scheduled()->select('id'))),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()->published()->select('id'))),
            'review-due' => Tab::make('Review due')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('posts.id', Post::query()->published()->reviewDue()->select('id'))),
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
