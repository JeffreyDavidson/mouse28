@extends('layouts.app')

@section('title', $pageTitle)
@section('meta_description', $pageDescription)
@section('og_title', $pageTitle)
@section('og_description', $pageDescription)
@section('og_image', '/images/hero-family.jpg')
@section('canonical', $canonicalUrl)
@section('robots', $robots)

@section('content')
    @php
        $categoryStyles = [
            'disney-tips' => ['accent' => 'bg-gold', 'badge' => 'bg-gold/20 text-gold', 'subtle' => 'bg-gold/10', 'text' => 'text-gold'],
            'park-accessibility' => ['accent' => 'bg-purple-light', 'badge' => 'bg-purple-light/20 text-purple-light', 'subtle' => 'bg-purple-light/10', 'text' => 'text-purple-light'],
            'episode-recap' => ['accent' => 'bg-green-500', 'badge' => 'bg-green-500/20 text-green-500', 'subtle' => 'bg-green-500/10', 'text' => 'text-green-500'],
            'family-life' => ['accent' => 'bg-blue-500', 'badge' => 'bg-blue-500/20 text-blue-500', 'subtle' => 'bg-blue-500/10', 'text' => 'text-blue-500'],
            'autism-awareness' => ['accent' => 'bg-pink-500', 'badge' => 'bg-pink-500/20 text-pink-500', 'subtle' => 'bg-pink-500/10', 'text' => 'text-pink-500'],
            'disney-news' => ['accent' => 'bg-orange-500', 'badge' => 'bg-orange-500/20 text-orange-500', 'subtle' => 'bg-orange-500/10', 'text' => 'text-orange-500'],
            'food-reviews' => ['accent' => 'bg-amber-500', 'badge' => 'bg-amber-500/20 text-amber-500', 'subtle' => 'bg-amber-500/10', 'text' => 'text-amber-500'],
            'resort-reviews' => ['accent' => 'bg-teal-500', 'badge' => 'bg-teal-500/20 text-teal-500', 'subtle' => 'bg-teal-500/10', 'text' => 'text-teal-500'],
            'disney-plus' => ['accent' => 'bg-indigo-500', 'badge' => 'bg-indigo-500/20 text-indigo-500', 'subtle' => 'bg-indigo-500/10', 'text' => 'text-indigo-500'],
            'merchandise' => ['accent' => 'bg-rose-500', 'badge' => 'bg-rose-500/20 text-rose-500', 'subtle' => 'bg-rose-500/10', 'text' => 'text-rose-500'],
            'general' => ['accent' => 'bg-[#4a90a4]', 'badge' => 'bg-[#4a90a4]/20 text-[#4a90a4]', 'subtle' => 'bg-[#4a90a4]/10', 'text' => 'text-[#4a90a4]'],
        ];
        $defaultCategoryStyle = ['accent' => 'bg-purple', 'badge' => 'bg-purple/20 text-purple', 'subtle' => 'bg-purple/10', 'text' => 'text-purple'];
    @endphp

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-linear-to-br from-navy to-navy-light py-16 md:py-24">
        <div class="relative z-10 mx-auto max-w-6xl px-4 text-center sm:px-6">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 backdrop-blur-sm">
                <span class="size-2 animate-pulse rounded-full bg-gold"></span>
                <span class="text-sm font-semibold tracking-widest text-gold uppercase">Stories & Tips</span>
            </div>
            <h1 class="mt-2 font-heading text-4xl font-bold text-white md:text-5xl lg:text-6xl">Blog</h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-white/60">Disney tips, park guides, and stories from our family to yours.</p>
        </div>
    </section>

    {{-- Posts Section with Sidebar --}}
    <section class="relative bg-cream py-12 sm:py-16">
        <div class="absolute inset-0 bg-[radial-gradient(#1a1040_1px,transparent_1px)] bg-size-[24px_24px] opacity-[0.02]"></div>

        <div class="relative mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col gap-10 lg:flex-row">
                {{-- Main Content --}}
                <div class="lg:w-[66%]">
            @if($posts->count())
                @php $featured = $posts->first(); $rest = $posts->skip(1); @endphp

                {{-- Featured Post: Single-column, navy gradient + animated border + ribbon --}}
                @if($posts->currentPage() === 1 && !request('q'))
                    @php $featuredStyle = $categoryStyles[$featured->category] ?? $defaultCategoryStyle; @endphp
                    <div class="blog-featured-wrapper mb-8 rounded-3xl">
                        {{-- Corner ribbon --}}
                        <div class="blog-ribbon blog-ribbon-top-left"><span>Featured</span></div>

                        <a href="{{ route('blog.show', $featured) }}" class="blog-featured-card-border group block transition-[transform,box-shadow] duration-300 hover:-translate-y-1 hover:shadow-2xl">
                            <div class="relative p-5 pt-20 sm:p-8 sm:pt-20 md:p-10 md:pt-16 lg:p-12">
                                {{-- Category + read time --}}
                                <div class="mb-5 flex items-center gap-3 md:pl-20">
                                    @if($featured->category)
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase {{ $featuredStyle['badge'] }}">{{ $featured->category_label }}</span>
                                    @endif
                                    <span class="text-xs text-white/30">{{ $featured->reading_time }} min read</span>
                                </div>

                                {{-- Title --}}
                                <h2 class="font-heading text-2xl/snug font-bold text-white transition-colors group-hover:text-gold md:pl-20 md:text-3xl">{{ $featured->title }}</h2>

                                {{-- Excerpt --}}
                                @if($featured->excerpt)
                                    <p class="mt-4 max-w-2xl text-base/relaxed text-white/55 md:pl-20">{{ $featured->excerpt }}</p>
                                @endif

                                {{-- Author + CTA --}}
                                <div class="mt-8 flex items-center justify-between border-t border-white/10 pt-6 md:ml-20">
                                    <div class="flex items-center gap-4">
                                        <div class="flex size-9 items-center justify-center rounded-full border border-gold/20 bg-linear-to-br from-gold/25 to-purple/15 font-heading text-[10px] font-bold text-gold">
                                            {{ $featured->author_initials }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-white">{{ $featured->author_name }}</p>
                                            <p class="text-xs text-white/40">{{ $featured->published_at->format('F j, Y') }}</p>
                                        </div>
                                    </div>
                                    <span class="hidden items-center gap-1.5 text-sm font-semibold text-gold transition-[gap] group-hover:gap-2.5 sm:inline-flex">
                                        Read Article
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @else
                    @php $rest = $posts; @endphp
                @endif

                {{-- Post Grid (single column since sidebar takes space) --}}
                @if($rest->count())
                    <div class="space-y-6">
                        @foreach($rest as $post)
                            @php $postStyle = $categoryStyles[$post->category] ?? $defaultCategoryStyle; @endphp
                            <a href="{{ route('blog.show', $post) }}" class="blog-post-card group relative block rounded-2xl border border-navy/5 bg-white p-5 shadow-sm sm:p-7">
                                {{-- Top accent bar on hover --}}
                                <div class="blog-accent-bar absolute inset-x-0 top-0 h-1 rounded-t-2xl {{ $postStyle['accent'] }}"></div>
                                <div class="mb-3 flex items-center gap-3">
                                    @if($post->category)
                                        <span class="rounded-full px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase {{ $postStyle['badge'] }}">{{ $post->category_label }}</span>
                                    @endif
                                    <span class="text-xs text-navy/25">{{ $post->reading_time }} min read</span>
                                </div>
                                <h3 class="line-clamp-2 font-heading text-xl/snug font-bold text-navy transition-colors group-hover:text-purple">{{ $post->title }}</h3>
                                @if($post->excerpt)
                                    <p class="mt-2 line-clamp-3 text-base/relaxed text-navy/45 sm:text-sm/relaxed">{{ $post->excerpt }}</p>
                                @endif
                                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-navy/5 pt-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-medium text-navy/60">{{ $post->author_name }}</span>
                                        <span class="text-navy/20">·</span>
                                        <span class="text-xs text-navy/30">{{ $post->published_at->format('M j, Y') }}</span>
                                    </div>
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold transition-[gap] group-hover:gap-2 {{ $postStyle['text'] }}">
                                        Read
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Pagination / Load More --}}
                @if($posts->hasPages())
                    <div class="mt-12 text-center">
                        @if($posts->hasMorePages())
                            <a href="{{ $posts->withQueryString()->nextPageUrl() }}" class="inline-flex items-center gap-2 rounded-full bg-linear-to-r from-gold to-gold-light px-8 py-3 text-sm font-semibold text-navy transition-[transform,box-shadow] hover:-translate-y-0.5 hover:shadow-lg hover:shadow-gold/25">
                                Load More Stories
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                        @endif
                    </div>
                    <div class="blog-pagination mt-6 flex justify-center">
                        {{ $posts->withQueryString()->links() }}
                    </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="relative overflow-hidden rounded-3xl bg-linear-to-br from-navy via-navy-light to-purple-dark px-8 py-16">
                    <div class="relative z-10 text-center">
                        <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-gold/30 px-4 py-[0.35rem]">
                            <span class="size-1.5 rounded-full bg-gold"></span>
                            <span class="font-body text-[0.7rem] font-semibold tracking-[0.15em] text-gold uppercase">
                                @if(request('q')) No Results @elseif($category) No Posts Yet @else Coming Soon @endif
                            </span>
                        </div>
                        <h2 class="mb-3 font-heading text-2xl font-bold text-cream">
                            @if(request('q'))
                                No posts match "{{ request('q') }}"
                            @elseif($category)
                                Nothing in {{ \App\Models\Post::CATEGORIES[$category] ?? 'this category' }} yet
                            @else
                                We're putting pen to paper
                            @endif
                        </h2>
                        <p class="mb-6 text-[0.9rem]/[1.8] text-cream/50">
                            @if(request('q'))
                                Try a different search term or browse all posts.
                            @elseif($category)
                                Check out everything else in the meantime.
                            @else
                                Park tips, accessibility insights, and real family stories. Our first posts are in the works.
                            @endif
                        </p>
                        @if(request('q') || $category)
                            <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-2 rounded-lg border border-gold/25 bg-gold/15 px-4 py-2 text-xs font-semibold text-gold transition-transform hover:-translate-y-0.5">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                View all posts
                            </a>
                        @endif
                    </div>
                </div>
            @endif
                </div>{{-- end main content --}}

                {{-- Sidebar --}}
                <aside class="lg:w-[34%]">
                    <div class="space-y-6 lg:sticky lg:top-[90px]">
                        {{-- Search --}}
                        <div class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Search</h3>
                            </div>
                            <form action="{{ route('blog.index') }}" method="GET" class="relative">
                                @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
                                <svg class="absolute top-1/2 left-4 size-4 -translate-y-1/2 text-navy/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <label for="blog-search" class="sr-only">Search blog posts</label>
                                <input id="blog-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search posts..."
                                    class="min-h-12 w-full rounded-xl border border-navy/10 py-3 pr-12 pl-11 text-base text-navy transition-[border-color,box-shadow] outline-none placeholder:text-navy/25 focus:border-gold focus:ring-2 focus:ring-gold/20 sm:text-sm"
                                >
                                @if(request('q'))
                                    <a href="{{ route('blog.index', array_filter(['category' => $category])) }}" class="absolute top-1/2 right-0 flex size-12 -translate-y-1/2 items-center justify-center text-navy/30 transition-colors hover:text-gold" aria-label="Clear search">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </a>
                                @endif
                            </form>
                            @if(request('q'))
                                <p class="mt-2 text-xs text-navy/40">{{ $posts->total() }} {{ Str::plural('result', $posts->total()) }} for "<span class="font-semibold text-gold">{{ request('q') }}</span>"</p>
                            @endif
                        </div>

                        {{-- Blog Stats --}}
                        @if($hasAnyPosts)
                        <div class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Blog Stats</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-xl bg-linear-to-br from-purple/8 to-navy/5 p-4 text-center">
                                    <span class="block font-heading text-3xl font-bold text-navy">{{ $posts->total() }}</span>
                                    <span class="mt-1 block text-xs font-medium tracking-wider text-navy/45 uppercase">{{ Str::plural('Post', $posts->total()) }}</span>
                                </div>
                                <div class="rounded-xl bg-linear-to-br from-gold/8 to-gold/3 p-4 text-center">
                                    <span class="block font-heading text-3xl font-bold text-navy">{{ count($usedCategories) }}</span>
                                    <span class="mt-1 block text-xs font-medium tracking-wider text-navy/45 uppercase">{{ Str::plural('Category', count($usedCategories)) }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Categories --}}
                        <div class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Categories</h3>
                            </div>
                            <div class="space-y-1">
                                <a href="{{ route('blog.index') }}" class="group flex min-h-11 items-center justify-between rounded-xl px-3 py-2.5 transition-colors {{ !$category ? 'bg-gold/10' : 'hover:bg-cream' }}">
                                    <span class="text-sm font-medium {{ !$category ? 'text-gold font-semibold' : 'text-navy/65 group-hover:text-navy' }} transition-colors">All Posts</span>
                                    <span class="rounded-full bg-gold/8 px-3 py-0.5 text-xs font-bold {{ !$category ? 'text-gold' : 'text-gold/70' }}">{{ $posts->total() }}</span>
                                </a>
                                @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                                    @continue(!in_array($slug, $usedCategories))
                                    @php $categoryStyle = $categoryStyles[$slug] ?? $defaultCategoryStyle; @endphp
                                    <a href="{{ route('blog.index', ['category' => $slug]) }}" class="group flex min-h-11 items-center justify-between rounded-xl px-3 py-2.5 transition-colors {{ $category === $slug ? $categoryStyle['subtle'] : 'hover:bg-cream' }}">
                                        <span class="text-sm font-medium transition-colors {{ $category === $slug ? 'font-semibold '.$categoryStyle['text'] : 'text-navy/65 group-hover:text-navy' }}">{{ $label }}</span>
                                        <span class="rounded-full px-3 py-0.5 text-xs font-bold {{ $categoryStyle['subtle'] }} {{ $categoryStyle['text'] }}">{{ $categoryCounts[$slug] ?? 0 }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Sort --}}
                        <div class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Sort By</h3>
                            </div>
                            <div class="flex overflow-hidden rounded-xl border border-navy/10">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
                                   class="flex min-h-11 flex-1 items-center justify-center py-2.5 text-base font-semibold transition-colors sm:text-xs {{ ($sort ?? 'newest') === 'newest' ? 'bg-gold/15 text-gold' : 'text-navy/40 hover:text-navy/60' }}">
                                    Newest
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}"
                                   class="flex min-h-11 flex-1 items-center justify-center border-l border-navy/10 py-2.5 text-base font-semibold transition-colors sm:text-xs {{ ($sort ?? 'newest') === 'oldest' ? 'bg-gold/15 text-gold' : 'text-navy/40 hover:text-navy/60' }}">
                                    Oldest
                                </a>
                            </div>
                        </div>

                        {{-- Podcast CTA --}}
                        <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-linear-to-br from-navy via-navy-light to-navy p-7 text-center">
                            <div class="absolute top-1/2 left-1/2 size-32 -translate-1/2 rounded-full bg-gold/5 blur-3xl"></div>
                            <div class="relative">
                                <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-xl border border-white/10 bg-white/10">
                                    <svg class="size-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                </div>
                                <h3 class="mb-2 font-heading text-lg font-bold text-white">Listen to the Podcast</h3>
                                <p class="mb-5 text-base/relaxed text-white/40 sm:text-sm/relaxed">Disney parks, accessibility, and family stories</p>
                                <a href="{{ route('episodes.index') }}" class="inline-block rounded-full bg-linear-to-r from-gold to-gold-light px-7 py-3 text-sm font-bold text-navy shadow-lg shadow-gold/20 transition-transform hover:-translate-y-0.5">
                                    Browse Episodes
                                </a>
                            </div>
                        </div>

                        {{-- Newsletter --}}
                        <x-newsletter-card subtitle="Disney tips, park updates, and new posts" />
                    </div>
                </aside>
            </div>{{-- end flex --}}
        </div>
    </section>
@endsection
