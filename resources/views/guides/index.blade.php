<x-layouts.app
    :title="$pageTitle"
    :description="$pageDescription"
    og-image="/images/hero-family.jpg"
    :canonical="$canonicalUrl"
    :dispatch-layout="true"
>
    <section class="dispatch-page-hero from-navy via-navy-light to-purple relative overflow-hidden bg-linear-to-br py-16 md:py-24">
        <div class="dispatch-page-heading mx-auto max-w-[86rem] px-4 sm:px-6">
            <h1 class="font-heading text-4xl font-bold text-white md:text-6xl">Park Guides</h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg/relaxed text-white/65">
                Clear, experience-based guidance for accessible and enjoyable Disney park days.
            </p>
        </div>
    </section>

    <section class="dispatch-page-field bg-cream py-12 md:py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <nav aria-label="Guide categories" class="mb-10 flex flex-wrap justify-center gap-3">
                <a
                    href="{{ route('guides.index') }}"
                    @if (! $category) aria-current="page" @endif
                    class="inline-flex min-h-12 items-center rounded-full px-5 py-3 text-sm font-semibold {{ ! $category ? 'bg-navy text-white' : 'border border-navy/10 bg-white text-navy hover:border-purple/30' }}"
                >All guides</a>
                @foreach (\App\Models\Guide::CATEGORIES as $slug => $label)
                    <a
                        href="{{ route('guides.index', ['category' => $slug]) }}"
                        @if ($category === $slug) aria-current="page" @endif
                        class="inline-flex min-h-12 items-center rounded-full px-5 py-3 text-sm font-semibold {{ $category === $slug ? 'bg-navy text-white' : 'border border-navy/10 bg-white text-navy hover:border-purple/30' }}"
                    >{{ $label }}</a>
                @endforeach
            </nav>

            @if ($guides->isNotEmpty())
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($guides as $guide)
                        <article class="min-w-0">
                            <a
                                href="{{ route('guides.show', $guide) }}"
                                class="dispatch-interactive-card group border-navy/5 flex h-full min-w-0 flex-col overflow-hidden rounded-2xl border bg-white shadow-sm"
                            >
                                <x-guide-artwork
                                    :guide="$guide"
                                    class="h-52 w-full object-cover transition-transform duration-500 group-hover:scale-[1.025]"
                                />
                                <div class="flex min-w-0 flex-1 flex-col p-6 wrap-anywhere">
                                    <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">{{ $guide->category_label }}</span>
                                    <h2 class="font-heading text-navy group-hover:text-purple mt-3 text-2xl font-bold transition-colors">
                                        {{ $guide->title }}
                                    </h2>
                                    @if ($guide->excerpt)
                                        <p class="text-navy/65 mt-3 flex-1 text-base/relaxed">{{ $guide->excerpt }}</p>
                                    @endif
                                    <div class="border-navy/5 mt-5 flex items-center justify-between border-t pt-4 text-sm">
                                        <span class="text-navy/65">{{ $guide->reading_time }} min read</span>
                                        <span class="text-purple inline-flex min-h-12 items-center font-semibold">Read guide →</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if ($guides->hasPages())
                    <div class="blog-pagination mt-12 flex justify-center">{{ $guides->links() }}</div>
                @endif
            @else
                <div class="border-navy/5 rounded-3xl border bg-white px-6 py-16 text-center shadow-sm">
                    @if ($category)
                        <h2 class="font-heading text-navy text-3xl font-bold">
                            No {{ \App\Models\Guide::CATEGORIES[$category] ?? 'matching' }} guides yet
                        </h2>
                        <p class="text-navy/65 mx-auto mt-3 max-w-xl text-base/relaxed">
                            Try another category or browse all of our guides.
                        </p>
                        <a
                            href="{{ route('guides.index') }}"
                            class="bg-navy hover:bg-purple mt-7 inline-flex min-h-12 items-center rounded-full px-5 py-3 font-semibold text-white"
                        >View all guides</a>
                    @else
                        <h2 class="font-heading text-navy text-3xl font-bold">Guides are on the way</h2>
                        <p class="text-navy/65 mx-auto mt-3 max-w-xl text-base/relaxed">
                            We are reviewing our park notes and sources so every published guide is useful and current.
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
