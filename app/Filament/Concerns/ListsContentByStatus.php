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
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;

/**
 * The status tabs and header counts shared by the post, guide and episode lists.
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
                ->drafts()),
            'scheduled' => $this->scopedTab('Scheduled', fn (): Builder => $this->contentQuery()
                ->scheduled()),
            'published' => $this->scopedTab('Published', fn (): Builder => $this->contentQuery()
                ->published()),
            'review-due' => $this->reviewDueTab(),
        ]);
    }

    public function getHeader(): ?View
    {
        $type = $this->contentType();

        return view('filament.resources.content.header', [
            'type' => $type,
            'subtitle' => $this->getSubheading(),
            'published' => $this->contentQuery()
                ->published()
                ->count(),
            'drafts' => $this->contentQuery()
                ->drafts()
                ->count(),
            'createUrl' => $type->resource()::getUrl('create'),
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
