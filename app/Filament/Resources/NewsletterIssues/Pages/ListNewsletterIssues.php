<?php

namespace App\Filament\Resources\NewsletterIssues\Pages;

use App\Enums\PublishStatus;
use App\Filament\Resources\NewsletterIssues\NewsletterIssueResource;
use App\Models\NewsletterIssue;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

class ListNewsletterIssues extends ListRecords
{
    #[\Override]
    protected static string $resource = NewsletterIssueResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'drafts' => Tab::make('Drafts')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('newsletter_issues.status', [PublishStatus::Draft, PublishStatus::InReview])),
            'scheduled' => Tab::make('Scheduled')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('newsletter_issues.id', NewsletterIssue::query()
                    ->scheduled()
                    ->select('id'))),
            'published' => Tab::make('Published')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn('newsletter_issues.id', NewsletterIssue::query()
                    ->published()
                    ->select('id'))),
        ];
    }

    public function getHeader(): ?View
    {
        return view('filament.resources.newsletter-issues.header', [
            'published' => NewsletterIssue::published()->count(),
            'drafts' => NewsletterIssue::query()
                ->whereIn('status', [PublishStatus::Draft, PublishStatus::InReview])
                ->count(),
            'createUrl' => NewsletterIssueResource::getUrl('create'),
        ]);
    }
}
