<?php

declare(strict_types=1);

namespace App\Data;

use App\Enums\BlogSort;
use App\Models\Category;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

/**
 * The blog archive's normalised filters: an existing category (or every story),
 * a trimmed and capped search, and a known sort. The page request and the
 * Livewire archive both build them here so they normalise the same way.
 */
final readonly class BlogFilters
{
    public function __construct(
        public ?Category $category = null,
        public string $search = '',
        public BlogSort $sort = BlogSort::Newest,
    ) {}

    /**
     * Normalises raw query string or component input, looking the category up
     * at most once (not at all when no category is given).
     */
    public static function fromInput(string $category, string $search, string $sort): self
    {
        return new self(
            category: $category === ''
                ? null
                : Category::query()
                    ->where('slug', $category)
                    ->first(['id', 'name', 'slug']),
            search: Str::of($search)
                ->trim()
                ->limit(Config::integer('mouse28.blog_search_max_length'), '')
                ->toString(),
            sort: BlogSort::fromInput($sort),
        );
    }

    /** The selected category's slug, or an empty string for every story. */
    public function categorySlug(): string
    {
        return $this->category->slug ?? '';
    }

    public function isDefault(): bool
    {
        return ! $this->category instanceof Category
            && $this->search === ''
            && $this->sort === BlogSort::Newest;
    }
}
