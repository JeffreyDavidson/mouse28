<?php

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Filament\Actions\PublishContentAction;
use App\Filament\Actions\UnpublishContentAction;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Models\NewsletterIssue;
use App\Support\Content\PreviewUrlGenerator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;

/** @property NewsletterIssue $record */
class EditNewsletterIssue extends EditRecord
{
    #[\Override]
    protected static string $resource = NewsletterIssueResource::class;

    public function getHeader(): ?View
    {
        return view('filament.resources.newsletter-issues.form-header', [
            'title' => 'Edit Issue',
            'subtitle' => $this->record->title,
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            PublishContentAction::make(),
            UnpublishContentAction::make(),
            Action::make('preview')
                ->icon(Heroicon::OutlinedEye)
                ->authorize('view')
                ->url(fn (PreviewUrlGenerator $previewUrls): string => $previewUrls->for($this->record))
                ->openUrlInNewTab(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
