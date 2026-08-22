@extends('layouts.app')

@section('title', ($post->meta_title ?: $post->title) . ' — Mouse28')
@section('meta_description', $post->meta_description ?: Str::limit($post->excerpt, 160))
@section('og_title', $post->meta_title ?: $post->title)
@section('og_description', $post->meta_description ?: Str::limit($post->excerpt, 200))
@section('og_type', 'article')
@section('robots', ($isPreview ?? false) ? 'noindex,nofollow' : 'index,follow')
@if($post->og_image_url ?: $post->cover_image_url) @section('og_image', $post->og_image_url ?: $post->cover_image_url) @endif

@unless ($isPreview ?? false)
    @push('head')
        <x-structured-data :data="\App\Support\StructuredData::forPost($post)" />
    @endpush
@endunless

@section('content')
    @if ($isPreview ?? false)
        <div role="status" class="bg-gold px-4 py-3 text-center text-sm font-semibold text-navy">Preview mode — this page is only visible to administrators.</div>
    @endif
    <div id="reading-progress" class="fixed top-16 left-0 z-40 h-[3px] w-0 bg-linear-to-r from-gold to-gold-light shadow-[0_0_8px_rgb(212_168_67/40%)] transition-[width] duration-100 ease-linear"></div>

    @php
        $categoryStyles = [
            'disney-tips' => ['surface' => 'from-gold/15 to-gold-light/10', 'icon' => '🏰'],
            'park-accessibility' => ['surface' => 'from-purple-light/15 to-violet-400/10', 'icon' => '💜'],
            'episode-recap' => ['surface' => 'from-emerald-600/15 to-emerald-400/10', 'icon' => '🎙️'],
            'family-life' => ['surface' => 'from-blue-600/15 to-blue-400/10', 'icon' => '👨‍👩‍👧'],
            'autism-awareness' => ['surface' => 'from-pink-600/15 to-pink-400/10', 'icon' => '🧩'],
            'disney-news' => ['surface' => 'from-orange-600/15 to-orange-400/10', 'icon' => '📰'],
            'food-reviews' => ['surface' => 'from-amber-600/15 to-amber-400/10', 'icon' => '🍽️'],
            'resort-reviews' => ['surface' => 'from-teal-600/15 to-teal-300/10', 'icon' => '🏨'],
            'disney-plus' => ['surface' => 'from-indigo-600/15 to-indigo-400/10', 'icon' => '📺'],
            'merchandise' => ['surface' => 'from-rose-600/15 to-rose-400/10', 'icon' => '🛍️'],
            'general' => ['surface' => 'from-slate-600/15 to-slate-400/10', 'icon' => '✨'],
        ];
        $defaultCategoryStyle = ['surface' => 'from-purple/15 to-purple-light/10', 'icon' => '✨'];
    @endphp

    {{-- Hero Section --}}
    <section class="relative flex min-h-[420px] items-end overflow-hidden">
        <span class="sparkle pointer-events-none absolute top-[15%] right-[12%] z-10 text-base text-gold-light/25" aria-hidden="true">✦</span>
        <span class="sparkle-delay pointer-events-none absolute top-[30%] left-[8%] z-10 text-[0.7rem] text-gold-light/15" aria-hidden="true">✦</span>

        <div class="absolute inset-0 bg-linear-to-br from-navy via-navy-light to-navy">
            @if($post->cover_image_url)
                <img src="{{ $post->cover_image_url }}" alt="" aria-hidden="true" class="size-full object-cover">
            @endif
            <div class="absolute inset-0 {{ $post->cover_image_url ? 'bg-linear-to-t from-navy/95 via-navy/70 to-navy/40' : 'bg-linear-to-t from-navy/95 via-navy/60 to-navy/30' }}"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 pt-20 pb-14 sm:px-6">
            {{-- Back link --}}
            <a href="{{ route('blog.index') }}" class="group mb-8 inline-flex min-h-11 items-center gap-1.5 text-base text-white/40 transition-colors hover:text-gold sm:text-sm">
                <svg class="size-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Blog
            </a>

            {{-- Meta row --}}
            <div class="mb-5 flex flex-wrap items-center gap-3">
                @if($post->category)
                    <span class="rounded-full border border-gold/30 bg-gold/15 px-4 py-1.5 text-xs font-bold tracking-wider text-gold uppercase backdrop-blur-sm">
                        {{ $post->category_label }}
                    </span>
                @endif
                <span class="text-sm text-white/30">{{ $post->published_at?->format('F j, Y') ?? 'Not scheduled' }}</span>
                <span class="text-white/20">·</span>
                <span class="text-sm text-white/30" id="reading-indicator">{{ $post->reading_time }} min read</span>
            </div>

            {{-- Title --}}
            <h1 class="max-w-4xl font-heading text-4xl/tight font-bold text-white md:text-5xl lg:text-6xl">
                {{ $post->title }}
            </h1>

            {{-- Gold divider --}}
            <div class="my-6 h-[3px] w-15 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>

            {{-- Excerpt --}}
            @if($post->excerpt)
                <p class="max-w-3xl text-lg/relaxed font-light text-white/50 md:text-xl">{{ $post->excerpt }}</p>
            @endif

            {{-- Author + Share row --}}
            <div class="mt-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex size-12 items-center justify-center rounded-full border-2 border-gold/20 bg-linear-to-br from-gold/30 to-purple/20 font-heading font-bold text-gold">
                        {{ $post->author_initials }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $post->author_name }}</p>
                        <p class="text-xs text-white/30">Mouse28</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="inline-flex size-11 items-center justify-center rounded-full border border-cream/15 bg-cream/5 text-cream/40 backdrop-blur-sm transition-[transform,border-color,background-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-gold-light/50 hover:bg-gold-light/10 hover:text-gold-light hover:shadow-[0_4px_12px_rgb(212_168_67/20%)]" aria-label="Share on X">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="inline-flex size-11 items-center justify-center rounded-full border border-cream/15 bg-cream/5 text-cream/40 backdrop-blur-sm transition-[transform,border-color,background-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-gold-light/50 hover:bg-gold-light/10 hover:text-gold-light hover:shadow-[0_4px_12px_rgb(212_168_67/20%)]" aria-label="Share on Facebook">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <button type="button" class="relative inline-flex size-11 items-center justify-center rounded-full border border-cream/15 bg-cream/5 text-cream/40 backdrop-blur-sm transition-[transform,border-color,background-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-gold-light/50 hover:bg-gold-light/10 hover:text-gold-light hover:shadow-[0_4px_12px_rgb(212_168_67/20%)]" data-copy-link aria-label="Copy link">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span class="copy-feedback absolute -bottom-9 left-1/2 hidden -translate-x-1/2 rounded-full bg-gold px-3 py-1 text-[10px] whitespace-nowrap text-white shadow-lg" aria-live="polite">Copied!</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Content Section --}}
    <section class="relative bg-cream py-14">
        {{-- Subtle decorative dots --}}
        <div class="absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent via-gold/20 to-transparent"></div>

        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col gap-12 lg:flex-row">
                {{-- Main Content --}}
                <div class="lg:w-[66%]">
                    <article id="article-body" data-reading-minutes="{{ $post->reading_time }}" class="relative rounded-3xl border border-navy/5 bg-white p-5 shadow-lg shadow-navy/5 sm:p-8 md:p-14">
                        {{-- Decorative corner accent --}}
                        <div class="absolute top-0 right-0 size-24 overflow-hidden rounded-tr-3xl">
                            <div class="absolute -top-12 -right-12 size-24 rotate-45 bg-linear-to-bl from-gold/8 to-transparent"></div>
                        </div>

                        <div class="blog-article-content prose-navy prose prose-lg max-w-none text-[1.1rem] leading-[1.85] text-navy/80">
                            {!! Str::markdown($post->body ?? '', [
                                'html_input' => 'strip',
                                'allow_unsafe_links' => false,
                                'renderer' => [
                                    'soft_break' => "<br />\n",
                                ],
                            ]) !!}
                        </div>

                        {{-- End flourish --}}
                        <div class="mt-12 flex items-center justify-center gap-3 border-t border-navy/5 pt-8">
                            <span class="text-gold/30">✦</span>
                            <span class="text-lg text-gold/50">✦</span>
                            <span class="text-gold/30">✦</span>
                        </div>
                    </article>

                    {{-- Author Card --}}
                    <div class="relative mt-10 overflow-hidden rounded-3xl border border-navy/5 bg-white p-5 shadow-lg shadow-navy/5 sm:p-8 md:p-10">
                        <div class="absolute inset-x-0 top-0 h-1 bg-linear-to-r from-gold via-purple to-gold"></div>
                        <div class="flex flex-col items-start gap-6 sm:flex-row">
                            <div class="flex size-20 shrink-0 items-center justify-center rounded-2xl border border-gold/15 bg-linear-to-br from-gold/25 to-purple/15 font-heading text-xl font-bold text-gold">
                                {{ $post->author_initials }}
                            </div>
                            <div>
                                <span class="text-xs font-bold tracking-widest text-gold uppercase">Written by</span>
                                <h2 class="mt-1 font-heading text-2xl font-bold text-navy">{{ $post->author_name }}</h2>
                                <div class="my-3 h-[3px] w-15 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <p class="text-base/relaxed text-navy/55 sm:text-sm/relaxed">
                                    @if(Str::contains($post->author_name, '&') || (Str::contains($post->author_name, 'Jeffrey') && Str::contains($post->author_name, 'Cassie')))
                                        The couple behind Mouse28. Over a decade as Disney passholders, navigating park life with their daughter Viola and sharing every tip, review, and memory along the way.
                                    @elseif(Str::contains($post->author_name, 'Cassie'))
                                        Co-host of Mouse28. Disney magic-maker, accessibility champion, and the planner behind every park day.
                                    @elseif(Str::contains($post->author_name, 'Jeffrey'))
                                        Co-host of Mouse28. Theme park nerd, tech enthusiast, and the voice keeping it real about Disney life.
                                    @else
                                        Disney park explorer, accessibility advocate, and parent.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Share This Post --}}
                    <div class="mt-10 rounded-3xl border border-navy/5 bg-white p-5 text-center shadow-lg shadow-navy/5 sm:p-8 md:p-10">
                        <span class="text-xs font-bold tracking-widest text-gold uppercase">Enjoyed this post?</span>
                        <h2 class="my-2 font-heading text-xl font-bold text-navy">Share it with fellow Disney fans</h2>
                        <p class="mb-6 text-base text-navy/40 sm:text-sm">Help others discover Mouse28</p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title . ' — Mouse28') }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-navy/10 bg-white px-5 py-2.5 text-base font-semibold text-navy/60 transition-[transform,border-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-[#1da1f2] hover:text-[#1da1f2] hover:shadow-[0_4px_12px_rgb(26_16_64/10%)] sm:text-[0.8rem]">
                                <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                Share on X
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-navy/10 bg-white px-5 py-2.5 text-base font-semibold text-navy/60 transition-[transform,border-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-[#1877f2] hover:text-[#1877f2] hover:shadow-[0_4px_12px_rgb(26_16_64/10%)] sm:text-[0.8rem]">
                                <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Share on Facebook
                            </a>
                            <button type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-full border border-navy/10 bg-white px-5 py-2.5 text-base font-semibold text-navy/60 transition-[transform,border-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-gold hover:text-gold hover:shadow-[0_4px_12px_rgb(26_16_64/10%)] sm:text-[0.8rem]" data-copy-link>
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <span data-copy-label>Copy Link</span>
                                <span class="hidden" data-copy-feedback aria-live="polite">Copied! ✓</span>
                            </button>
                        </div>
                    </div>

                    {{-- Related Episode --}}
                    @if($post->episode)
                        <div class="mt-8 rounded-3xl border border-navy/5 bg-white p-8 shadow-lg shadow-navy/5">
                            <span class="text-xs font-bold tracking-widest text-gold uppercase">Related Episode</span>
                            <a href="{{ route('episodes.show', $post->episode) }}" class="group mt-4 block">
                                <div class="flex items-center gap-5">
                                    <span class="inline-flex size-14 shrink-0 items-center justify-center rounded-2xl border border-purple/10 bg-linear-to-br from-purple/15 to-gold/10 font-heading text-lg font-bold text-purple">{{ $post->episode->episode_number }}</span>
                                    <div>
                                        <h2 class="font-heading text-lg font-semibold text-navy transition-colors group-hover:text-purple">{{ $post->episode->title }}</h2>
                                        <p class="mt-1 text-base text-navy/40 transition-colors group-hover:text-gold sm:text-sm">Listen to the full episode →</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif

                    {{-- Read Next --}}
                    @if($recentPosts->count())
                        <div class="mt-12">
                            <div class="mb-6 flex items-center gap-4">
                                <h2 class="font-heading text-xl font-bold text-navy">Continue Reading</h2>
                                <div class="h-px flex-1 bg-linear-to-r from-navy/10 to-transparent"></div>
                            </div>
                            <div class="grid gap-6 sm:grid-cols-2">
                                @foreach($recentPosts->take(2) as $next)
                                    @php $nextStyle = $categoryStyles[$next->category] ?? $defaultCategoryStyle; @endphp
                                    <a href="{{ route('blog.show', $next) }}" class="group overflow-hidden rounded-2xl border border-navy/5 bg-white shadow-md shadow-navy/5 transition-[transform,box-shadow] duration-300 hover:-translate-y-1 hover:shadow-xl">
                                        <div class="h-40 overflow-hidden">
                                            @if($next->cover_image_url)
                                                <img src="{{ $next->cover_image_url }}" alt="" class="size-full object-cover transition-transform duration-500 group-hover:scale-105">
                                            @else
                                                <div class="flex size-full items-center justify-center bg-linear-to-br {{ $nextStyle['surface'] }}">
                                                    <span class="text-4xl opacity-60 transition-transform duration-500 group-hover:scale-110">{{ $nextStyle['icon'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="p-6">
                                            @if($next->category)
                                                <span class="text-[10px] font-bold tracking-wider text-gold uppercase">{{ $next->category_label }}</span>
                                            @endif
                                            <h3 class="mt-1 line-clamp-2 font-heading text-base/snug font-semibold text-navy transition-colors group-hover:text-purple">{{ $next->title }}</h3>
                                            <span class="mt-3 block text-xs text-navy/30">{{ $next->reading_time }} min read</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[34%]">
                    <div class="space-y-7 lg:sticky lg:top-[90px]">
                        {{-- Table of Contents (populated by JS) --}}
                        <div id="toc-card" class="hidden rounded-2xl border border-navy/5 bg-white p-7 shadow-md shadow-navy/5">
                            <div class="mb-4 flex items-center gap-3">
                                <div class="h-[3px] w-15 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">In This Post</h3>
                            </div>
                            <nav id="toc-nav" class="space-y-0.5"></nav>
                        </div>

                        {{-- Recent Posts --}}
                        @if($recentPosts->count())
                            <div class="rounded-2xl border border-navy/5 bg-white p-7 shadow-md shadow-navy/5">
                                <div class="mb-5 flex items-center gap-3">
                                    <div class="h-[3px] w-15 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                    <h3 class="font-heading text-lg font-bold text-navy">Recent Posts</h3>
                                </div>
                                <div class="space-y-4">
                                    @foreach($recentPosts as $recent)
                                        @php $recentStyle = $categoryStyles[$recent->category] ?? $defaultCategoryStyle; @endphp
                                        <a href="{{ route('blog.show', $recent) }}" class="group -mx-2 flex items-start gap-4 rounded-xl p-2 transition-colors hover:bg-cream/50">
                                            @if($recent->cover_image_url)
                                                <img src="{{ $recent->cover_image_url }}" alt="" class="size-16 shrink-0 rounded-xl object-cover shadow-sm">
                                            @else
                                                <div class="flex size-16 shrink-0 items-center justify-center rounded-xl bg-linear-to-br {{ $recentStyle['surface'] }}">
                                                    <span class="text-2xl opacity-60">{{ $recentStyle['icon'] }}</span>
                                                </div>
                                            @endif
                                            <div class="min-w-0 pt-0.5">
                                                <h4 class="line-clamp-2 text-sm/snug font-semibold text-navy transition-colors group-hover:text-purple">{{ $recent->title }}</h4>
                                                <span class="mt-1.5 block text-xs text-navy/35">{{ $recent->published_at->format('M j, Y') }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Categories --}}
                        <div class="rounded-2xl border border-navy/5 bg-white p-7 shadow-md shadow-navy/5">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-[3px] w-15 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Categories</h3>
                            </div>
                            <div class="space-y-1">
                                @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                                    @php $count = $categoryCounts[$slug] ?? 0; @endphp
                                    @if($count > 0)
                                        <a href="{{ route('blog.index', ['category' => $slug]) }}" class="group flex min-h-11 items-center justify-between rounded-xl px-3 py-2.5 transition-colors hover:bg-cream">
                                            <span class="text-sm font-medium text-navy/65 transition-colors group-hover:text-navy">{{ $label }}</span>
                                            <span class="rounded-full bg-gold/8 px-3 py-0.5 text-xs font-bold text-gold/70 transition-colors group-hover:bg-gold/15">{{ $count }}</span>
                                        </a>
                                    @endif
                                @endforeach
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
            </div>
        </div>
    </section>

    {{-- Back to Top Button --}}
    <button type="button" id="back-to-top" class="pointer-events-none fixed right-8 bottom-8 z-50 flex size-12 translate-y-2.5 cursor-pointer items-center justify-center rounded-full bg-linear-to-br from-gold to-gold-light text-white opacity-0 shadow-[0_4px_15px_rgb(212_168_67/30%)] transition-[transform,opacity,box-shadow] duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgb(212_168_67/40%)]" aria-label="Back to top">
        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
    </button>
@endsection
