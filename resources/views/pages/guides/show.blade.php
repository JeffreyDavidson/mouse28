<x-layouts.app
    :title="($guide->meta_title ?: $guide->title).' | Mouse28'"
    :description="$guide->meta_description ?: Str::limit($guide->excerpt, 160)"
    :og-title="$guide->meta_title ?: $guide->title"
    :og-description="$guide->meta_description ?: Str::limit($guide->excerpt, 200)"
    og-type="article"
    :og-image="$guide->og_image_url ?: $guide->cover_image_url"
    :robots="($isPreview ?? false) ? 'noindex,nofollow' : 'index,follow'"
    :dispatch-layout="true"
>
    <!--
        THESIS: A Mouse28 guide should read like a trusted field reference, not a blog post placed inside a generic article card.
        OWN-WORLD: Navy cover cloth, category artwork, cream paper, gold rules, Besley headings, and a focused reading column.
        STORY: Readers identify the guide, verify its author and review context, read the advice, and continue to a related plan.
        FIRST VIEWPORT: The title and practical promise sit beside real guide artwork with category and reading time kept secondary.
        FORM [seed: field-reference]: A split cover opening followed by an editorial details rail and one calm reading surface.
    -->
    @unless ($isPreview ?? false)
        @push('head')
            <x-structured-data :data="\App\Support\StructuredData::forGuide($guide)" />
        @endpush
    @endunless

    @if ($isPreview ?? false)
        <x-preview-banner />
    @endif

    <div data-guide-detail>
        <header class="bg-navy text-cream">
            <div class="mx-auto grid max-w-[86rem] lg:grid-cols-[7fr_5fr]">
                <div class="flex flex-col justify-center px-4 py-12 wrap-anywhere sm:px-6 sm:py-16 lg:px-12 lg:py-20 xl:px-20">
                    <x-archive-back-link
                        href="{{ route('guides.index') }}"
                        label="Back to guides"
                        class="text-cream/70"
                    />
                    <h1 class="font-heading mt-6 max-w-[18ch] text-4xl/[1.06] [font-weight:660] tracking-[-0.03em] text-balance sm:text-5xl lg:text-6xl">
                        {{ $guide->title }}
                    </h1>
                    @if ($guide->excerpt)
                        <p class="text-cream/75 mt-6 max-w-[62ch] text-base/7 text-pretty sm:text-lg/8">
                            {{ $guide->excerpt }}
                        </p>
                    @endif
                    <div class="border-gold/40 text-cream/70 mt-8 flex flex-wrap gap-x-5 gap-y-2 border-t pt-5 text-sm">
                        <a
                            href="{{ route('guides.index', ['category' => $guide->category]) }}"
                            class="text-gold hover:text-gold-light inline-flex min-h-12 items-center font-semibold transition-colors"
                        >{{ $guide->category_label }}</a>
                        <span>{{ $guide->reading_time }} min read</span>
                    </div>
                </div>

                <x-guide-artwork
                    :guide="$guide"
                    loading="eager"
                    fetchpriority="high"
                    class="aspect-[4/3] size-full object-cover lg:aspect-auto lg:min-h-[36rem]"
                />
            </div>
        </header>

        <main class="dispatch-page-field bg-cream">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[4fr_8fr] lg:items-start lg:gap-16 lg:py-20">
                <aside class="lg:sticky lg:top-28" data-print-hidden>
                    <h2 class="font-heading text-2xl [font-weight:620] tracking-[-0.015em]">About this guide</h2>
                    <dl class="border-navy/15 divide-navy/15 mt-6 divide-y border-y text-sm">
                        <div class="py-4">
                            <dt class="text-navy/60">Written by</dt>
                            <dd class="mt-1 font-semibold">{{ $guide->author_name }}</dd>
                        </div>
                        @if ($guide->last_reviewed_at)
                            <div class="py-4">
                                <dt class="text-navy/60">Last reviewed</dt>
                                <dd class="mt-1 font-semibold">{{ $guide->last_reviewed_at->format('F j, Y') }}</dd>
                            </div>
                        @endif
                        <div class="py-4">
                            <dt class="text-navy/60">Topic</dt>
                            <dd class="mt-1 font-semibold">{{ $guide->category_label }}</dd>
                        </div>
                    </dl>

                    @if ($guide->source_url)
                        <x-source-attribution
                            class="mt-7 border-0 py-0"
                            :url="$guide->source_url"
                            message="Policies can change. Check the official source before your visit."
                        />
                    @endif
                </aside>

                <div>
                    @if ($guide->isReviewDue())
                        <x-review-due-notice
                            class="border-gold/55 bg-gold/10 text-navy mb-8"
                            message="This guide is due for editorial review. Disney policies and park operations can change, so confirm current details with official Disney information before your visit."
                        />
                    @endif

                    <article class="dispatch-reader-sheet relative rounded-xl bg-white p-6 shadow-[0_1.75rem_4rem_rgb(26_16_64/0.12)] sm:p-10 lg:p-14">
                        <div class="guide-reading-column blog-article-content prose-navy prose prose-lg text-navy/80 max-w-none text-[1.1rem] leading-[1.85] wrap-anywhere">
                            {!! Str::markdown($guide->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]) !!}
                        </div>
                    </article>
                </div>
            </div>

            @if ($relatedGuides->isNotEmpty())
                <x-related-content-section
                    title="Keep planning"
                    label-id="related-guides-heading"
                    class="border-navy/10 border-t border-b-0 py-14 sm:py-18 lg:py-22"
                >
                    @foreach ($relatedGuides as $relatedGuide)
                        <x-guide-card :guide="$relatedGuide" variant="related" />
                    @endforeach
                </x-related-content-section>
            @endif
        </main>
    </div>
</x-layouts.app>
