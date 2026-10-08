<?php

namespace App\ViewModels;

use App\Enums\SearchContentType;
use App\Models\Episode;
use App\Models\Guide;
use App\Models\Post;
use App\Queries\SearchQuery;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use UnexpectedValueException;

class SearchViewModel
{
    public function __construct(private readonly SearchQuery $searchQuery) {}

    /**
     * The search page for the query. Each group's page links keep the search terms and
     * the other groups' pages, and return to the group's heading.
     *
     * @return array{
     *     query: string,
     *     results: array<string, LengthAwarePaginator<int, array{url: string, eyebrow: string, title: string, description: string|null}>>,
     *     resultCount: int,
     *     typeLabels: array<string, string>
     * }
     */
    public function data(string $query): array
    {
        $results = $this->searchQuery->get($query);

        foreach ($results as $type => $group) {
            $group->withQueryString()
                ->fragment("search-{$type}");
        }

        $results = array_map(
            fn (LengthAwarePaginator $group): LengthAwarePaginator => $group->through(fn (Model $model): array => $this->result($model)),
            $results,
        );

        return [
            'query' => $query,
            'results' => $results,
            'resultCount' => array_sum(array_map(fn (LengthAwarePaginator $group): int => $group->total(), $results)),
            'typeLabels' => SearchContentType::labels(),
        ];
    }

    /**
     * One search result as its card shows it.
     *
     * @return array{url: string, eyebrow: string, title: string, description: string|null}
     */
    private function result(Model $model): array
    {
        [$url, $eyebrow, $title, $description] = match (true) {
            $model instanceof Post => [route('blog.show', $model), $model->category_label, $model->title, $model->excerpt],
            $model instanceof Guide => [route('guides.show', $model), $model->category_label, $model->title, $model->excerpt],
            $model instanceof Episode => [route('episodes.show', $model), "Episode {$model->episode_number}", $model->title, $model->description],
            default => throw new UnexpectedValueException('Unsupported search result model.'),
        };

        return [
            'url' => $url,
            'eyebrow' => $eyebrow,
            'title' => $title,
            'description' => $description,
        ];
    }
}
