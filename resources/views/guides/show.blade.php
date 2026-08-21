@extends('layouts.app')

@section('title', ($guide->meta_title ?: $guide->title).' — Mouse28')
@section('meta_description', $guide->meta_description ?: Str::limit($guide->excerpt, 160))
@section('og_title', $guide->meta_title ?: $guide->title)
@section('og_description', $guide->meta_description ?: Str::limit($guide->excerpt, 200))
@section('og_type', 'article')
@if ($guide->og_image_url ?: $guide->cover_image_url) @section('og_image', $guide->og_image_url ?: $guide->cover_image_url) @endif

@section('content')
    <section class="relative overflow-hidden bg-linear-to-br from-navy via-navy-light to-purple py-14 md:py-20">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            <a href="{{ route('guides.index') }}" class="inline-flex min-h-12 items-center text-sm text-white/60 hover:text-gold">← Back to Guides</a>
            <div class="mt-7 flex flex-wrap items-center gap-3 text-sm">
                <span class="rounded-full border border-gold/30 bg-gold/15 px-4 py-2 font-bold tracking-wider text-gold uppercase">{{ $guide->category_label }}</span>
                <span class="text-white/45">{{ $guide->reading_time }} min read</span>
            </div>
            <h1 class="mt-5 max-w-4xl font-heading text-4xl/tight font-bold text-white md:text-6xl">{{ $guide->title }}</h1>
            @if ($guide->excerpt)<p class="mt-5 max-w-3xl text-lg/relaxed text-white/65">{{ $guide->excerpt }}</p>@endif
        </div>
    </section>

    <section class="bg-cream py-12 md:py-16">
        <div class="mx-auto max-w-4xl px-4 sm:px-6">
            <div class="mb-6 flex flex-col gap-2 rounded-2xl border border-gold/20 bg-gold/10 px-5 py-4 text-sm text-navy/65 sm:flex-row sm:items-center sm:justify-between">
                <span>Written by {{ $guide->author_name }}</span>
                @if ($guide->last_reviewed_at)<span>Last reviewed {{ $guide->last_reviewed_at->format('F j, Y') }}</span>@endif
            </div>

            <article class="rounded-3xl border border-navy/5 bg-white p-6 shadow-lg shadow-navy/5 sm:p-10 md:p-14">
                <div class="blog-article-content prose-navy prose prose-lg max-w-none text-[1.1rem] leading-[1.85] text-navy/80">
                    {!! Str::markdown($guide->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                </div>

                @if ($guide->source_url)
                    <div class="mt-10 border-t border-navy/10 pt-6">
                        <p class="text-sm text-navy/55">Policies can change. Review the official source before your visit.</p>
                        <a href="{{ $guide->source_url }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex min-h-12 items-center font-semibold text-purple hover:text-navy">View official source ↗</a>
                    </div>
                @endif
            </article>

            @if ($relatedGuides->isNotEmpty())
                <section class="mt-12" aria-labelledby="related-guides-heading">
                    <h2 id="related-guides-heading" class="font-heading text-3xl font-bold text-navy">Related guides</h2>
                    <div class="mt-6 grid gap-5 md:grid-cols-3">
                        @foreach ($relatedGuides as $relatedGuide)
                            <a href="{{ route('guides.show', $relatedGuide) }}" class="rounded-2xl border border-navy/5 bg-white p-5 font-heading text-lg font-bold text-navy shadow-sm hover:text-purple">{{ $relatedGuide->title }}</a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </section>
@endsection
