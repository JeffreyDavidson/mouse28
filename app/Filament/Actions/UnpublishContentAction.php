<?php

namespace App\Filament\Actions;

use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/**
 * Takes published content off the public site while keeping its locked permalink.
 */
class UnpublishContentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'unpublish';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(Heroicon::OutlinedArrowUturnLeft)
            ->authorize('update')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Post|Guide|Episode $record): bool => $record->is_published)
            ->action(function (Post|Guide|Episode $record): void {
                $record->update(['is_published' => false]);

                Notification::make()->success()->title(class_basename($record).' unpublished')->send();
            });
    }
}
