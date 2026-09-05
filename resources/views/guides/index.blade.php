<x-layouts.app
    :title="$pageTitle"
    :description="$pageDescription"
    og-image="/images/guides/accessibility.webp"
    :canonical="$canonicalUrl"
    :dispatch-layout="true"
>
    <!--
        THESIS: The guide archive is a family-tested reference shelf, not a grid of interchangeable resource cards.
        OWN-WORLD: Navy cloth, cream paper, category artwork, gold rules, Besley headings, and quiet indexed navigation.
        STORY: Readers understand the scope, choose a planning topic, and scan consistent guide entries for the right next step.
        FIRST VIEWPORT: A concise masthead pairs the archive promise with the four guide subjects in a structured image field.
        FORM [seed: reference-shelf]: An editorial reference archive with equal-width entries and no dashboard widgets.
    -->
    <div data-guide-archive>
        <header class="bg-navy text-cream">
            <div class="mx-auto grid max-w-[86rem] gap-10 px-4 py-12 sm:px-6 sm:py-16 lg:grid-cols-[5fr_7fr] lg:items-center lg:gap-16 lg:py-20">
                <div class="max-w-2xl">
                    <h1 class="font-heading text-5xl/[1.04] [font-weight:680] tracking-[-0.03em] text-balance sm:text-6xl lg:text-7xl">
                        Park guides.
                    </h1>
                    <p class="text-cream/75 mt-5 max-w-[56ch] text-base/7 text-pretty sm:text-lg/8">
                        Practical guidance for accessibility, park strategy, food, and family days at Disney.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:gap-3" aria-hidden="true">
                    <img
                        src="/images/guides/accessibility.webp"
                        alt=""
                        width="960"
                        height="640"
                        fetchpriority="high"
                        class="aspect-[16/10] w-full rounded-xl object-cover"
                    />
                    <img
                        src="/images/guides/park-strategy.webp"
                        alt=""
                        width="960"
                        height="640"
                        class="aspect-[16/10] w-full rounded-xl object-cover"
                    />
                    <img
                        src="/images/guides/food-reviews.webp"
                        alt=""
                        width="960"
                        height="640"
                        class="aspect-[16/10] w-full rounded-xl object-cover"
                    />
                    <img
                        src="/images/guides/family-planning.webp"
                        alt=""
                        width="960"
                        height="640"
                        class="aspect-[16/10] w-full rounded-xl object-cover"
                    />
                </div>
            </div>
        </header>

        <main class="dispatch-page-field bg-cream">
            <div class="mx-auto max-w-[86rem] px-4 py-10 sm:px-6 sm:py-14 lg:py-18">
                <nav aria-label="Guide categories" class="border-navy/15 overflow-x-auto border-y">
                    <div class="flex min-w-max gap-7 sm:gap-9">
                        <a
                            href="{{ route('guides.index') }}"
                            @if (! $category) aria-current="page" @endif
                            class="{{ ! $category ? 'border-gold text-navy' : 'border-transparent text-navy/65 hover:text-purple' }} inline-flex min-h-14 items-center border-b-2 text-sm font-semibold transition-colors"
                        >All guides</a>
                        @foreach (\App\Models\Guide::CATEGORIES as $slug => $label)
                            <a
                                href="{{ route('guides.index', ['category' => $slug]) }}"
                                @if ($category === $slug) aria-current="page" @endif
                                class="{{ $category === $slug ? 'border-gold text-navy' : 'border-transparent text-navy/65 hover:text-purple' }} inline-flex min-h-14 items-center border-b-2 text-sm font-semibold transition-colors"
                            >{{ $label }}</a>
                        @endforeach
                    </div>
                </nav>

                @if ($guides->isNotEmpty())
                    <div class="mt-10 flex flex-wrap items-end justify-between gap-4 sm:mt-14">
                        <div>
                            <h2 class="font-heading text-4xl/[1.08] [font-weight:640] tracking-[-0.025em] text-balance sm:text-5xl">
                                {{ $category ? (\App\Models\Guide::CATEGORIES[$category] ?? 'Guides') : 'The guide shelf' }}
                            </h2>
                            <p class="text-navy/65 mt-3 max-w-xl text-base/7">
                                Choose the guide that matches the day you are planning.
                            </p>
                        </div>
                        <p class="text-navy/60 text-sm">
                            {{ $guides->total() }} {{ Str::plural('guide', $guides->total()) }}
                        </p>
                    </div>

                    <div
                        class="mt-10 grid gap-x-10 gap-y-14 md:grid-cols-2 lg:gap-x-14 lg:gap-y-18"
                        data-equal-width-guides
                    >
                        @foreach ($guides as $guide)
                            <article class="group min-w-0">
                                <a
                                    href="{{ route('guides.show', $guide) }}"
                                    aria-label="Read {{ $guide->title }}"
                                    class="block overflow-hidden rounded-xl"
                                >
                                    <x-guide-artwork
                                        :guide="$guide"
                                        class="aspect-[16/10] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.025]"
                                    />
                                </a>
                                <div class="pt-5 wrap-anywhere">
                                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm">
                                        <span class="text-purple font-semibold">{{ $guide->category_label }}</span>
                                        <span class="text-navy/60">{{ $guide->reading_time }} min read</span>
                                    </div>
                                    <h3 class="font-heading group-hover:text-purple mt-2 text-3xl/[1.15] [font-weight:610] tracking-[-0.02em] text-balance transition-colors">
                                        <a href="{{ route('guides.show', $guide) }}">{{ $guide->title }}</a>
                                    </h3>
                                    @if ($guide->excerpt)
                                        <p class="text-navy/70 mt-3 max-w-[62ch] text-base/7 text-pretty">
                                            {{ Str::limit($guide->excerpt, 180) }}
                                        </p>
                                    @endif
                                    <a
                                        href="{{ route('guides.show', $guide) }}"
                                        class="text-purple decoration-gold/70 hover:text-navy mt-5 inline-flex min-h-12 items-center font-semibold underline underline-offset-8 transition-colors"
                                    >Read the guide</a>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($guides->hasPages())
                        <div class="blog-pagination mt-14 flex justify-center">{{ $guides->links() }}</div>
                    @endif
                @else
                    <section class="border-navy/15 mt-12 grid gap-7 border-y py-12 sm:mt-16 sm:py-16 lg:grid-cols-[5fr_7fr] lg:gap-20">
                        <h2 class="font-heading max-w-[15ch] text-4xl/[1.1] [font-weight:640] tracking-[-0.025em] text-balance sm:text-5xl">
                            @if ($category)
                                No {{ \App\Models\Guide::CATEGORIES[$category] ?? 'matching' }} guides yet
                            @else
                                Guides are on the way
                            @endif
                        </h2>
                        <div class="max-w-[62ch]">
                            <p class="text-navy/70 text-base/7 text-pretty">
                                @if ($category)
                                    We have not published a guide for this topic yet. Try another category or browse the
                                    full shelf.
                                @else
                                    We are reviewing our park notes and official sources so every guide is useful,
                                    specific, and current.
                                @endif
                            </p>
                            <div class="mt-6 flex flex-wrap gap-x-6 gap-y-2">
                                @if ($category)
                                    <a
                                        href="{{ route('guides.index') }}"
                                        class="text-purple decoration-gold/70 inline-flex min-h-12 items-center font-semibold underline underline-offset-8"
                                    >View all guides</a>
                                @else
                                    <a
                                        href="{{ route('blog.index') }}"
                                        class="text-purple decoration-gold/70 inline-flex min-h-12 items-center font-semibold underline underline-offset-8"
                                    >Read our latest stories</a>
                                    <a
                                        href="{{ route('episodes.index') }}"
                                        class="text-purple decoration-gold/70 inline-flex min-h-12 items-center font-semibold underline underline-offset-8"
                                    >Hear the podcast</a>
                                @endif
                            </div>
                        </div>
                    </section>
                @endif
            </div>
        </main>
    </div>
</x-layouts.app>
