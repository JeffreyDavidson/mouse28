<?php

namespace App\Livewire;

use App\Enums\PostCategory;
use App\Models\Post;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BlogIndex extends Component
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
        $this->category = PostCategory::tryFrom($category)?->value ?: '';
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
        $cardColumns = ['id', 'slug', 'title', 'excerpt', 'body', 'category', 'author', 'cover_image', 'published_at'];

        $posts = Post::published()
            ->select($cardColumns)
            ->when($this->category, fn (Builder $query) => $query->where('category', $this->category))
            ->when($this->search, fn (Builder $query) => $query->where(function (Builder $query): void {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhere('excerpt', 'like', "%{$this->search}%")
                    ->orWhere('body', 'like', "%{$this->search}%");
            }))
            ->orderBy('published_at', $this->sort === 'oldest' ? 'asc' : 'desc')
            ->paginate(12);

        $featuredPost = $this->hasDefaultFilters() && $posts->currentPage() === 1
            ? $posts->first()
            : Post::published()->select($cardColumns)->latest('published_at')->first();

        $archivePosts = $featuredPost && $this->hasDefaultFilters()
            ? $posts->getCollection()->reject(fn (Post $post): bool => $post->is($featuredPost))
            : $posts->getCollection();

        return view('livewire.blog-index', [
            'posts' => $posts,
            'featuredPost' => $featuredPost,
            'archivePosts' => $archivePosts,
            'hasAnyPosts' => $featuredPost !== null,
            'usedCategories' => Post::published()->distinct()->pluck('category')->filter()
                ->values()
                ->map(fn (PostCategory $category): string => $category->value)
                ->all(),
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
        return PostCategory::tryFrom($this->category)?->value ?: '';
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
        $categoryLabel = PostCategory::tryFrom($this->category)?->getLabel();

        $this->dispatch(
            'blog-metadata-updated',
            pageTitle: $categoryLabel ? "{$categoryLabel} | Mouse28" : 'Disney Parks Blog | Mouse28',
            pageDescription: $categoryLabel
                ? "Mouse28 {$categoryLabel} articles, family experiences, and practical Disney park takeaways."
                : 'Disney park accessibility tips, trip reports, family experiences, news, and practical planning from Jeffrey and Cassie Davidson.',
            canonicalUrl: route('blog.index', array_filter([
                'category' => $this->category ?: null,
                'page' => $page > 1 ? $page : null,
            ])),
            robots: $this->search !== '' || $this->sort !== 'newest' ? 'noindex,follow' : 'index,follow',
        );
    }
}
