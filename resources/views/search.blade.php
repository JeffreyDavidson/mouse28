<x-layouts.app
    :title="($query ? 'Search results for “'.$query.'”' : 'Search').' | Mouse28'"
    description="Search Mouse28 blog posts, accessibility guides, and podcast episodes."
    og-title="Search Mouse28"
    robots="noindex,follow"
    :canonical="route('search')"
    :dispatch-layout="true"
>
    <section class="dispatch-page-hero from-navy via-navy-light to-purple relative overflow-hidden bg-linear-to-br py-16 md:py-24">
        <div class="dispatch-page-heading mx-auto max-w-[86rem] px-4 wrap-anywhere sm:px-6">
            <h1 class="font-heading text-4xl font-bold text-white md:text-6xl">Search</h1>
            <form
                action="{{ route('search') }}"
                method="GET"
                role="search"
                class="mx-auto mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row"
            >
                <label for="site-search" class="sr-only">Search posts, guides, and podcast episodes</label>
                <input
                    id="site-search"
                    type="search"
                    name="q"
                    value="{{ $query }}"
                    maxlength="100"
                    placeholder="Try “sensory breaks” or “DAS”"
                    @error('q') aria-invalid="true" aria-describedby="site-search-error" autofocus @enderror
                    class="text-navy placeholder:text-navy/65 focus:border-gold focus:ring-gold/40 min-h-12 min-w-0 flex-1 rounded-full border border-white/15 bg-white px-5 py-3 text-base shadow-lg focus:ring-2 focus:outline-none"
                />
                <button
                    type="submit"
                    class="bg-gold text-navy hover:bg-gold-light min-h-12 rounded-full px-7 py-3 font-semibold shadow-lg transition-colors"
                >
                    Search
                </button>
            </form>
            @error('q')
                <p id="site-search-error" role="alert" class="mt-3 text-sm text-red-200">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="dispatch-page-field bg-cream py-12 md:py-16">
        <div class="mx-auto max-w-5xl px-4 wrap-anywhere sm:px-6">
            @if ($query === '')
                <div class="border-navy/5 rounded-3xl border bg-white px-6 py-10 shadow-sm sm:px-10 sm:py-12">
                    <div class="text-center">
                        <h2 class="font-heading text-navy text-3xl font-bold">What can we help you find?</h2>
                        <p class="text-navy/65 mx-auto mt-3 max-w-xl text-base/relaxed">
                            Search our family stories, practical park guides, and podcast conversations.
                        </p>
                    </div>

                    <div class="mt-9 grid gap-4 text-left md:grid-cols-[1.2fr_0.8fr]" aria-label="Explore Mouse28">
                        <a
                            href="{{ route('blog.index') }}"
                            class="dispatch-interactive-card group from-navy to-navy-light relative flex min-h-64 flex-col justify-end overflow-hidden rounded-2xl bg-linear-to-br p-7 text-white md:row-span-2"
                        >
                            <span class="text-gold text-sm font-semibold">Start somewhere inspiring</span>
                            <h3 class="font-heading mt-2 max-w-md text-3xl font-bold">
                                Stories from park days that taught us something.
                            </h3>
                            <span class="text-gold mt-5 inline-flex min-h-12 items-center font-semibold">Browse the blog →</span>
                        </a>
                        <a
                            href="{{ route('guides.index') }}"
                            class="dispatch-interactive-card group border-navy/8 bg-cream hover:border-purple/25 flex min-h-30 items-center justify-between gap-5 rounded-2xl border p-6"
                        >
                            <span>
                                <strong class="font-heading text-navy block text-xl">Browse practical guides</strong>
                                <span class="text-navy/65 mt-1 block text-base/relaxed sm:text-sm/relaxed">Plan calmer, more accessible park days.</span>
                            </span>
                            <span class="text-purple text-xl" aria-hidden="true">→</span>
                        </a>
                        <a
                            href="{{ route('episodes.index') }}"
                            class="dispatch-interactive-card group border-navy/8 bg-cream hover:border-purple/25 flex min-h-30 items-center justify-between gap-5 rounded-2xl border p-6"
                        >
                            <span>
                                <strong class="font-heading text-navy block text-xl">Listen to the podcast</strong>
                                <span class="text-navy/65 mt-1 block text-base/relaxed sm:text-sm/relaxed">Hear the conversations behind the advice.</span>
                            </span>
                            <span class="text-purple text-xl" aria-hidden="true">→</span>
                        </a>
                    </div>
                </div>
            @elseif ($resultCount === 0)
                <div role="status" class="border-navy/5 rounded-3xl border bg-white px-6 py-14 text-center shadow-sm">
                    <h2 class="font-heading text-navy text-3xl font-bold">No results for “{{ $query }}”</h2>
                    <p class="text-navy/65 mx-auto mt-3 max-w-xl text-base/relaxed">
                        Try a broader phrase, or browse the blog, guides, and podcast directly.
                    </p>
                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a
                            href="{{ route('blog.index') }}"
                            class="bg-navy hover:bg-purple inline-flex min-h-12 items-center rounded-full px-5 py-3 font-semibold text-white"
                        >Browse blog</a>
                        <a
                            href="{{ route('guides.index') }}"
                            class="border-navy/10 text-navy hover:border-purple/30 inline-flex min-h-12 items-center rounded-full border bg-white px-5 py-3 font-semibold"
                        >Browse guides</a>
                        <a
                            href="{{ route('episodes.index') }}"
                            class="border-navy/10 text-navy hover:border-purple/30 inline-flex min-h-12 items-center rounded-full border bg-white px-5 py-3 font-semibold"
                        >Browse podcast</a>
                    </div>
                </div>
            @else
                <p role="status" class="text-navy/65 mb-10 text-center text-sm">
                    Showing {{ $resultCount }} {{ Str::plural('result', $resultCount) }} for “{{ $query }}”
                </p>

                <div class="space-y-12">
                    @if ($posts->isNotEmpty())
                        <section aria-labelledby="post-results-heading">
                            <div class="border-navy/10 mb-5 flex items-end justify-between gap-4 border-b pb-3">
                                <h2 id="post-results-heading" class="font-heading text-navy text-3xl font-bold">
                                    Blog posts
                                </h2>
                                <span class="text-navy/65 text-sm">{{ $posts->count() }} found</span>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($posts as $post)
                                    <a
                                        href="{{ route('blog.show', $post) }}"
                                        class="dispatch-interactive-card group border-navy/5 block rounded-2xl border bg-white p-6 shadow-sm"
                                    >
                                        <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">{{ $post->category_label }}</span>
                                        <h3 class="font-heading text-navy group-hover:text-purple mt-2 text-2xl font-bold transition-colors">
                                            {{ $post->title }}
                                        </h3>
                                        @if ($post->excerpt)
                                            <p class="text-navy/65 mt-3 text-sm/relaxed">
                                                {{ Str::limit($post->excerpt, 150) }}
                                            </p>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($guides->isNotEmpty())
                        <section aria-labelledby="guide-results-heading">
                            <div class="border-navy/10 mb-5 flex items-end justify-between gap-4 border-b pb-3">
                                <h2 id="guide-results-heading" class="font-heading text-navy text-3xl font-bold">
                                    Guides
                                </h2>
                                <span class="text-navy/65 text-sm">{{ $guides->count() }} found</span>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($guides as $guide)
                                    <a
                                        href="{{ route('guides.show', $guide) }}"
                                        class="dispatch-interactive-card group border-navy/5 block rounded-2xl border bg-white p-6 shadow-sm"
                                    >
                                        <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">{{ $guide->category_label }}</span>
                                        <h3 class="font-heading text-navy group-hover:text-purple mt-2 text-2xl font-bold transition-colors">
                                            {{ $guide->title }}
                                        </h3>
                                        @if ($guide->excerpt)
                                            <p class="text-navy/65 mt-3 text-sm/relaxed">
                                                {{ Str::limit($guide->excerpt, 150) }}
                                            </p>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($episodes->isNotEmpty())
                        <section aria-labelledby="episode-results-heading">
                            <div class="border-navy/10 mb-5 flex items-end justify-between gap-4 border-b pb-3">
                                <h2 id="episode-results-heading" class="font-heading text-navy text-3xl font-bold">
                                    Podcast episodes
                                </h2>
                                <span class="text-navy/65 text-sm">{{ $episodes->count() }} found</span>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($episodes as $episode)
                                    <a
                                        href="{{ route('episodes.show', $episode) }}"
                                        class="dispatch-interactive-card group border-navy/5 block rounded-2xl border bg-white p-6 shadow-sm"
                                    >
                                        <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">Episode {{ $episode->episode_number }}</span>
                                        <h3 class="font-heading text-navy group-hover:text-purple mt-2 text-2xl font-bold transition-colors">
                                            {{ $episode->title }}
                                        </h3>
                                        @if ($episode->description)
                                            <p class="text-navy/65 mt-3 text-sm/relaxed">
                                                {{ Str::limit($episode->description, 150) }}
                                            </p>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
