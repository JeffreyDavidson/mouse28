<x-layouts.app
    :title="($guide->meta_title ?: $guide->title).' — Mouse28'"
    :description="$guide->meta_description ?: Str::limit($guide->excerpt, 160)"
    :og-title="$guide->meta_title ?: $guide->title"
    :og-description="$guide->meta_description ?: Str::limit($guide->excerpt, 200)"
    og-type="article"
    :og-image="$guide->og_image_url ?: $guide->cover_image_url"
    :robots="($isPreview ?? false) ? 'noindex,nofollow' : 'index,follow'"
    :dispatch-layout="true"
>
    @unless ($isPreview ?? false)
        @push('head')
            <x-structured-data :data="\App\Support\StructuredData::forGuide($guide)" />
        @endpush
    @endunless

    @if ($isPreview ?? false)
        <div role="status" class="bg-gold text-navy px-4 py-3 text-center text-sm font-semibold">
            Preview mode — this page is only visible to administrators.
        </div>
    @endif
    <section class="dispatch-page-hero from-navy via-navy-light to-purple relative overflow-hidden bg-linear-to-br py-14 md:py-20">
        <x-guide-artwork
            :guide="$guide"
            loading="eager"
            fetchpriority="high"
            class="absolute inset-0 size-full object-cover opacity-40"
        />
        <div class="from-navy/95 via-navy/78 to-purple/65 absolute inset-0 bg-linear-to-r"></div>
        <div class="dispatch-page-heading relative z-10 mx-auto max-w-[86rem] px-4 wrap-anywhere sm:px-6">
            <a
                href="{{ route('guides.index') }}"
                class="hover:text-gold inline-flex min-h-12 items-center text-sm text-white/60"
            >← Back to Guides</a>
            <div class="mt-7 flex flex-wrap items-center gap-3 text-sm">
                <a
                    href="{{ route('guides.index', ['category' => $guide->category]) }}"
                    class="border-gold/30 bg-gold/15 text-gold hover:border-gold hover:bg-gold/25 inline-flex min-h-12 items-center rounded-full border px-4 py-2 font-bold tracking-wider uppercase transition-colors"
                >{{ $guide->category_label }}</a>
                <span class="text-white/60">{{ $guide->reading_time }} min read</span>
            </div>
            <h1 class="font-heading mt-5 max-w-4xl text-4xl/tight font-bold text-white md:text-6xl">
                {{ $guide->title }}
            </h1>
            @if ($guide->excerpt)
                <p class="mt-5 max-w-3xl text-lg/relaxed text-white/65">{{ $guide->excerpt }}</p>
            @endif
        </div>
    </section>

    <section class="dispatch-page-field bg-cream py-12 md:py-16">
        <div class="mx-auto max-w-4xl px-4 sm:px-6">
            <div class="border-gold/20 bg-gold/10 text-navy/65 mb-6 flex flex-col gap-2 rounded-2xl border px-5 py-4 text-sm sm:flex-row sm:items-center sm:justify-between">
                <span>Written by {{ $guide->author_name }}</span>
                @if ($guide->last_reviewed_at)
                    <span>Last reviewed {{ $guide->last_reviewed_at->format('F j, Y') }}</span>
                @endif
            </div>

            @if ($guide->isReviewDue())
                <div
                    role="note"
                    class="mb-6 rounded-2xl border border-amber-500/30 bg-amber-100 px-5 py-4 text-sm/relaxed text-amber-950"
                >
                    This guide is due for editorial review. Disney policies and park operations can change, so confirm
                    current details with official Disney information before your visit.
                </div>
            @endif

            <article class="dispatch-reader-sheet border-navy/5 shadow-navy/5 rounded-3xl border bg-white p-6 shadow-lg sm:p-10 md:p-14">
                <div class="blog-article-content prose-navy prose prose-lg text-navy/80 max-w-none text-[1.1rem] leading-[1.85] wrap-anywhere">
                    {!! Str::markdown($guide->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                </div>

                @if ($guide->source_url)
                    <div class="border-navy/10 mt-10 border-t pt-6">
                        <p class="text-navy/65 text-sm">
                            Policies can change. Review the official source before your visit.
                        </p>
                        <a
                            href="{{ $guide->source_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-purple hover:text-navy mt-2 inline-flex min-h-12 items-center font-semibold"
                        >View official source ↗</a>
                    </div>
                @endif
            </article>

            @if ($relatedGuides->isNotEmpty())
                <section class="mt-12" aria-labelledby="related-guides-heading" data-print-hidden>
                    <h2 id="related-guides-heading" class="font-heading text-navy text-3xl font-bold">
                        Related guides
                    </h2>
                    <div class="mt-6 grid gap-5 md:grid-cols-3">
                        @foreach ($relatedGuides as $relatedGuide)
                            <a
                                href="{{ route('guides.show', $relatedGuide) }}"
                                class="dispatch-interactive-card group border-navy/5 text-navy hover:text-purple overflow-hidden rounded-2xl border bg-white shadow-sm"
                            >
                                <x-guide-artwork
                                    :guide="$relatedGuide"
                                    class="h-28 w-full object-cover transition-transform duration-500 group-hover:scale-[1.035]"
                                />
                                <span class="font-heading block p-5 text-lg font-bold wrap-anywhere">{{ $relatedGuide->title }}</span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </section>
</x-layouts.app>
