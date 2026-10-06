<x-layouts.app
    :title="$pageTitle"
    :description="$pageDescription"
    og-image="/images/hero-family.jpg"
    :canonical="$canonicalUrl"
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
        THESIS: The newsletter archive is a stack of letters from the family, not a feed of cards.
        OWN-WORLD: Navy cloth masthead, cream paper, Besley headlines, gold rules, and quiet dated entries.
        STORY: Readers see what a letter is, subscribe by email or feed, then read past issues at their own pace.
        FIRST VIEWPORT: A plain masthead with the promise and both ways to subscribe.
        FORM [seed: letter-archive]: One calm column of dated entries under a navy masthead.
    -->
    <header class="bg-navy text-cream px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
        <div class="mx-auto max-w-[86rem]">
            <p class="text-gold text-sm font-semibold tracking-wider uppercase">Newsletter</p>
            <h1 class="font-heading mt-3 max-w-3xl text-4xl/tight [font-weight:680] tracking-[-0.03em] text-balance sm:text-5xl/tight lg:text-6xl/tight">
                Letters from the Mouse28 family
            </h1>
            <p class="text-cream/72 mt-6 max-w-2xl text-lg/8 text-pretty">
                Park accessibility notes, family stories, and practical planning from Jeffrey and Cassie, sent only when
                there is something worth sharing.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-x-8 gap-y-2">
                <a
                    href="#newsletter"
                    class="text-gold hover:text-cream inline-flex min-h-12 items-center font-semibold underline decoration-current/35 underline-offset-8 transition-colors"
                >Subscribe by email</a>
                <a
                    href="{{ route('newsletter.rss') }}"
                    class="text-gold hover:text-cream inline-flex min-h-12 items-center font-semibold underline decoration-current/35 underline-offset-8 transition-colors"
                >Subscribe via RSS</a>
            </div>
        </div>
    </header>

    <section
        class="dispatch-page-field bg-cream text-navy py-12 sm:py-16 lg:py-20"
        aria-labelledby="newsletter-archive-heading"
    >
        <div class="mx-auto max-w-3xl px-4 sm:px-6">
            <h2 id="newsletter-archive-heading" class="font-heading text-navy text-2xl [font-weight:640] sm:text-3xl">
                Archive
                @unless ($issues->onFirstPage())
                    <span class="text-navy/60 text-lg font-normal">— Page {{ $issues->currentPage() }}</span>
                @endunless
            </h2>

            @forelse ($issues as $issue)
                <article class="border-gold/35 mt-8 border-t pt-8">
                    <x-display-date :date="$issue->published_at" class="text-navy/60 text-sm" />
                    <h3 class="font-heading mt-2 text-2xl/tight [font-weight:620] text-balance sm:text-3xl/tight">
                        <a
                            href="{{ route('newsletter.issue', $issue) }}"
                            class="hover:text-purple focus-visible:outline-purple rounded-sm transition-colors focus-visible:outline-2 focus-visible:outline-offset-4"
                        >{{ $issue->title }}</a>
                    </h3>
                    @if ($issue->excerpt)
                        <p class="text-navy/75 mt-3 text-lg/8 text-pretty">{{ $issue->excerpt }}</p>
                    @endif
                    <a
                        href="{{ route('newsletter.issue', $issue) }}"
                        class="text-purple hover:text-navy mt-4 inline-flex min-h-12 items-center font-semibold underline decoration-current/35 underline-offset-8 transition-colors"
                        aria-label="Read {{ $issue->title }}"
                    >Read issue <span aria-hidden="true" class="ml-1">→</span></a>
                </article>
            @empty
                <div class="border-gold/40 mt-8 rounded-xl border border-dashed p-10 text-center">
                    <h3 class="font-heading text-xl [font-weight:620]">No issues yet</h3>
                    <p class="text-navy/70 mt-2">New newsletter issues will appear here once they are published.</p>
                </div>
            @endforelse

            @if ($issues->hasPages())
                <div class="blog-pagination mt-14 flex justify-center">{{ $issues->links() }}</div>
            @endif
        </div>
    </section>
</x-layouts.app>
