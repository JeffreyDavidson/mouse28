<?php

namespace App\Livewire;

use App\Data\BlogFilters;
use App\Enums\BlogSort;
use App\Models\Category;
use App\Models\Post;
use App\Support\TextSearch;
use App\ViewModels\PostIndexViewModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BlogArchive extends Component
{
    use WithPagination;

    #[Url(as: 'q', history: true, except: '')]
    public string $search = '';

    #[Url(history: true, except: '')]
    public string $category = '';

    #[Url(history: true, except: BlogSort::Newest->value)]
    public string $sort = BlogSort::Newest->value;

    /**
     * This request's normalised filters, so the metadata and the render share
     * one category lookup. Livewire does not keep it between requests.
     */
    private ?BlogFilters $filters = null;

    public function mount(): void
    {
        $this->applyFilters($this->filtersFromProperties());
    }

    public function updatedSearch(): void
    {
        $this->filtersChanged($this->filtersFromProperties());
    }

    public function updatedCategory(): void
    {
        $this->filtersChanged($this->filtersFromProperties());
    }

    public function updatedSort(): void
    {
        $this->filtersChanged($this->filtersFromProperties());
    }

    public function applySearch(): void
    {
        $this->updatedSearch();
    }

    public function clearSearch(): void
    {
        $this->filtersChanged(BlogFilters::fromInput($this->category, '', $this->sort));
    }

    public function selectCategory(string $category): void
    {
        $this->filtersChanged(BlogFilters::fromInput($category, '', BlogSort::Newest->value));
    }

    public function clearFilters(): void
    {
        $this->filtersChanged(new BlogFilters);
    }

    public function updatedPaginators(int $page, string $pageName): void
    {
        if ($pageName === 'page') {
            $this->dispatchMetadata($page);
        }
    }

    public function render(): View
    {
        $filters = $this->filters();
        $cardColumns = ['id', 'slug', 'title', 'excerpt', 'content', 'category_id', 'featured_image_path', 'published_at'];
        $cardRelations = ['category:id,name,slug', 'authors:id,name'];
        $usedCategories = Category::query()
            ->whereHas('publishedPosts')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);

        $posts = Post::published()
            ->select($cardColumns)
            ->with($cardRelations)
            ->when($filters->category, fn (Builder $query, Category $category) => $query->whereBelongsTo($category))
            ->when($filters->search, fn (Builder $query, string $search) => TextSearch::constrain($query, ['title', 'excerpt', 'content'], $search))
            ->orderBy('published_at', $filters->sort->direction())
            ->orderBy('id', $filters->sort->direction())
            ->paginate(Config::integer('mouse28.blog_posts_per_page'));

        $featuredPost = $filters->isDefault() && $posts->currentPage() === 1
            ? $posts->first()
            : Post::published()
                ->select($cardColumns)
                ->with($cardRelations)
                ->newestFirst()
                ->first();

        $archivePosts = $featuredPost && $filters->isDefault()
            ? $posts->getCollection()
                ->reject(fn (Post $post): bool => $post->is($featuredPost))
            : $posts->getCollection();

        return view('livewire.blog-archive', [
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'archivePosts' => $archivePosts,
            'hasAnyPosts' => $featuredPost !== null,
            'usedCategories' => $usedCategories,
            'selectedCategoryName' => $filters->category?->name,
        ]);
    }

    private function filtersChanged(BlogFilters $filters): void
    {
        $this->applyFilters($filters);
        $this->resetPage();
        $this->dispatchMetadata(1);
    }

    /** Writes the normalised filters back to the URL-bound properties. */
    private function applyFilters(BlogFilters $filters): void
    {
        $this->filters = $filters;
        $this->category = $filters->categorySlug();
        $this->search = $filters->search;
        $this->sort = $filters->sort->value;
    }

    /** The filters for a request with no filter update, such as a page change. */
    private function filters(): BlogFilters
    {
        return $this->filters ??= $this->filtersFromProperties();
    }

    private function filtersFromProperties(): BlogFilters
    {
        return BlogFilters::fromInput($this->category, $this->search, $this->sort);
    }

    private function dispatchMetadata(int $page): void
    {
        $this->dispatch(
            'blog-metadata-updated',
            ...app(PostIndexViewModel::class)->metadata($this->filters(), $page),
        );
    }
}
