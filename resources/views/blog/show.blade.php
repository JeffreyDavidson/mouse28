<x-layouts.app
    :title="($post->meta_title ?: $post->title).' — Mouse28'"
    :description="$post->meta_description ?: Str::limit($post->excerpt, 160)"
    :og-title="$post->meta_title ?: $post->title"
    :og-description="$post->meta_description ?: Str::limit($post->excerpt, 200)"
    og-type="article"
    :og-image="$post->og_image_url ?: $post->cover_image_url"
    :robots="($isPreview ?? false) ? 'noindex,nofollow' : 'index,follow'"
    :dispatch-layout="true"
    :show-footer-newsletter="false"
>
    <!--
        THESIS: A Mouse28 post should feel like opening a family field journal, not entering a publishing template.
        OWN-WORLD: Navy cloth, cream paper, published artwork, Besley headlines, gold rules, and generous reading space.
        STORY: Establish the story, read without interruption, meet its author, then continue to related work.
        FIRST VIEWPORT: The title and practical promise share the frame with the post's real artwork.
        FORM [seed: reading-sheet]: Artwork-led masthead followed by one uninterrupted editorial column.
    -->
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
        class="from-gold to-gold-light fixed top-16 left-0 z-40 h-[3px] w-0 bg-linear-to-r transition-[width] duration-100 ease-linear"
    ></div>

    <section class="editorial-detail-hero bg-navy text-cream relative overflow-hidden">
        <div class="relative mx-auto grid max-w-[86rem] gap-10 px-4 py-10 sm:px-6 sm:py-14 lg:grid-cols-[7fr_5fr] lg:items-center lg:gap-16 lg:py-20">
            <div class="min-w-0 wrap-anywhere">
                <a
                    href="{{ route('blog.index') }}"
                    class="text-cream/65 hover:text-gold inline-flex min-h-12 items-center gap-2 text-sm font-semibold transition-colors"
                >
                    <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    Back to Blog
                </a>

                <div class="text-cream/65 mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                    @if ($post->category)
                        <a
                            href="{{ route('blog.index', ['category' => $post->category]) }}"
                            class="text-gold hover:text-cream inline-flex min-h-12 items-center font-semibold underline decoration-current/35 underline-offset-8 transition-colors"
                        >{{ $post->category_label }}</a>
                    @endif
                    <span>{{ $post->published_at?->format('F j, Y') ?? 'Not scheduled' }}</span>
                    <span id="reading-indicator">{{ $post->reading_time }} min read</span>
                </div>

                <h1 class="font-heading mt-4 max-w-4xl text-4xl/tight [font-weight:680] tracking-[-0.03em] text-balance sm:text-5xl/tight lg:text-6xl/tight">
                    {{ $post->title }}
                </h1>

                @if ($post->excerpt)
                    <p class="text-cream/72 mt-6 max-w-3xl text-lg/8 text-pretty">{{ $post->excerpt }}</p>
                @endif

                <div class="border-gold/30 mt-8 flex flex-wrap items-center justify-between gap-5 border-t pt-6">
                    <div class="flex items-center gap-3">
                        <span class="border-gold/30 text-gold font-heading inline-flex size-12 items-center justify-center rounded-full border text-sm font-semibold">
                            {{ $post->author_initials }}
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">{{ $post->author_name }}</span>
                            <span class="text-cream/55 block text-xs">Mouse28</span>
                        </span>
                    </div>
                    <div class="flex items-center gap-2" data-print-hidden>
                        <a
                            href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}"
                            target="_blank"
                            rel="noopener"
                            class="border-cream/20 text-cream/70 hover:border-gold hover:text-gold inline-flex size-12 items-center justify-center rounded-full border transition-colors"
                            aria-label="Share on X"
                        >
                            <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                        </a>
                        <a
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                            target="_blank"
                            rel="noopener"
                            class="border-cream/20 text-cream/70 hover:border-gold hover:text-gold inline-flex size-12 items-center justify-center rounded-full border transition-colors"
                            aria-label="Share on Facebook"
                        >
                            <svg aria-hidden="true" class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                        </a>
                        <button
                            type="button"
                            data-copy-link
                            class="border-cream/20 text-cream/70 hover:border-gold hover:text-gold relative inline-flex size-12 items-center justify-center rounded-full border transition-colors"
                            aria-label="Copy link"
                        >
                            <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                            <span
                                class="copy-feedback bg-gold text-navy absolute -bottom-9 left-1/2 hidden -translate-x-1/2 rounded-full px-3 py-1 text-xs whitespace-nowrap"
                                role="status"
                                aria-live="polite"
                                aria-atomic="true"
                            >Copied!</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="editorial-detail-artwork overflow-hidden rounded-xl">
                @if ($post->cover_image_url)
                    <img
                        src="{{ $post->cover_image_url }}"
                        alt=""
                        width="1024"
                        height="768"
                        fetchpriority="high"
                        decoding="async"
                        class="aspect-[4/3] size-full object-cover"
                    />
                @else
                    <x-post-artwork :post="$post" class="aspect-[4/3] size-full object-cover" />
                @endif
            </div>
        </div>
    </section>

    <section class="dispatch-page-field bg-cream py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-[72ch] px-4 sm:px-6">
            <div id="toc-card" class="border-gold/35 mb-10 hidden border-y py-6" data-print-hidden>
                <h2 class="font-heading text-navy text-xl [font-weight:620]">In this story</h2>
                <nav id="toc-nav" class="mt-3 grid gap-x-6 sm:grid-cols-2"></nav>
            </div>

            <article id="article-body" class="editorial-reading-column">
                @if ($post->last_reviewed_at)
                    <p class="text-navy/60 mb-7 text-sm">
                        Last reviewed {{ $post->last_reviewed_at->format('F j, Y') }}
                    </p>
                @endif

                @if ($post->isReviewDue())
                    <div
                        role="note"
                        class="mb-8 rounded-xl border border-amber-700/25 bg-amber-100 px-5 py-4 text-sm/7 text-amber-950"
                    >
                        This post is due for editorial review. Disney policies and park operations can change, so
                        confirm current details with the official source before your visit.
                    </div>
                @endif

                <div class="blog-article-content prose-navy prose text-navy/80 max-w-none text-[1.0625rem] leading-[1.85] wrap-anywhere">
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
                    <div class="border-navy/12 mt-12 border-y py-6">
                        <p class="text-navy/65 text-sm/6">
                            Policies can change. Review the official source before your visit.
                        </p>
                        <a
                            href="{{ $post->source_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-purple hover:text-navy mt-2 inline-flex min-h-12 items-center font-semibold underline underline-offset-8"
                        >View official source</a>
                    </div>
                @endif
            </article>

            <section class="border-navy/12 mt-14 border-y py-8" aria-labelledby="about-author">
                <div class="flex items-start gap-5">
                    <span class="border-gold/35 text-gold-ink font-heading inline-flex size-14 shrink-0 items-center justify-center rounded-full border font-semibold">
                        {{ $post->author_initials }}
                    </span>
                    <div>
                        <h2 id="about-author" class="font-heading text-navy text-2xl [font-weight:620]">
                            {{ $post->author_name }}
                        </h2>
                        <p class="text-navy/68 mt-2 text-sm/7">
                            @if (Str::contains($post->author_name, '&') || (Str::contains($post->author_name, 'Jeffrey') && Str::contains($post->author_name, 'Cassie')))
                                The couple behind Mouse28, sharing practical park lessons and honest family experiences.
                            @elseif (Str::contains($post->author_name, 'Cassie'))
                                Mouse28 co-host, accessibility advocate, and the planner behind the family's park days.
                            @elseif (Str::contains($post->author_name, 'Jeffrey'))
                                Mouse28 co-host, theme park enthusiast, and candid chronicler of Disney family life.
                            @else
                                Disney park explorer, accessibility advocate, and parent.
                            @endif
                        </p>
                    </div>
                </div>
            </section>

            @if ($post->episode)
                <aside class="bg-navy text-cream mt-12 rounded-xl px-6 py-7 sm:px-8" data-print-hidden>
                    <h2 class="font-heading text-2xl [font-weight:620]">{{ $post->episode->title }}</h2>
                    <p class="text-cream/65 mt-2 text-sm/6">Hear the conversation behind this story.</p>
                    <a
                        href="{{ route('episodes.show', $post->episode) }}"
                        class="text-gold mt-4 inline-flex min-h-12 items-center font-semibold underline underline-offset-8"
                    >Listen to episode {{ $post->episode->episode_number }}</a>
                </aside>
            @endif

            <div class="mt-12 flex flex-wrap items-center gap-5" data-print-hidden>
                <span class="text-navy font-semibold">Share this story</span>
                <a
                    href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title . ' — Mouse28') }}"
                    target="_blank"
                    rel="noopener"
                    class="text-purple inline-flex min-h-12 items-center underline underline-offset-8"
                >Post on X</a>
                <a
                    href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}"
                    target="_blank"
                    rel="noopener"
                    class="text-purple inline-flex min-h-12 items-center underline underline-offset-8"
                >Share on Facebook</a>
            </div>
        </div>

        @if ($recentPosts->count())
            <section class="mx-auto mt-16 max-w-6xl px-4 sm:px-6" aria-labelledby="continue-reading" data-print-hidden>
                <h2 id="continue-reading" class="font-heading text-navy text-3xl [font-weight:640]">
                    Continue reading
                </h2>
                <div class="mt-7 grid gap-8 sm:grid-cols-2">
                    @foreach ($recentPosts->take(2) as $next)
                        <article class="group min-w-0">
                            <a
                                href="{{ route('blog.show', $next) }}"
                                aria-label="Read {{ $next->title }}"
                                class="block overflow-hidden rounded-xl"
                            >
                                <x-post-artwork
                                    :post="$next"
                                    :compact="true"
                                    class="aspect-[4/3] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.025]"
                                />
                            </a>
                            <p class="text-purple mt-4 text-sm font-semibold">{{ $next->category_label }}</p>
                            <h3 class="font-heading text-navy group-hover:text-purple mt-1 text-2xl [font-weight:600] tracking-[-0.015em] transition-colors">
                                <a href="{{ route('blog.show', $next) }}">{{ $next->title }}</a>
                            </h3>
                            <p class="text-navy/60 mt-2 text-sm">{{ $next->reading_time }} min read</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="mx-auto mt-16 max-w-3xl px-4 sm:px-6" data-print-hidden>
            <x-newsletter-card subtitle="Disney tips, park updates, and new posts" />
        </div>
    </section>

    <button
        type="button"
        id="back-to-top"
        class="bg-gold text-navy pointer-events-none invisible fixed right-4 bottom-20 z-50 inline-flex size-12 translate-y-2.5 cursor-pointer items-center justify-center rounded-full opacity-0 shadow-lg transition-[transform,opacity,box-shadow] duration-300 hover:-translate-y-0.5 sm:right-8 sm:bottom-8"
        aria-label="Back to top"
        aria-hidden="true"
        tabindex="-1"
    >
        <svg aria-hidden="true" class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" /></svg>
    </button>
</x-layouts.app>
