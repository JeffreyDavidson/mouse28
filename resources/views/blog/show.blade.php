<x-layouts.app
    :title="($post->meta_title ?: $post->title).' — Mouse28'"
    :description="$post->meta_description ?: Str::limit($post->excerpt, 160)"
    :og-title="$post->meta_title ?: $post->title"
    :og-description="$post->meta_description ?: Str::limit($post->excerpt, 200)"
    og-type="article"
    :og-image="$post->og_image_url ?: $post->cover_image_url"
    :robots="($isPreview ?? false) ? 'noindex,nofollow' : 'index,follow'"
>
    @unless ($isPreview ?? false)
        @push('head')
            <x-structured-data :data="\App\Support\StructuredData::forPost($post)" />
        @endpush
    @endunless

    @if ($isPreview ?? false)
        <div role="status" class="bg-gold text-navy px-4 py-3 text-center text-sm font-semibold">
            Preview mode — this page is only visible to administrators.
        </div>
    @endif
    <div
        id="reading-progress"
        class="from-gold to-gold-light fixed top-16 left-0 z-40 h-[3px] w-0 bg-linear-to-r shadow-[0_0_8px_rgb(212_168_67/40%)] transition-[width] duration-100 ease-linear"
    ></div>

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
            'disney-plus' => ['surface' => 'from-purple/15 to-purple-light/10', 'icon' => '📺'],
            'merchandise' => ['surface' => 'from-rose-600/15 to-rose-400/10', 'icon' => '🛍️'],
            'general' => ['surface' => 'from-slate-600/15 to-slate-400/10', 'icon' => '✨'],
        ];
        $defaultCategoryStyle = ['surface' => 'from-purple/15 to-purple-light/10', 'icon' => '✨'];
    @endphp

    {{-- Hero Section --}}
    <section class="relative flex min-h-[420px] items-end overflow-hidden">
        <span
            class="sparkle text-gold-light/25 pointer-events-none absolute top-[15%] right-[12%] z-10 text-base"
            aria-hidden="true"
        >✦</span>
        <span
            class="sparkle-delay text-gold-light/15 pointer-events-none absolute top-[30%] left-[8%] z-10 text-[0.7rem]"
            aria-hidden="true"
        >✦</span>

        <div class="from-navy via-navy-light to-navy absolute inset-0 bg-linear-to-br" data-print-hidden>
            @if ($post->cover_image_url)
                <img
                    src="{{ $post->cover_image_url }}"
                    alt=""
                    aria-hidden="true"
                    loading="eager"
                    fetchpriority="high"
                    decoding="async"
                    class="size-full object-cover"
                />
            @endif
            <div class="absolute inset-0 {{ $post->cover_image_url ? 'bg-linear-to-t from-navy/95 via-navy/70 to-navy/40' : 'bg-linear-to-t from-navy/95 via-navy/60 to-navy/30' }}"></div>
        </div>

        <div class="relative z-10 mx-auto w-full max-w-6xl px-4 pt-20 pb-14 wrap-anywhere sm:px-6">
            {{-- Back link --}}
            <a
                href="{{ route('blog.index') }}"
                class="group hover:text-gold mb-8 inline-flex min-h-12 items-center gap-1.5 text-base text-white/60 transition-colors sm:text-sm"
            >
                <svg aria-hidden="true" class="size-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                Back to Blog
            </a>

            {{-- Meta row --}}
            <div class="mb-5 flex flex-wrap items-center gap-3">
                @if ($post->category)
                    <a
                        href="{{ route('blog.index', ['category' => $post->category]) }}"
                        class="border-gold/30 bg-gold/15 text-gold hover:border-gold hover:bg-gold/25 inline-flex min-h-12 items-center rounded-full border px-4 py-1.5 text-xs font-bold tracking-wider uppercase backdrop-blur-sm transition-colors"
                    >
                        {{ $post->category_label }}
                    </a>
                @endif
                <span class="text-sm text-white/60">{{ $post->published_at?->format('F j, Y') ?? 'Not scheduled' }}</span>
                <span class="text-white/20">·</span>
                <span class="text-sm text-white/60" id="reading-indicator">{{ $post->reading_time }} min read</span>
            </div>

            {{-- Title --}}
            <h1 class="font-heading max-w-4xl text-4xl/tight font-bold text-white md:text-5xl lg:text-6xl">
                {{ $post->title }}
            </h1>

            {{-- Gold divider --}}
            <div class="from-gold to-gold-light my-6 h-[3px] w-15 rounded-sm bg-linear-to-r"></div>

            {{-- Excerpt --}}
            @if ($post->excerpt)
                <p class="max-w-3xl text-lg/relaxed font-light text-white/50 md:text-xl">{{ $post->excerpt }}</p>
            @endif

            {{-- Author + Share row --}}
            <div class="mt-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="border-gold/20 from-gold/30 to-purple/20 font-heading text-gold flex size-12 items-center justify-center rounded-full border-2 bg-linear-to-br font-bold">
                        {{ $post->author_initials }}
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">{{ $post->author_name }}</p>
                        <p class="text-xs text-white/60">Mouse28</p>
                    </div>
                </div>
                <div class="flex items-center gap-2" data-print-hidden>
                    <a
                        href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}"
                        target="_blank"
                        rel="noopener"
                        class="border-cream/15 bg-cream/5 text-cream/60 hover:border-gold-light/50 hover:bg-gold-light/10 hover:text-gold-light inline-flex size-12 items-center justify-center rounded-full border backdrop-blur-sm transition-[transform,border-color,background-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgb(212_168_67/20%)]"
                        aria-label="Share on X"
                    >
                        <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                    </a>
                    <a
                        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                        target="_blank"
                        rel="noopener"
                        class="border-cream/15 bg-cream/5 text-cream/60 hover:border-gold-light/50 hover:bg-gold-light/10 hover:text-gold-light inline-flex size-12 items-center justify-center rounded-full border backdrop-blur-sm transition-[transform,border-color,background-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgb(212_168_67/20%)]"
                        aria-label="Share on Facebook"
                    >
                        <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                    </a>
                    <button
                        type="button"
                        class="border-cream/15 bg-cream/5 text-cream/60 hover:border-gold-light/50 hover:bg-gold-light/10 hover:text-gold-light relative inline-flex size-12 items-center justify-center rounded-full border backdrop-blur-sm transition-[transform,border-color,background-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgb(212_168_67/20%)]"
                        data-copy-link
                        aria-label="Copy link"
                    >
                        <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                        <span
                            class="copy-feedback bg-gold absolute -bottom-9 left-1/2 hidden -translate-x-1/2 rounded-full px-3 py-1 text-[10px] whitespace-nowrap text-white shadow-lg"
                            aria-live="polite"
                        >Copied!</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Content Section --}}
    <section class="bg-cream relative py-14">
        {{-- Subtle decorative dots --}}
        <div class="via-gold/20 absolute inset-x-0 top-0 h-px bg-linear-to-r from-transparent to-transparent"></div>

        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col gap-12 lg:flex-row">
                {{-- Main Content --}}
                <div class="min-w-0 lg:w-[66%]">
                    <article
                        id="article-body"
                        class="border-navy/5 shadow-navy/5 relative rounded-3xl border bg-white p-5 shadow-lg sm:p-8 md:p-14"
                    >
                        @if ($post->last_reviewed_at)
                            <p class="text-navy/65 mb-8 text-sm">
                                Last reviewed {{ $post->last_reviewed_at->format('F j, Y') }}
                            </p>
                        @endif

                        @if ($post->isReviewDue())
                            <div
                                role="note"
                                class="mb-8 rounded-2xl border border-amber-500/30 bg-amber-100 px-5 py-4 text-sm/relaxed text-amber-950"
                            >
                                This post is due for editorial review. Disney policies and park operations can change,
                                so confirm current details with the official source before your visit.
                            </div>
                        @endif

                        {{-- Decorative corner accent --}}
                        <div class="absolute top-0 right-0 size-24 overflow-hidden rounded-tr-3xl">
                            <div class="from-gold/8 absolute -top-12 -right-12 size-24 rotate-45 bg-linear-to-bl to-transparent"></div>
                        </div>

                        <div class="blog-article-content prose-navy prose text-navy/80 max-w-[68ch] text-[1.0625rem] leading-[1.8] wrap-anywhere">
                            {!!
                                Str::markdown($post->body ?? '', [
                                    'html_input' => 'strip',
                                    'allow_unsafe_links' => false,
                                    'renderer' => [
                                        'soft_break' => "<br />\n",
                                    ],
                                ])
                            !!}
                        </div>

                        @if ($post->source_url)
                            <div class="border-navy/10 mt-10 border-t pt-6">
                                <p class="text-navy/65 text-sm">
                                    Policies can change. Review the official source before your visit.
                                </p>
                                <a
                                    href="{{ $post->source_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-purple hover:text-navy mt-2 inline-flex min-h-12 items-center font-semibold"
                                >View official source ↗</a>
                            </div>
                        @endif

                        {{-- End flourish --}}
                        <div
                            class="border-navy/5 mt-12 flex items-center justify-center gap-3 border-t pt-8"
                            aria-hidden="true"
                        >
                            <span class="text-gold/30">✦</span>
                            <span class="text-gold/50 text-lg">✦</span>
                            <span class="text-gold/30">✦</span>
                        </div>
                    </article>

                    {{-- Author Card --}}
                    <div class="border-navy/5 shadow-navy/5 relative mt-10 overflow-hidden rounded-3xl border bg-white p-5 shadow-lg sm:p-8 md:p-10">
                        <div class="from-gold via-purple to-gold absolute inset-x-0 top-0 h-1 bg-linear-to-r"></div>
                        <div class="flex flex-col items-start gap-6 sm:flex-row">
                            <div class="border-gold/15 from-gold/25 to-purple/15 font-heading text-gold flex size-20 shrink-0 items-center justify-center rounded-2xl border bg-linear-to-br text-xl font-bold">
                                {{ $post->author_initials }}
                            </div>
                            <div>
                                <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">Written by</span>
                                <h2 class="font-heading text-navy mt-1 text-2xl font-bold">{{ $post->author_name }}</h2>
                                <div class="from-gold to-gold-light my-3 h-[3px] w-15 rounded-sm bg-linear-to-r"></div>
                                <p class="text-navy/65 text-base/relaxed sm:text-sm/relaxed">
                                    @if (Str::contains($post->author_name, '&') || (Str::contains($post->author_name, 'Jeffrey') && Str::contains($post->author_name, 'Cassie')))
                                        The couple behind Mouse28. Over a decade as Disney passholders, navigating park
                                        life with their daughter Viola and sharing every tip, review, and memory along
                                        the way.
                                    @elseif (Str::contains($post->author_name, 'Cassie'))
                                        Co-host of Mouse28. Disney magic-maker, accessibility champion, and the planner
                                        behind every park day.
                                    @elseif (Str::contains($post->author_name, 'Jeffrey'))
                                        Co-host of Mouse28. Theme park nerd, tech enthusiast, and the voice keeping it
                                        real about Disney life.
                                    @else
                                        Disney park explorer, accessibility advocate, and parent.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Share This Post --}}
                    <div
                        class="border-navy/5 shadow-navy/5 mt-10 rounded-3xl border bg-white p-5 text-center shadow-lg sm:p-8 md:p-10"
                        data-print-hidden
                    >
                        <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">Enjoyed this post?</span>
                        <h2 class="font-heading text-navy my-2 text-xl font-bold">Share it with fellow Disney fans</h2>
                        <p class="text-navy/65 mb-6 text-base sm:text-sm">Help others discover Mouse28</p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <a
                                href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title . ' — Mouse28') }}"
                                target="_blank"
                                rel="noopener"
                                class="border-navy/10 text-navy/60 inline-flex min-h-12 items-center justify-center gap-2 rounded-full border bg-white px-5 py-2.5 text-base font-semibold transition-[transform,border-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-[#1da1f2] hover:text-[#1da1f2] hover:shadow-[0_4px_12px_rgb(26_16_64/10%)] sm:text-[0.8rem]"
                            >
                                <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                                Share on X
                            </a>
                            <a
                                href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                                target="_blank"
                                rel="noopener"
                                class="border-navy/10 text-navy/60 inline-flex min-h-12 items-center justify-center gap-2 rounded-full border bg-white px-5 py-2.5 text-base font-semibold transition-[transform,border-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:border-[#1877f2] hover:text-[#1877f2] hover:shadow-[0_4px_12px_rgb(26_16_64/10%)] sm:text-[0.8rem]"
                            >
                                <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                                Share on Facebook
                            </a>
                            <button
                                type="button"
                                class="border-navy/10 text-navy/60 hover:border-gold hover:text-gold inline-flex min-h-12 items-center justify-center gap-2 rounded-full border bg-white px-5 py-2.5 text-base font-semibold transition-[transform,border-color,color,box-shadow] duration-300 hover:-translate-y-0.5 hover:shadow-[0_4px_12px_rgb(26_16_64/10%)] sm:text-[0.8rem]"
                                data-copy-link
                            >
                                <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                <span data-copy-label>Copy Link</span>
                                <span class="hidden" data-copy-feedback aria-live="polite">Copied! ✓</span>
                            </button>
                        </div>
                    </div>

                    {{-- Related Episode --}}
                    @if ($post->episode)
                        <div
                            class="border-navy/5 shadow-navy/5 mt-8 rounded-3xl border bg-white p-8 shadow-lg"
                            data-print-hidden
                        >
                            <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">Related Episode</span>
                            <a href="{{ route('episodes.show', $post->episode) }}" class="group mt-4 block">
                                <div class="flex items-center gap-5">
                                    <span class="border-purple/10 from-purple/15 to-gold/10 font-heading text-purple inline-flex size-14 shrink-0 items-center justify-center rounded-2xl border bg-linear-to-br text-lg font-bold">{{ $post->episode->episode_number }}</span>
                                    <div>
                                        <h2 class="font-heading text-navy group-hover:text-purple text-lg font-semibold transition-colors">
                                            {{ $post->episode->title }}
                                        </h2>
                                        <p class="text-navy/65 group-hover:text-gold mt-1 text-base transition-colors sm:text-sm">
                                            Listen to the full episode →
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif

                    {{-- Read Next --}}
                    @if ($recentPosts->count())
                        <div class="mt-12" data-print-hidden>
                            <div class="mb-6 flex items-center gap-4">
                                <h2 class="font-heading text-navy text-xl font-bold">Continue Reading</h2>
                                <div class="from-navy/10 h-px flex-1 bg-linear-to-r to-transparent"></div>
                            </div>
                            <div class="grid gap-6 sm:grid-cols-2">
                                @foreach ($recentPosts->take(2) as $next)
                                    @php $nextStyle = $categoryStyles[$next->category] ?? $defaultCategoryStyle; @endphp
                                    <a
                                        href="{{ route('blog.show', $next) }}"
                                        class="group border-navy/5 shadow-navy/5 overflow-hidden rounded-2xl border bg-white shadow-md transition-[transform,box-shadow] duration-300 hover:-translate-y-1 hover:shadow-xl"
                                    >
                                        <div class="h-40 overflow-hidden">
                                            @if ($next->cover_image_url)
                                                <img
                                                    src="{{ $next->cover_image_url }}"
                                                    alt=""
                                                    loading="lazy"
                                                    decoding="async"
                                                    class="size-full object-cover transition-transform duration-500 group-hover:scale-105"
                                                />
                                            @else
                                                <div class="flex size-full items-center justify-center bg-linear-to-br {{ $nextStyle['surface'] }}">
                                                    <span
                                                        class="text-4xl opacity-60 transition-transform duration-500 group-hover:scale-110"
                                                        aria-hidden="true"
                                                    >{{ $nextStyle['icon'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="p-6">
                                            @if ($next->category)
                                                <span class="text-gold-ink text-[10px] font-bold tracking-wider uppercase">{{ $next->category_label }}</span>
                                            @endif
                                            <h3 class="font-heading text-navy group-hover:text-purple mt-1 line-clamp-2 text-base/snug font-semibold transition-colors">
                                                {{ $next->title }}
                                            </h3>
                                            <span class="text-navy/65 mt-3 block text-xs">{{ $next->reading_time }} min read</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[34%]" data-print-hidden>
                    <div class="space-y-7 lg:sticky lg:top-[90px]">
                        {{-- Table of Contents (populated by JS) --}}
                        <div
                            id="toc-card"
                            class="border-navy/5 shadow-navy/5 hidden rounded-2xl border bg-white p-7 shadow-md"
                        >
                            <div class="mb-4 flex items-center gap-3">
                                <div class="from-gold to-gold-light h-[3px] w-15 rounded-sm bg-linear-to-r"></div>
                                <h3 class="font-heading text-navy text-lg font-bold">In This Post</h3>
                            </div>
                            <nav id="toc-nav" class="space-y-0.5"></nav>
                        </div>

                        {{-- Recent Posts --}}
                        @if ($recentPosts->count())
                            <div class="border-navy/5 shadow-navy/5 rounded-2xl border bg-white p-7 shadow-md">
                                <div class="mb-5 flex items-center gap-3">
                                    <div class="from-gold to-gold-light h-[3px] w-15 rounded-sm bg-linear-to-r"></div>
                                    <h3 class="font-heading text-navy text-lg font-bold">Recent Posts</h3>
                                </div>
                                <div class="space-y-4">
                                    @foreach ($recentPosts as $recent)
                                        @php $recentStyle = $categoryStyles[$recent->category] ?? $defaultCategoryStyle; @endphp
                                        <a
                                            href="{{ route('blog.show', $recent) }}"
                                            class="group hover:bg-cream/50 -mx-2 flex items-start gap-4 rounded-xl p-2 transition-colors"
                                        >
                                            @if ($recent->cover_image_url)
                                                <img
                                                    src="{{ $recent->cover_image_url }}"
                                                    alt=""
                                                    loading="lazy"
                                                    decoding="async"
                                                    class="size-16 shrink-0 rounded-xl object-cover shadow-sm"
                                                />
                                            @else
                                                <div class="flex size-16 shrink-0 items-center justify-center rounded-xl bg-linear-to-br {{ $recentStyle['surface'] }}">
                                                    <span
                                                        class="text-2xl opacity-60"
                                                        aria-hidden="true"
                                                    >{{ $recentStyle['icon'] }}</span>
                                                </div>
                                            @endif
                                            <div class="min-w-0 pt-0.5">
                                                <h4 class="text-navy group-hover:text-purple line-clamp-2 text-sm/snug font-semibold transition-colors">
                                                    {{ $recent->title }}
                                                </h4>
                                                <span class="text-navy/65 mt-1.5 block text-xs">{{ $recent->published_at->format('M j, Y') }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Categories --}}
                        <div class="border-navy/5 shadow-navy/5 rounded-2xl border bg-white p-7 shadow-md">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="from-gold to-gold-light h-[3px] w-15 rounded-sm bg-linear-to-r"></div>
                                <h3 class="font-heading text-navy text-lg font-bold">Categories</h3>
                            </div>
                            <div class="space-y-1">
                                @foreach (\App\Models\Post::CATEGORIES as $slug => $label)
                                    @php $count = $categoryCounts[$slug] ?? 0; @endphp
                                    @if ($count > 0)
                                        <a
                                            href="{{ route('blog.index', ['category' => $slug]) }}"
                                            class="group hover:bg-cream flex min-h-12 items-center justify-between rounded-xl px-3 py-2.5 transition-colors"
                                        >
                                            <span class="text-navy/65 group-hover:text-navy text-sm font-medium transition-colors">{{ $label }}</span>
                                            <span class="bg-gold/8 text-gold-ink group-hover:bg-gold/15 rounded-full px-3 py-0.5 text-xs font-bold transition-colors">{{ $count }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Podcast CTA --}}
                        <div class="from-navy via-navy-light to-navy relative overflow-hidden rounded-2xl border border-white/5 bg-linear-to-br p-7 text-center">
                            <div class="bg-gold/5 absolute top-1/2 left-1/2 size-32 -translate-1/2 rounded-full blur-3xl"></div>
                            <div class="relative">
                                <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-xl border border-white/10 bg-white/10">
                                    <svg aria-hidden="true" class="text-gold size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                                </div>
                                <h3 class="font-heading mb-2 text-lg font-bold text-white">Listen to the Podcast</h3>
                                <p class="mb-5 text-base/relaxed text-white/60 sm:text-sm/relaxed">
                                    Disney parks, accessibility, and family stories
                                </p>
                                <a
                                    href="{{ route('episodes.index') }}"
                                    class="from-gold to-gold-light text-navy shadow-gold/20 inline-block rounded-full bg-linear-to-r px-7 py-3 text-sm font-bold shadow-lg transition-transform hover:-translate-y-0.5"
                                >
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
    <button
        type="button"
        id="back-to-top"
        class="from-gold to-gold-light text-navy pointer-events-none invisible fixed right-4 bottom-20 z-50 flex size-12 translate-y-2.5 cursor-pointer items-center justify-center rounded-full bg-linear-to-br opacity-0 shadow-[0_4px_15px_rgb(212_168_67/30%)] transition-[transform,opacity,box-shadow] duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgb(212_168_67/40%)] sm:right-8 sm:bottom-8"
        aria-label="Back to top"
        aria-hidden="true"
        tabindex="-1"
    >
        <svg aria-hidden="true" class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" /></svg>
    </button>
</x-layouts.app>
