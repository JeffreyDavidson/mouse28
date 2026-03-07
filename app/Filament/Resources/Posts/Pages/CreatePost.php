<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\View\View;

class CreatePost extends CreateRecord
{
    protected static string $resource = PostResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.posts.form-header', [
            'title' => 'Create Post',
            'subtitle' => 'Write a new blog post',
        ]);
    }
}
