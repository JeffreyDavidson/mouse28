<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\View\View;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->icon('heroicon-o-eye')
                ->authorize('view')
                ->url(fn (): string => route('preview.posts', $this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.posts.form-header', [
            'title' => 'Edit Post',
            'subtitle' => $this->record->title,
        ]);
    }
}
