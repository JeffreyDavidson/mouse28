<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Post;
use App\Support\TextSearch;
use App\ViewModels\PostIndexViewModel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
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

    #[Url(history: true, except: 'newest')]
    public string $sort = 'newest';

    public function mount(): void
    {
        $this->normalizeFilters();
    }

    public function updatedSearch(): void
    {
        $this->search = $this->normalizedSearch();
        $this->resetPage();
        $this->dispatchMetadata(1);
    }

    public function updatedCategory(): void
    {
        $this->category = $this->normalizedCategory();
        $this->resetPage();
        $this->dispatchMetadata(1);
    }

    public function updatedSort(): void
    {
        $this->sort = $this->normalizedSort();
        $this->resetPage();
        $this->dispatchMetadata(1);
    }

    public function applySearch(): void
    {
        $this->updatedSearch();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
        $this->dispatchMetadata(1);
    }

    public function selectCategory(string $category): void
    {
        $this->category = $this->existingCategorySlug($category);
        $this->search = '';
        $this->sort = 'newest';
        $this->resetPage();
        $this->dispatchMetadata(1);
    }

    public function clearFilters(): void
    {
        $this->category = '';
        $this->search = '';
        $this->sort = 'newest';
        $this->resetPage();
        $this->dispatchMetadata(1);
    }

    public function updatedPaginators(int $page, string $pageName): void
    {
        if ($pageName === 'page') {
            $this->dispatchMetadata($page);
        }
    }

    public function render(): View
    {
        $cardColumns = ['id', 'slug', 'title', 'excerpt', 'content', 'category_id', 'cover_image', 'published_at'];
        $cardRelations = ['category:id,name,slug', 'authors:id,name'];
        $usedCategories = Category::query()
            ->whereHas('publishedPosts')
            ->orderBy('id')
            ->get(['id', 'name', 'slug']);

        $posts = Post::published()
            ->select($cardColumns)
            ->with($cardRelations)
            ->when($this->category, fn (Builder $query) => $query->whereRelation('category', 'slug', $this->category))
            ->when($this->search, fn (Builder $query) => TextSearch::constrain($query, ['title', 'excerpt', 'content'], $this->search))
            ->orderBy('published_at', $this->sort === 'oldest' ? 'asc' : 'desc')
            ->orderBy('id', $this->sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(Config::integer('mouse28.blog_posts_per_page'));

        $featuredPost = $this->hasDefaultFilters() && $posts->currentPage() === 1
            ? $posts->first()
            : Post::published()->select($cardColumns)->with($cardRelations)->latest('published_at')->latest('id')->first();

        $archivePosts = $featuredPost && $this->hasDefaultFilters()
            ? $posts->getCollection()->reject(fn (Post $post): bool => $post->is($featuredPost))
            : $posts->getCollection();

        return view('livewire.blog-archive', [
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'archivePosts' => $archivePosts,
            'hasAnyPosts' => $featuredPost !== null,
            'usedCategories' => $usedCategories,
            'selectedCategoryName' => $this->category === ''
                ? null
                : Category::query()->where('slug', $this->category)->value('name'),
        ]);
    }

    private function normalizeFilters(): void
    {
        $this->search = $this->normalizedSearch();
        $this->category = $this->normalizedCategory();
        $this->sort = $this->normalizedSort();
    }

    private function normalizedSearch(): string
    {
        return Str::of($this->search)->trim()->limit(100, '')->toString();
    }

    private function normalizedCategory(): string
    {
        return $this->existingCategorySlug($this->category);
    }

    /** The slug when a category with it exists, otherwise an empty string (all stories). */
    private function existingCategorySlug(string $slug): string
    {
        if ($slug === '') {
            return '';
        }

        $existingSlug = Category::query()->where('slug', $slug)->value('slug');

        return is_string($existingSlug) ? $existingSlug : '';
    }

    private function normalizedSort(): string
    {
        return in_array($this->sort, ['newest', 'oldest'], true) ? $this->sort : 'newest';
    }

    private function hasDefaultFilters(): bool
    {
        return $this->search === '' && $this->category === '' && $this->sort === 'newest';
    }

    private function dispatchMetadata(int $page): void
    {
        $this->dispatch(
            'blog-metadata-updated',
            ...app(PostIndexViewModel::class)->metadata($this->category, $this->search, $this->sort, $page),
        );
    }
}
