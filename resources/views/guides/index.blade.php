<x-layouts.app
    :title="$pageTitle"
    :description="$pageDescription"
    og-image="/images/hero-family.jpg"
    :canonical="$canonicalUrl"
>
    <section class="from-navy via-navy-light to-purple relative overflow-hidden bg-linear-to-br py-16 md:py-24">
        <div class="mx-auto max-w-6xl px-4 text-center sm:px-6">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase">Plan With Confidence</span>
            <h1 class="font-heading mt-3 text-4xl font-bold text-white md:text-6xl">Park Guides</h1>
            <p class="mx-auto mt-5 max-w-2xl text-lg/relaxed text-white/65">
                Clear, experience-based guidance for accessible and enjoyable Disney park days.
            </p>
        </div>
    </section>

    <section class="bg-cream py-12 md:py-16">
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
                        <article class="border-navy/5 flex flex-col overflow-hidden rounded-3xl border bg-white shadow-sm transition-[transform,box-shadow] hover:-translate-y-1 hover:shadow-xl">
                            @if ($guide->cover_image_url)
                                <img src="{{ $guide->cover_image_url }}" alt="" class="h-52 w-full object-cover" />
                            @else
                                <div
                                    class="from-purple/15 to-gold/15 flex h-40 items-center justify-center bg-linear-to-br"
                                    aria-hidden="true"
                                >
                                    <span class="text-5xl">✦</span>
                                </div>
                            @endif
                            <div class="flex flex-1 flex-col p-6">
                                <span class="text-gold text-xs font-bold tracking-widest uppercase">{{ $guide->category_label }}</span>
                                <h2 class="font-heading text-navy mt-3 text-2xl font-bold">
                                    <a
                                        href="{{ route('guides.show', $guide) }}"
                                        class="hover:text-purple inline-flex min-h-12 items-center"
                                    >{{ $guide->title }}</a>
                                </h2>
                                @if ($guide->excerpt)
                                    <p class="text-navy/55 mt-3 flex-1 text-base/relaxed">{{ $guide->excerpt }}</p>
                                @endif
                                <div class="border-navy/5 mt-5 flex items-center justify-between border-t pt-4 text-sm">
                                    <span class="text-navy/40">{{ $guide->reading_time }} min read</span>
                                    <a
                                        href="{{ route('guides.show', $guide) }}"
                                        class="text-purple inline-flex min-h-12 items-center font-semibold"
                                    >Read guide →</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($guides->hasPages())
                    <div class="mt-12">{{ $guides->links() }}</div>
                @endif
            @else
                <div class="border-navy/5 rounded-3xl border bg-white px-6 py-16 text-center shadow-sm">
                    <h2 class="font-heading text-navy text-3xl font-bold">Guides are on the way</h2>
                    <p class="text-navy/55 mx-auto mt-3 max-w-xl text-base/relaxed">
                        We are reviewing our park notes and sources so every published guide is useful and current.
                    </p>
                </div>
            @endif
        </div>
    </section>
</x-layouts.app>
