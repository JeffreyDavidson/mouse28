@extends('layouts.error')

@section('title', 'Page Not Found — Mouse28')
@section('meta_description', 'The page you requested could not be found. Search Mouse28 or continue exploring our Disney park stories, guides, and podcast.')
@section('og_title', 'Page Not Found — Mouse28')
@section('og_description', 'Search Mouse28 or continue exploring our Disney park stories, accessibility guides, and podcast.')
@section('robots', 'noindex,nofollow')

@section('content')
    <section class="relative isolate flex min-h-[70vh] items-center overflow-hidden bg-linear-to-br from-navy via-navy-light to-purple py-16 text-white">
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <span class="absolute top-[12%] left-[12%] text-2xl text-gold/25">✦</span>
            <span class="absolute top-[28%] right-[16%] text-base text-gold/20">✦</span>
            <span class="absolute bottom-[18%] left-[22%] text-lg text-purple-light/30">✦</span>
            <div class="absolute top-1/2 left-1/2 size-[34rem] -translate-1/2 rounded-full bg-gold/5 blur-3xl"></div>
        </div>

        <div class="relative mx-auto w-full max-w-4xl px-4 text-center sm:px-6">
            <p class="font-heading text-8xl font-bold text-gold/20 sm:text-9xl" aria-hidden="true">404</p>
            <p class="mt-3 text-sm font-semibold tracking-[0.18em] text-gold uppercase">Page not found</p>
            <h1 class="mt-4 font-heading text-4xl/tight font-bold sm:text-5xl md:text-6xl">That page wandered off</h1>
            <p class="mx-auto mt-5 max-w-2xl text-base/relaxed text-white/65 sm:text-lg/relaxed">
                The address may have changed, or the page may no longer be available. Search Mouse28 or choose a place to keep exploring.
            </p>

            <form action="{{ route('search') }}" method="GET" role="search" class="mx-auto mt-8 flex max-w-xl flex-col gap-3 sm:flex-row">
                <label for="not-found-search" class="sr-only">Search Mouse28</label>
                <input id="not-found-search" type="search" name="q" maxlength="100" placeholder="Search posts, guides, and episodes" class="min-h-12 min-w-0 flex-1 rounded-full border border-white/15 bg-white px-5 py-3 text-base text-navy shadow-lg placeholder:text-navy/35 focus:border-gold focus:ring-2 focus:ring-gold/40 focus:outline-none">
                <button type="submit" class="min-h-12 rounded-full bg-gold px-7 py-3 font-semibold text-navy shadow-lg transition-colors hover:bg-gold-light">Search</button>
            </form>

            <div class="mt-8 flex flex-wrap justify-center gap-3">
                <a href="{{ route('home') }}" class="inline-flex min-h-12 items-center rounded-full bg-white px-6 py-3 font-semibold text-navy transition-colors hover:bg-cream">Go home</a>
                <a href="{{ route('blog.index') }}" class="inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors hover:border-gold/50 hover:text-gold">Browse blog</a>
                <a href="{{ route('guides.index') }}" class="inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors hover:border-gold/50 hover:text-gold">Explore guides</a>
                <a href="{{ route('episodes.index') }}" class="inline-flex min-h-12 items-center rounded-full border border-white/15 bg-white/8 px-6 py-3 font-semibold text-white transition-colors hover:border-gold/50 hover:text-gold">Listen to podcast</a>
            </div>
        </div>
    </section>
@endsection
