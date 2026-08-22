@extends('layouts.app')

@section('title', ($query ? 'Search results for “'.$query.'”' : 'Search').' — Mouse28')
@section('meta_description', 'Search Mouse28 blog posts, accessibility guides, and podcast episodes.')
@section('robots', 'noindex,follow')

@section('content')
    <section class="bg-linear-to-br from-navy via-navy-light to-purple py-14 md:py-20">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6">
            <span class="text-sm font-semibold tracking-[0.15em] text-gold uppercase">Explore Mouse28</span>
            <h1 class="mt-3 font-heading text-4xl font-bold text-white md:text-6xl">Search</h1>
            <form action="{{ route('search') }}" method="GET" role="search" class="mx-auto mt-8 flex max-w-2xl flex-col gap-3 sm:flex-row">
                <label for="site-search" class="sr-only">Search posts, guides, and podcast episodes</label>
                <input id="site-search" type="search" name="q" value="{{ $query }}" maxlength="100" placeholder="Try “sensory breaks” or “DAS”" class="min-h-12 flex-1 rounded-full border border-white/15 bg-white px-5 py-3 text-base text-navy shadow-lg placeholder:text-navy/35 focus:border-gold focus:ring-2 focus:ring-gold/40 focus:outline-none">
                <button type="submit" class="min-h-12 rounded-full bg-gold px-7 py-3 font-semibold text-navy shadow-lg transition-colors hover:bg-gold-light">Search</button>
            </form>
            @error('q')
                <p role="alert" class="mt-3 text-sm text-red-200">{{ $message }}</p>
            @enderror
        </div>
    </section>

    <section class="bg-cream py-12 md:py-16">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            @if ($query === '')
                <div class="rounded-3xl border border-navy/5 bg-white px-6 py-14 text-center shadow-sm">
                    <h2 class="font-heading text-3xl font-bold text-navy">What can we help you find?</h2>
                    <p class="mx-auto mt-3 max-w-xl text-base/relaxed text-navy/55">Search our family stories, practical park guides, and podcast conversations.</p>
                </div>
            @elseif ($resultCount === 0)
                <div role="status" class="rounded-3xl border border-navy/5 bg-white px-6 py-14 text-center shadow-sm">
                    <h2 class="font-heading text-3xl font-bold text-navy">No results for “{{ $query }}”</h2>
                    <p class="mx-auto mt-3 max-w-xl text-base/relaxed text-navy/55">Try a broader phrase, or browse the blog, guides, and podcast directly.</p>
                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('blog.index') }}" class="inline-flex min-h-12 items-center rounded-full bg-navy px-5 py-3 font-semibold text-white hover:bg-purple">Browse blog</a>
                        <a href="{{ route('guides.index') }}" class="inline-flex min-h-12 items-center rounded-full border border-navy/10 bg-white px-5 py-3 font-semibold text-navy hover:border-purple/30">Browse guides</a>
                        <a href="{{ route('episodes.index') }}" class="inline-flex min-h-12 items-center rounded-full border border-navy/10 bg-white px-5 py-3 font-semibold text-navy hover:border-purple/30">Browse podcast</a>
                    </div>
                </div>
            @else
                <p role="status" class="mb-10 text-center text-sm text-navy/55">Showing {{ $resultCount }} {{ Str::plural('result', $resultCount) }} for “{{ $query }}”</p>

                <div class="space-y-12">
                    @if ($posts->isNotEmpty())
                        <section aria-labelledby="post-results-heading">
                            <div class="mb-5 flex items-end justify-between gap-4 border-b border-navy/10 pb-3">
                                <h2 id="post-results-heading" class="font-heading text-3xl font-bold text-navy">Blog posts</h2>
                                <span class="text-sm text-navy/40">{{ $posts->count() }} found</span>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($posts as $post)
                                    <article class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                                        <span class="text-xs font-bold tracking-widest text-gold uppercase">{{ $post->category_label }}</span>
                                        <h3 class="mt-2 font-heading text-2xl font-bold text-navy"><a href="{{ route('blog.show', $post) }}" class="hover:text-purple">{{ $post->title }}</a></h3>
                                        @if ($post->excerpt)<p class="mt-3 text-sm/relaxed text-navy/55">{{ Str::limit($post->excerpt, 150) }}</p>@endif
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($guides->isNotEmpty())
                        <section aria-labelledby="guide-results-heading">
                            <div class="mb-5 flex items-end justify-between gap-4 border-b border-navy/10 pb-3">
                                <h2 id="guide-results-heading" class="font-heading text-3xl font-bold text-navy">Guides</h2>
                                <span class="text-sm text-navy/40">{{ $guides->count() }} found</span>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($guides as $guide)
                                    <article class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                                        <span class="text-xs font-bold tracking-widest text-gold uppercase">{{ $guide->category_label }}</span>
                                        <h3 class="mt-2 font-heading text-2xl font-bold text-navy"><a href="{{ route('guides.show', $guide) }}" class="hover:text-purple">{{ $guide->title }}</a></h3>
                                        @if ($guide->excerpt)<p class="mt-3 text-sm/relaxed text-navy/55">{{ Str::limit($guide->excerpt, 150) }}</p>@endif
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    @if ($episodes->isNotEmpty())
                        <section aria-labelledby="episode-results-heading">
                            <div class="mb-5 flex items-end justify-between gap-4 border-b border-navy/10 pb-3">
                                <h2 id="episode-results-heading" class="font-heading text-3xl font-bold text-navy">Podcast episodes</h2>
                                <span class="text-sm text-navy/40">{{ $episodes->count() }} found</span>
                            </div>
                            <div class="grid gap-4 md:grid-cols-2">
                                @foreach ($episodes as $episode)
                                    <article class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                                        <span class="text-xs font-bold tracking-widest text-gold uppercase">Episode {{ $episode->episode_number }}</span>
                                        <h3 class="mt-2 font-heading text-2xl font-bold text-navy"><a href="{{ route('episodes.show', $episode) }}" class="hover:text-purple">{{ $episode->title }}</a></h3>
                                        @if ($episode->description)<p class="mt-3 text-sm/relaxed text-navy/55">{{ Str::limit($episode->description, 150) }}</p>@endif
                                    </article>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
