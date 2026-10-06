<x-layouts.app
    :title="$issue->title.' | Mouse28'"
    :description="Str::limit($issue->excerpt ?: strip_tags(Str::markdown($issue->content, ['html_input' => 'strip'])), 160)"
    og-type="article"
    og-image="/images/hero-family.jpg"
    :canonical="route('newsletter.issue', $issue)"
    :robots="($isPreview ?? false) ? 'noindex,nofollow' : 'index,follow'"
    :dispatch-layout="true"
>
    @push('head')
        <link
            rel="alternate"
            type="application/rss+xml"
            title="Mouse28 Newsletter"
            href="{{ route('newsletter.rss') }}"
        />
    @endpush

    <!--
        THESIS: A newsletter issue should read like a letter from the family, not a web page about one.
        OWN-WORLD: Navy cloth masthead, cream paper, Besley headlines, gold rules, and one generous reading column.
        STORY: Establish the issue, read without interruption, then return to the archive or subscribe.
        FIRST VIEWPORT: The title, date and summary in a plain masthead.
        FORM [seed: letter-sheet]: Masthead followed by one uninterrupted editorial column.
    -->
    @if ($isPreview ?? false)
        <x-preview-banner />
    @endif

    <header class="bg-navy text-cream px-4 py-10 sm:px-6 sm:py-14 lg:py-16">
        <div class="mx-auto max-w-3xl wrap-anywhere">
            <x-back-link href="{{ route('newsletter.index') }}" label="Back to Newsletter" />
            <p class="text-cream/65 mt-5 text-sm">
                <x-display-date :date="$issue->published_at" fallback="Not scheduled" />
            </p>
            <h1 class="font-heading mt-4 text-4xl/tight [font-weight:680] tracking-[-0.03em] text-balance sm:text-5xl/tight">
                {{ $issue->title }}
            </h1>
            @if ($issue->excerpt)
                <p class="text-cream/72 mt-6 text-lg/8 text-pretty">{{ $issue->excerpt }}</p>
            @endif
        </div>
    </header>

    <section class="dispatch-page-field bg-cream py-12 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-[72ch] px-4 sm:px-6">
            <article class="editorial-reading-column">
                <div class="blog-article-content prose-navy prose text-navy/80 max-w-none text-[1.0625rem] leading-[1.85] wrap-anywhere">
                    {!!
                        Str::markdown($issue->content, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                            'renderer' => [
                                'soft_break' => "<br />\n",
                            ],
                        ])
                    !!}
                </div>
            </article>

            <div class="border-gold/35 mt-12 flex flex-wrap items-center justify-between gap-4 border-t pt-8">
                <a
                    href="{{ route('newsletter.index') }}"
                    class="text-purple hover:text-navy inline-flex min-h-12 items-center font-semibold underline decoration-current/35 underline-offset-8 transition-colors"
                ><span aria-hidden="true" class="mr-1">←</span> Back to the archive</a>
                <a
                    href="#newsletter"
                    class="text-purple hover:text-navy inline-flex min-h-12 items-center font-semibold underline decoration-current/35 underline-offset-8 transition-colors"
                >Get letters by email</a>
            </div>
        </div>
    </section>
</x-layouts.app>
