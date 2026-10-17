<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use App\Enums\ContentType;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use Closure;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

/**
 * The status tabs shared by the content lists; the Drafts and Published tabs show their counts.
 * Each tab narrows the table to the ids its model scopes find, so a scope's joins
 * and columns never mix with the table's own query. The page sets its header
 * description through Filament's `$subheading`.
 *
 * @phpstan-require-extends ListRecords
 */
trait ListsContentByStatus
{
    abstract protected function contentType(): ContentType;

    /** @return array<string, Tab> */
    public function getTabs(): array
    {
        return array_filter([
            'all' => Tab::make('All'),
            'attention' => $this->scopedTab('Needs attention', fn (): Builder => $this->contentQuery()
                ->needsAttention()),
            'drafts' => $this->scopedTab('Drafts', fn (): Builder => $this->contentQuery()
                ->drafts())
                ->badge(fn (): int => $this->contentQuery()
                    ->drafts()
                    ->count()),
            'scheduled' => $this->scopedTab('Scheduled', fn (): Builder => $this->contentQuery()
                ->scheduled()),
            'published' => $this->scopedTab('Published', fn (): Builder => $this->contentQuery()
                ->published())
                ->badge(fn (): int => $this->contentQuery()
                    ->published()
                    ->count()),
            'review-due' => $this->reviewDueTab(),
        ]);
    }

    /**
     * Posts and guides cite official sources, so they list published content due for review.
     */
    private function reviewDueTab(): ?Tab
    {
        return match ($this->contentType()) {
            ContentType::Post => $this->scopedTab('Review due', fn (): Builder => Post::published()
                ->reviewDue()),
            ContentType::Guide => $this->scopedTab('Review due', fn (): Builder => Guide::published()
                ->reviewDue()),
            ContentType::Episode => null,
        };
    }

    /**
     * @param  Closure(): (Builder<Post>|Builder<Guide>|Builder<Episode>)  $scope
     */
    private function scopedTab(string $label, Closure $scope): Tab
    {
        return Tab::make($label)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->whereIn($query->qualifyColumn('id'), $scope()
                ->select('id')));
    }

    /** @return Builder<Post>|Builder<Guide>|Builder<Episode> */
    private function contentQuery(): Builder
    {
        return match ($this->contentType()) {
            ContentType::Post => Post::query(),
            ContentType::Guide => Guide::query(),
            ContentType::Episode => Episode::query(),
        };
    }
}
