<div data-blog-browser aria-busy="false" wire:loading.attr="aria-busy">
    <section class="editorial-index-hero bg-navy text-cream relative overflow-hidden">
        <div class="relative mx-auto max-w-[86rem] px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
            <div class="max-w-2xl">
                <h1 class="font-heading text-5xl [font-weight:680] tracking-[-0.025em] text-balance sm:text-6xl">
                    Blog
                </h1>
                <p class="text-cream/75 mt-4 max-w-xl text-base/7 text-pretty sm:text-lg/8">
                    Disney tips, practical planning, and honest stories from our family to yours.
                </p>
            </div>

            @if ($featuredPost)
                <article class="editorial-feature mt-10 grid overflow-hidden lg:grid-cols-[7fr_5fr]">
                    <a
                        href="{{ route('blog.show', $featuredPost) }}"
                        aria-label="Read {{ $featuredPost->title }}"
                        class="group block min-h-72 overflow-hidden lg:min-h-[31rem]"
                    >
                        <x-post-artwork
                            :post="$featuredPost"
                            class="h-full min-h-72 w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.025] lg:min-h-[31rem]"
                        />
                    </a>
                    <div class="bg-cream text-navy flex flex-col justify-center p-7 sm:p-10 lg:p-12">
                        <h2 class="font-heading text-3xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-4xl">
                            <a
                                class="hover:text-purple transition-colors"
                                href="{{ route('blog.show', $featuredPost) }}"
                            >{{ $featuredPost->title }}</a>
                        </h2>
                        @if ($featuredPost->excerpt)
                            <p class="text-navy/70 mt-5 max-w-[48ch] text-base/7 text-pretty">
                                {{ $featuredPost->excerpt }}
                            </p>
                        @endif
                        <div class="border-gold/35 mt-7 flex flex-wrap items-center gap-x-4 gap-y-2 border-t pt-5 text-sm">
                            <span class="font-semibold">{{ $featuredPost->author_name }}</span>
                            <span class="text-navy/65">{{ $featuredPost->published_at->format('F j, Y') }}</span>
                            <span class="text-navy/65">{{ $featuredPost->reading_time }} min read</span>
                        </div>
                        <a
                            href="{{ route('blog.show', $featuredPost) }}"
                            class="text-purple decoration-gold/70 hover:text-navy mt-6 inline-flex min-h-12 w-fit items-center font-semibold underline underline-offset-8 transition-colors"
                        >Read the story</a>
                    </div>
                </article>
            @endif
        </div>
    </section>

    <section class="dispatch-page-field bg-cream py-10 sm:py-14">
        <div class="mx-auto max-w-[86rem] px-4 sm:px-6">
            @if ($hasAnyPosts || $search || $category)
                <div data-blog-filters class="border-navy/15 border-y py-6">
                    <div class="grid min-w-0 gap-6 lg:grid-cols-[minmax(15rem,1fr)_2fr_auto] lg:items-end">
                        <form
                            action="{{ route('blog.index') }}"
                            method="GET"
                            wire:submit.prevent="applySearch"
                            data-preserve-blog-filter-position
                        >
                            @if ($category)
                                <input type="hidden" name="category" value="{{ $category }}" />
                            @endif
                            @if ($sort !== 'newest')
                                <input type="hidden" name="sort" value="{{ $sort }}" />
                            @endif
                            <label for="blog-search" class="text-navy mb-2 block text-sm font-semibold"
                                >Search stories</label>
                            <div class="relative">
                                <svg aria-hidden="true" class="text-navy/40 pointer-events-none absolute top-1/2 left-4 size-4 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input
                                    id="blog-search"
                                    type="search"
                                    name="q"
                                    wire:model.live.debounce.300ms="search"
                                    data-preserve-blog-filter-position
                                    placeholder="Search posts..."
                                    class="border-navy/15 bg-cream text-navy placeholder:text-navy/60 focus:border-purple focus:ring-purple/20 min-h-12 w-full appearance-none rounded-xl border py-3 pr-12 pl-11 text-base transition-opacity outline-none focus:ring-2 data-loading:opacity-60 motion-reduce:transition-none"
                                />
                                @if ($search)
                                    <a
                                        href="{{ route('blog.index', array_filter(['category' => $category, 'sort' => $sort !== 'newest' ? $sort : null])) }}"
                                        wire:click.prevent="clearSearch"
                                        data-preserve-blog-filter-position
                                        class="text-navy/65 hover:text-purple absolute top-1/2 right-0 flex size-12 -translate-y-1/2 items-center justify-center data-loading:pointer-events-none data-loading:opacity-60"
                                        aria-label="Clear search"
                                    >
                                        <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </form>

                        <nav aria-label="Blog categories" class="min-w-0">
                            <p class="text-navy mb-2 text-sm font-semibold">Browse by topic</p>
                            <div class="flex gap-2 overflow-x-auto pb-1">
                                <a
                                    href="{{ route('blog.index') }}"
                                    wire:click.prevent="clearFilters"
                                    data-preserve-blog-filter-position
                                    data-blog-filter-link
                                    @if (! $category) aria-current="page" @endif
                                    class="data-loading:pointer-events-none data-loading:opacity-60 inline-flex min-h-12 shrink-0 items-center rounded-full px-4 py-2 text-sm font-semibold transition-colors {{ ! $category ? 'bg-navy text-cream' : 'border-navy/15 text-navy hover:border-purple border' }}"
                                >All stories</a>
                                @foreach (\App\Enums\PostCategory::cases() as $categoryOption)
                                    @continue(! in_array($categoryOption->value, $usedCategories))
                                    <a
                                        href="{{ route('blog.index', ['category' => $categoryOption->value]) }}"
                                        wire:click.prevent="selectCategory('{{ $categoryOption->value }}')"
                                        data-preserve-blog-filter-position
                                        data-blog-filter-link
                                        @if ($category === $categoryOption->value) aria-current="page" @endif
                                        class="data-loading:pointer-events-none data-loading:opacity-60 inline-flex min-h-12 shrink-0 items-center rounded-full px-4 py-2 text-sm font-semibold transition-colors {{ $category === $categoryOption->value ? 'bg-navy text-cream' : 'border-navy/15 text-navy hover:border-purple border' }}"
                                    >{{ $categoryOption->getLabel() }}</a>
                                @endforeach
                            </div>
                        </nav>

                        <form action="{{ route('blog.index') }}" method="GET">
                            @if ($category)
                                <input type="hidden" name="category" value="{{ $category }}" />
                            @endif
                            @if ($search)
                                <input type="hidden" name="q" value="{{ $search }}" />
                            @endif
                            <label for="blog-sort" class="text-navy mb-2 block text-sm font-semibold">Order</label>
                            <div class="relative">
                                <select
                                    id="blog-sort"
                                    name="sort"
                                    wire:model.live="sort"
                                    data-preserve-blog-filter-position
                                    class="border-navy/15 bg-cream text-navy focus:border-purple focus:ring-purple/20 min-h-12 min-w-44 appearance-none rounded-xl border py-3 pr-12 pl-4 text-base transition-opacity outline-none focus:ring-2 data-loading:opacity-60 motion-reduce:transition-none"
                                >
                                    <option value="newest">Newest first</option>
                                    <option value="oldest">Oldest first</option>
                                </select>
                                <svg data-blog-sort-chevron aria-hidden="true" class="text-navy/55 pointer-events-none absolute top-1/2 right-4 size-5 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" />
                                </svg>
                            </div>
                            <noscript>
                                <button
                                    type="submit"
                                    class="bg-navy text-cream mt-3 inline-flex min-h-12 items-center rounded-xl px-4 py-3 text-sm font-semibold"
                                >
                                    Apply order
                                </button>
                            </noscript>
                        </form>
                    </div>
                </div>
            @endif

            <div
                data-blog-results
                wire:loading.class.delay="opacity-60"
                class="pt-10 transition-opacity duration-150 motion-reduce:transition-none sm:pt-14"
            >
                @if ($posts->count())
                    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                        <h2 class="font-heading text-navy text-3xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-4xl">
                            @if ($search)
                                Results for "{{ $search }}"
                            @elseif ($category)
                                {{ \App\Enums\PostCategory::tryFrom($category)?->getLabel() ?? 'Stories' }}
                            @else
                                More stories
                            @endif
                        </h2>
                        <p
                            data-blog-status
                            role="status"
                            aria-live="polite"
                            aria-atomic="true"
                            class="text-navy/65 min-h-5 text-sm"
                        >
                            <span wire:loading.delay>Updating stories…</span>
                            <span wire:loading.remove>{{ $posts->total() }} {{ Str::plural('story', $posts->total()) }}</span>
                        </p>
                    </div>

                    @if ($archivePosts->count())
                        <div class="editorial-story-grid" data-equal-width-stories>
                            @foreach ($archivePosts as $post)
                                <article
                                    wire:key="blog-post-{{ $post->getKey() }}"
                                    data-blog-post="{{ $post->getKey() }}"
                                    class="editorial-story group min-w-0"
                                >
                                    <a
                                        href="{{ route('blog.show', $post) }}"
                                        aria-label="Read {{ $post->title }}"
                                        class="block overflow-hidden rounded-xl"
                                    >
                                        <x-post-artwork
                                            :post="$post"
                                            :compact="true"
                                            class="aspect-[4/3] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.03]"
                                        />
                                    </a>
                                    <div class="pt-5">
                                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                                            <span class="text-purple font-semibold">{{ $post->category_label }}</span>
                                            <span class="text-navy/60">{{ $post->published_at->format('M j, Y') }}</span>
                                            <span class="text-navy/60">{{ $post->reading_time }} min read</span>
                                        </div>
                                        <h3 class="font-heading text-navy group-hover:text-purple mt-2 text-2xl [font-weight:600] tracking-[-0.015em] text-balance transition-colors sm:text-3xl">
                                            <a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
                                        </h3>
                                        @if ($post->excerpt)
                                            <p class="text-navy/70 mt-3 max-w-[58ch] text-base/7 text-pretty">
                                                {{ Str::limit($post->excerpt, 180) }}
                                            </p>
                                        @endif
                                        <p class="text-navy/60 mt-4 text-sm">By {{ $post->author_name }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    @if ($posts->hasPages())
                        <div class="blog-pagination mt-14 flex justify-center">
                            {{ $posts->links(data: ['scrollTo' => false]) }}
                        </div>
                    @endif
                @else
                    <div class="bg-navy text-cream mx-auto max-w-3xl rounded-xl px-7 py-12 text-center sm:px-12">
                        <h2 class="font-heading text-3xl [font-weight:640] tracking-[-0.02em] text-balance">
                            @if ($search)
                                No posts match "{{ $search }}"
                            @elseif ($category)
                                Nothing in {{ \App\Enums\PostCategory::tryFrom($category)?->getLabel() ?? 'this category' }} yet
                            @else
                                We're putting pen to paper
                            @endif
                        </h2>
                        <p class="text-cream/75 mx-auto mt-4 max-w-xl text-base/7">
                            @if ($search)
                                Try a different search term or browse all posts.
                            @elseif ($category)
                                Browse the rest of our stories in the meantime.
                            @else
                                Park tips, accessibility insights, and real family stories are on the way.
                            @endif
                        </p>
                        <div class="mt-6 flex flex-wrap justify-center gap-5">
                            @if ($search || $category)
                                <a
                                    href="{{ route('blog.index') }}"
                                    wire:click.prevent="clearFilters"
                                    data-preserve-blog-filter-position
                                    class="text-gold inline-flex min-h-12 items-center font-semibold underline underline-offset-8 data-loading:pointer-events-none data-loading:opacity-60"
                                >View all posts</a>
                            @else
                                @if (config('mouse28.guides_enabled'))
                                    <a
                                        href="{{ route('guides.index') }}"
                                        class="text-gold inline-flex min-h-12 items-center font-semibold underline underline-offset-8"
                                    >Park guides</a>
                                @endif
                                <a
                                    href="{{ route('episodes.index') }}"
                                    class="text-gold inline-flex min-h-12 items-center font-semibold underline underline-offset-8"
                                >Podcast episodes</a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            @if ($hasAnyPosts || $search || $category)
                <div class="mx-auto mt-16 max-w-3xl">
                    <x-newsletter-card subtitle="Disney tips, park updates, and new posts" />
                </div>
            @endif
        </div>
    </section>
</div>
