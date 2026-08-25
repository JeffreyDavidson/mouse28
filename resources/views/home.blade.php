<x-layouts.app
    title="Mouse28 — Disney Parks Through Different Eyes"
    description="Accessibility tips, sensory-friendly park planning, family experiences, and the Mouse28 podcast from Jeffrey and Cassie Davidson."
    og-description="Accessibility tips, sensory-friendly Disney park planning, and honest family experiences from Jeffrey and Cassie Davidson."
    og-image="/images/hero-family.jpg"
    :canonical="route('home')"
>
    {{-- Hero Section — Split Identity --}}
    <section class="hero-split">
        {{-- Left: Text --}}
        <div class="hero-split-text">
            <div class="pointer-events-none absolute right-[-20%] bottom-[-30%] size-[400px] bg-[radial-gradient(circle,rgb(212_168_67/6%)_0%,transparent_60%)]"></div>
            <span class="sparkle text-gold/25 absolute top-[15%] left-[10%] text-[10px]">✦</span>
            <span class="sparkle-delay text-gold/15 absolute right-[15%] bottom-[20%] text-sm">✧</span>

            <div class="relative z-10 ml-auto max-w-lg">
                <div class="border-gold/30 mb-6 inline-flex items-center gap-2 rounded-full border px-4 py-[0.35rem]">
                    <span class="bg-gold size-1.5 rounded-full"></span>
                    <span class="font-body text-gold text-[0.7rem] font-semibold tracking-[0.15em] uppercase">Autism Family · Disney Every Week</span>
                </div>

                <h1 class="font-heading mb-5 text-[clamp(2.25rem,4vw,3.5rem)] leading-[1.08] font-bold text-white">
                    Disney Parks<br />Through<br />
                    <span class="text-gold">Different Eyes</span>
                </h1>

                <p class="font-body text-cream/50 mb-8 text-base/7">
                    Accessibility tips, sensory-friendly recommendations, and real stories from a family who visits
                    Disney every single week with our autistic daughter.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    <a
                        href="{{ route('blog.index') }}"
                        class="cta-primary bg-gold font-body text-navy shadow-gold/20 hover:bg-gold-light hover:shadow-gold/50 inline-flex min-h-12 items-center rounded-full px-7 py-3.5 text-base font-semibold shadow-lg transition-[transform,background-color,box-shadow] duration-300 hover:-translate-y-1 hover:scale-105 sm:text-sm"
                    >
                        Read Our Blog
                    </a>
                    <a
                        href="{{ route('episodes.index') }}"
                        class="font-body text-cream/45 hover:text-gold inline-flex min-h-12 items-center gap-2 text-base font-medium transition-colors duration-200 sm:text-sm"
                    >
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                        Listen to the podcast
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Photo --}}
        <div class="hero-split-photo">
            <img
                src="/images/hero-family.jpg"
                alt="Jeffrey and Cassie Davidson on Kilimanjaro Safaris at Disney's Animal Kingdom"
                width="2048"
                height="2048"
                fetchpriority="high"
            />
            <div class="from-navy/30 absolute inset-x-0 bottom-0 z-1 h-20 bg-linear-to-t to-transparent"></div>
        </div>
    </section>

    {{-- Gold divider --}}
    <div class="from-gold via-gold-dark to-gold h-1 bg-linear-to-r"></div>

    {{-- Featured Post --}}
    @if ($featuredPost)
        <section class="bg-cream py-16 md:py-24" data-animate>
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <div class="mb-10 text-center">
                    <span class="font-body text-gold text-sm font-semibold tracking-[0.15em] uppercase">Latest from the Blog</span>
                </div>
                <a
                    href="{{ route('blog.show', $featuredPost) }}"
                    class="group from-navy to-navy-light block overflow-hidden rounded-2xl bg-linear-to-br shadow-xl transition-[transform,box-shadow] duration-300 hover:-translate-y-1 hover:shadow-2xl"
                >
                    <div class="flex flex-col md:flex-row">
                        {{-- Left: Cover image or gradient --}}
                        <div class="relative min-h-[220px] shrink-0 overflow-hidden md:min-h-[320px] md:w-2/5">
                            @if ($featuredPost->cover_image_url)
                                <img
                                    src="{{ $featuredPost->cover_image_url }}"
                                    alt="{{ $featuredPost->title }}"
                                    class="absolute inset-0 size-full object-cover transition-transform duration-700 group-hover:scale-105"
                                />
                                <div class="md:to-navy/40 absolute inset-y-0 right-0 left-1/2 md:bg-linear-to-r md:from-transparent md:via-transparent"></div>
                            @else
                                <div class="from-purple/40 to-navy absolute inset-0 flex items-center justify-center bg-linear-to-br">
                                    <div class="flex size-20 items-center justify-center rounded-2xl border border-white/10 bg-white/10">
                                        <svg class="text-gold/60 size-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Right: Content --}}
                        <div class="flex flex-1 flex-col justify-center p-8 md:p-10 lg:p-12">
                            <div class="mb-5 flex flex-wrap items-center gap-3">
                                <span class="bg-gold/20 text-gold rounded-full px-3 py-1 text-[10px] font-bold tracking-wider uppercase">Featured</span>
                                @if ($featuredPost->category)
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold tracking-wider text-white/70 uppercase">{{ $featuredPost->category_label }}</span>
                                @endif
                                <span class="text-xs text-white/30">{{ $featuredPost->reading_time }} min read</span>
                            </div>
                            <h2 class="font-heading group-hover:text-gold mb-4 text-2xl/snug font-bold text-white transition-colors duration-300 md:text-3xl lg:text-4xl">
                                {{ $featuredPost->title }}
                            </h2>
                            @if ($featuredPost->excerpt)
                                <p class="mb-6 line-clamp-3 text-base/relaxed text-white/55">
                                    {{ $featuredPost->excerpt }}
                                </p>
                            @endif
                            <div class="mt-auto flex items-center justify-between border-t border-white/10 pt-6">
                                <div class="flex items-center gap-3">
                                    <div class="border-gold/20 from-gold/25 to-purple/15 font-heading text-gold flex size-9 items-center justify-center rounded-full border bg-linear-to-br text-[10px] font-bold">
                                        {{ $featuredPost->author_initials }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ $featuredPost->author_name }}</p>
                                        <p class="text-xs text-white/40">
                                            {{ $featuredPost->published_at->format('F j, Y') }}
                                        </p>
                                    </div>
                                </div>
                                <span class="text-gold hidden items-center gap-1.5 text-sm font-semibold transition-[gap] group-hover:gap-2.5 sm:inline-flex">
                                    Read Article
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>
    @endif

    {{-- Latest Posts / Coming Soon --}}
    @if ($latestPosts->count())
        <section class="bg-white py-16 md:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="mb-12 flex items-end justify-between" data-animate>
                    <div>
                        <span class="font-body text-gold text-sm font-semibold tracking-[0.15em] uppercase">Latest Stories</span>
                        <h2 class="font-heading text-navy mt-2 text-3xl font-bold md:text-4xl">From the Blog</h2>
                    </div>
                    <a
                        href="{{ route('blog.index') }}"
                        class="font-body text-purple hover:text-navy hidden items-center gap-1 text-sm font-semibold transition-colors sm:inline-flex"
                    >
                        View all
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($latestPosts as $post)
                        <a
                            href="{{ route('blog.show', $post) }}"
                            class="group post-card border-navy/5 relative overflow-hidden rounded-xl border bg-white shadow-sm hover:-translate-y-1 hover:shadow-lg"
                            data-animate
                            data-stagger="{{ $loop->index }}"
                        >
                            <div class="card-shimmer relative overflow-hidden">
                                @if ($post->cover_image_url)
                                    <img
                                        src="{{ $post->cover_image_url }}"
                                        alt="{{ $post->title }}"
                                        class="card-img h-52 w-full object-cover"
                                        loading="lazy"
                                        decoding="async"
                                    />
                                    <div class="card-overlay from-purple/20 absolute inset-0 bg-linear-to-t to-transparent"></div>
                                @else
                                    <div class="from-purple/10 to-gold/10 flex h-52 w-full items-center justify-center bg-linear-to-br">
                                        <span class="text-3xl">✨</span>
                                    </div>
                                @endif
                                @if ($post->category)
                                    <span class="bg-navy/80 font-body absolute top-3 left-3 rounded-full px-3 py-1 text-xs font-bold tracking-wider text-white uppercase backdrop-blur-sm">{{ $post->category_label }}</span>
                                @endif
                            </div>
                            <div class="p-6">
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="font-body text-navy/40 text-xs">{{ $post->published_at->format('M j, Y') }}</span>
                                    <span class="font-body text-navy/40 text-xs">{{ $post->reading_time }} min read</span>
                                </div>
                                <h3 class="font-heading text-navy group-hover:text-purple mb-2 text-xl/snug font-bold transition-colors duration-200">
                                    {{ $post->title }}
                                </h3>
                                @if ($post->excerpt)
                                    <p class="font-body text-navy/65 mb-4 line-clamp-2 text-base leading-[1.7] sm:text-sm">
                                        {{ Str::limit($post->excerpt, 130) }}
                                    </p>
                                @endif
                                <div class="border-navy/5 flex items-center gap-2 border-t pt-3">
                                    <div class="bg-purple/10 text-purple flex size-7 shrink-0 items-center justify-center rounded-full text-[10px] font-bold">
                                        {{ $post->author_initials }}
                                    </div>
                                    <span class="font-body text-navy/40 text-xs font-medium">{{ $post->author_name }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-10 text-center sm:hidden">
                    <a
                        href="{{ route('blog.index') }}"
                        class="font-body text-purple hover:text-navy inline-flex min-h-12 items-center text-base font-semibold transition-colors sm:text-sm"
                    >View all posts →</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Guides --}}
    <section class="bg-cream py-16 md:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            <div class="mb-12 text-center" data-animate>
                <span class="font-body text-gold text-sm font-semibold tracking-[0.15em] uppercase">Plan With Confidence</span>
                <h2 class="font-heading text-navy mt-2 text-3xl font-bold md:text-4xl">Your Guide to the Parks</h2>
                <p class="text-navy/50 mx-auto mt-4 max-w-2xl text-base/relaxed">
                    Practical, regularly reviewed guidance for more comfortable and accessible Disney park days.
                </p>
            </div>

            @if ($latestGuides->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2" data-animate>
                    @foreach ($latestGuides as $guide)
                        <a
                            href="{{ route('guides.show', $guide) }}"
                            class="group border-navy/5 rounded-2xl border bg-white p-7 shadow-sm transition-[transform,box-shadow] hover:-translate-y-1 hover:shadow-lg"
                        >
                            <span class="text-gold text-xs font-bold tracking-widest uppercase">{{ $guide->category_label }}</span>
                            <h3 class="font-heading text-navy group-hover:text-purple mt-3 text-2xl font-bold transition-colors">
                                {{ $guide->title }}
                            </h3>
                            @if ($guide->excerpt)
                                <p class="text-navy/55 mt-3 text-base/relaxed">{{ $guide->excerpt }}</p>
                            @endif
                            <span class="text-purple mt-5 inline-flex min-h-12 items-center text-sm font-semibold">Read guide →</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2" data-animate>
                    @foreach (\App\Models\Guide::CATEGORIES as $slug => $label)
                        <a
                            href="{{ route('guides.index', ['category' => $slug]) }}"
                            class="group border-navy/5 rounded-2xl border bg-white p-7 shadow-sm transition-[transform,box-shadow] hover:-translate-y-1 hover:shadow-lg"
                        >
                            <h3 class="font-heading text-navy group-hover:text-purple text-2xl font-bold transition-colors">
                                {{ $label }}
                            </h3>
                            <span class="text-gold mt-4 inline-flex min-h-12 items-center text-sm font-semibold">Explore guides →</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-10 text-center">
                <a
                    href="{{ route('guides.index') }}"
                    class="bg-navy hover:bg-purple inline-flex min-h-12 items-center rounded-full px-7 py-3 text-base font-semibold text-white transition-colors"
                >Browse all guides</a>
            </div>
        </div>
    </section>

    {{-- Podcast Section --}}
    <section class="relative bg-white py-16 md:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6">
            <div class="mb-12 flex items-end justify-between" data-animate>
                <div class="flex items-center gap-4">
                    <img
                        src="/images/podcast/mouse28-cover.jpg"
                        alt=""
                        width="64"
                        height="64"
                        loading="lazy"
                        decoding="async"
                        class="size-14 rounded-xl object-cover shadow-sm"
                    />
                    <div>
                    <span class="font-body text-purple/65 text-sm font-semibold tracking-[0.15em] uppercase">🎙️ Also Listen</span>
                    <h2 class="font-heading text-navy mt-2 text-3xl font-bold md:text-4xl">From the Podcast</h2>
                    </div>
                </div>
                <a
                    href="{{ route('episodes.index') }}"
                    class="font-body text-purple hover:text-navy hidden items-center gap-1 text-sm font-semibold transition-colors sm:inline-flex"
                >
                    All episodes
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </a>
            </div>

            @if ($latestEpisodes->count())
                <div class="divide-navy/8 divide-y">
                    @foreach ($latestEpisodes as $episode)
                        <a
                            href="{{ route('episodes.show', $episode) }}"
                            class="group hover:bg-cream/50 -mx-4 flex min-h-[56px] items-center gap-5 rounded-xl px-4 py-5 transition-[transform,background-color] duration-250 hover:translate-x-1"
                            data-animate
                            data-stagger="{{ $loop->index }}"
                        >
                            <div class="bg-purple/10 group-hover:bg-purple/20 relative flex size-12 shrink-0 items-center justify-center rounded-full transition-colors">
                                <span class="font-body text-purple text-sm font-bold transition-opacity group-hover:opacity-0">{{ $episode->episode_number }}</span>
                                <svg class="text-purple absolute size-5 opacity-0 transition-opacity group-hover:opacity-100" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-heading text-navy group-hover:text-purple truncate text-base font-semibold transition-colors">
                                    {{ $episode->title }}
                                </h3>
                                <p class="font-body text-navy/40 mt-0.5 text-sm">
                                    {{ $episode->published_at->format('M j, Y') }}
                                    @if ($episode->duration_seconds)
                                        <span class="mx-1.5">·</span
                                        >{{ $episode->formatted_duration }}
                                    @endif
                                </p>
                            </div>
                            <svg class="text-navy/25 group-hover:text-purple size-5 shrink-0 transition-[transform,color] group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                    @endforeach
                </div>
                <div class="mt-6 text-center sm:hidden">
                    <a
                        href="{{ route('episodes.index') }}"
                        class="font-body text-purple hover:text-navy inline-flex min-h-12 items-center text-base font-semibold transition-colors sm:text-sm"
                    >All episodes →</a>
                </div>
            @else
                @php
                    $homeWaveformHeights = ['h-[20%]', 'h-[35%]', 'h-[55%]', 'h-[75%]', 'h-full', 'h-[65%]', 'h-[45%]', 'h-[30%]'];
                @endphp
                <div class="border-gold/12 from-navy to-navy-light relative overflow-hidden rounded-3xl border bg-linear-to-br px-6 py-12 sm:px-10">
                    {{-- Ambient glow --}}
                    <div class="pointer-events-none absolute top-[-40%] right-[-20%] size-[300px] bg-[radial-gradient(circle,rgb(212_168_67/10%)_0%,transparent_60%)]"></div>

                    <div class="relative flex flex-wrap items-center gap-10">
                        {{-- Waveform visual --}}
                        <div class="shrink-0">
                            <div class="size-[100px] overflow-hidden rounded-[1.25rem] shadow-[0_10px_30px_rgb(212_168_67/25%)]">
                                <img
                                    src="/images/podcast/mouse28-cover.jpg"
                                    alt="Mouse28 podcast artwork"
                                    width="3000"
                                    height="3000"
                                    loading="lazy"
                                    decoding="async"
                                    class="size-full object-cover"
                                />
                            </div>
                        </div>

                        <div class="min-w-60 flex-1">
                            <h3 class="font-heading text-cream mb-2 text-2xl font-bold">Episode One Is Coming</h3>
                            <p class="font-body text-cream/50 mb-5 text-[0.95rem] leading-[1.7]">
                                Jeffrey &amp; Cassie are recording their first episode, an intro to who they are, why
                                they started Mouse28, and what to expect from the show.
                            </p>
                            {{-- Faux waveform bars --}}
                            <div class="flex h-7 items-end gap-[3px] opacity-30">
                                @for ($i = 0; $i < 40; $i++)
                                    <div class="w-[3px] rounded-sm bg-gold {{ $homeWaveformHeights[$i % count($homeWaveformHeights)] }}"></div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div
                class="mt-8 flex flex-wrap items-center justify-center gap-3"
                aria-label="Subscribe to the Mouse28 podcast"
            >
                @foreach ($podcast->distributionLinks() as $link)
                    <a
                        href="{{ $link['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="border-purple/15 bg-purple/5 text-purple hover:border-purple/30 hover:bg-purple/10 inline-flex min-h-12 items-center rounded-full border px-5 py-3 text-sm font-semibold transition-colors"
                    >{{ $link['label'] }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Meet the Family --}}
    <section class="bg-cream relative overflow-hidden py-16 md:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            <div class="flex flex-col items-center gap-10 md:flex-row md:gap-14" data-animate>
                {{-- Photo --}}
                <div class="w-full shrink-0 md:w-2/5">
                    <div class="relative">
                        <div class="border-gold/20 overflow-hidden rounded-2xl border-[3px] shadow-xl">
                            <img
                                src="/images/meet-jeffrey-and-cassie.jpg"
                                alt="Jeffrey and Cassie Davidson at Disney"
                                width="1024"
                                height="1536"
                                loading="lazy"
                                decoding="async"
                                class="aspect-4/5 h-auto w-full object-cover"
                            />
                        </div>
                    </div>
                </div>

                {{-- Text --}}
                <div class="flex-1">
                    <span class="font-body text-gold text-sm font-semibold tracking-[0.15em] uppercase">The Family Behind Mouse28</span>
                    <h2 class="font-heading text-navy mt-2 mb-4 text-3xl font-bold md:text-4xl">
                        Meet Jeffrey & Cassie
                    </h2>
                    <div class="font-body text-navy/60 space-y-4 leading-relaxed">
                        <p>
                            We're a Florida family who visits Disney every single week with our daughter Viola. She's
                            autistic and nonverbal, and she's taught us to experience the parks in ways we never
                            expected.
                        </p>
                        <p>
                            Mouse28 is where we share what we've learned — the accessibility tips nobody tells you, the
                            sensory-friendly spots, the real moments that make it all worth it. Two voices, no filter,
                            lots of maple popcorn.
                        </p>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <a
                            href="{{ route('about') }}"
                            class="bg-navy font-body hover:bg-navy-light inline-flex items-center gap-2 rounded-full px-6 py-3 text-sm font-semibold text-white transition-[transform,background-color,box-shadow] duration-300 hover:-translate-y-0.5 hover:shadow-lg"
                        >
                            Our Full Story
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                        </a>
                        <a
                            href="{{ route('contact.show') }}"
                            class="font-body text-purple hover:text-navy inline-flex min-h-12 items-center gap-1.5 text-base font-semibold transition-colors sm:text-sm"
                        >
                            Say hello →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- The Story in Numbers --}}
    <section class="relative bg-white">
        <div class="via-gold/20 h-px bg-linear-to-r from-transparent to-transparent"></div>
        <div class="mx-auto max-w-5xl px-4 py-18 sm:px-6">
            <div class="mb-10 text-center">
                <span class="font-body text-gold text-[0.7rem] font-semibold tracking-[0.15em] uppercase">The Family Behind Mouse28</span>
            </div>
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4 md:gap-8">
                <div class="text-center">
                    <div class="font-heading text-navy text-[clamp(2.5rem,5vw,3.5rem)] leading-none font-extrabold">
                        20
                    </div>
                    <p class="font-body text-navy/45 mt-2 text-base/6 sm:text-[0.8rem]/6">
                        Minutes from<br />the Magic Kingdom
                    </p>
                </div>
                <div class="text-center">
                    <div class="font-heading text-navy text-[clamp(2.5rem,5vw,3.5rem)] leading-none font-extrabold">
                        2
                    </div>
                    <p class="font-body text-navy/45 mt-2 text-base/6 sm:text-[0.8rem]/6">
                        Voices, one mic,<br />zero filter
                    </p>
                </div>
                <div class="text-center">
                    <div class="font-heading text-gold text-[clamp(2.5rem,5vw,3.5rem)] leading-none font-extrabold">
                        52
                    </div>
                    <p class="font-body text-navy/45 mt-2 text-base/6 sm:text-[0.8rem]/6">
                        Park days a year<br />(at least)
                    </p>
                </div>
                <div class="text-center">
                    <div class="font-heading text-navy text-[clamp(2.5rem,5vw,3.5rem)] leading-none font-extrabold">
                        ∞
                    </div>
                    <p class="font-body text-navy/45 mt-2 text-base/6 sm:text-[0.8rem]/6">
                        Buckets of maple popcorn<br />(and counting)
                    </p>
                </div>
            </div>
        </div>
        <div class="via-gold/20 h-px bg-linear-to-r from-transparent to-transparent"></div>
    </section>

    {{-- Newsletter CTA --}}
    <section
        id="newsletter"
        class="from-navy via-navy-light to-navy relative overflow-hidden bg-linear-to-br py-16 md:py-24"
    >
        @php
            $newsletterHasFeedback = $errors->newsletter->isNotEmpty() || session('newsletter_error');
        @endphp
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <span class="sparkle text-gold/30 absolute top-[20%] left-[15%] text-sm">✦</span>
            <span class="sparkle-delay text-gold/20 absolute right-[20%] bottom-[25%] text-lg">✧</span>
            <span class="sparkle-delay-2 text-gold/15 absolute top-[50%] left-[70%] text-xs">✦</span>
        </div>

        <div class="relative z-10 mx-auto max-w-2xl px-4 text-center sm:px-6" data-animate>
            <h2 class="font-heading mb-4 text-3xl font-bold text-white md:text-4xl">Stay in the Loop</h2>
            <p class="font-body mb-8 text-lg leading-[1.7] text-white/55">
                New posts, podcast episodes, and park tips straight to your inbox. No spam, just pixie dust.
            </p>
            @if (session('newsletter_success'))
                <div class="mx-auto max-w-md rounded-xl border border-green-400/30 bg-green-500/20 px-6 py-4">
                    <p class="font-body text-sm text-green-300">✨ You're subscribed! We'll send you the good stuff.</p>
                </div>
            @elseif (session('newsletter_error'))
                <div class="mx-auto mb-4 max-w-md rounded-xl border border-red-400/30 bg-red-500/20 px-6 py-4">
                    <p class="font-body text-sm text-red-300">{{ session('newsletter_error') }}</p>
                </div>
                <form action="{{ route('newsletter.store') }}" method="POST" class="mx-auto max-w-md space-y-3">
                    @csrf
                    <x-newsletter-protection honeypot-id="home-newsletter-website-error" />
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <label for="home-newsletter-email-error" class="sr-only">Email address</label>
                        <input
                            id="home-newsletter-email-error"
                            type="email"
                            name="email"
                            value="{{ $newsletterHasFeedback ? old('email') : '' }}"
                            placeholder="your@email.com"
                            autocomplete="email"
                            required
                            @error('email', 'newsletter') aria-invalid="true" aria-describedby="home-newsletter-email-error-message" @enderror
                            class="newsletter-input font-body focus:border-gold/40 focus:ring-gold/60 min-h-[48px] flex-1 rounded-full border border-white/20 bg-white/10 px-5 py-3.5 text-base text-white transition-[border-color,box-shadow] duration-300 placeholder:text-white/35 focus:ring-2 focus:outline-none sm:text-sm"
                        />
                        <button
                            type="submit"
                            class="bg-gold font-body text-navy hover:bg-gold-light hover:shadow-gold/30 min-h-[48px] rounded-full px-7 py-3.5 text-base font-semibold transition-[transform,background-color,box-shadow] duration-300 hover:-translate-y-0.5 hover:scale-105 hover:shadow-lg active:scale-95 sm:text-sm"
                        >
                            Subscribe
                        </button>
                    </div>
                    @error('email', 'newsletter')
                        <p id="home-newsletter-email-error-message" role="alert" class="text-left text-sm text-red-300">
                            {{ $message }}
                        </p>
                    @enderror
                </form>
            @else
                <form action="{{ route('newsletter.store') }}" method="POST" class="mx-auto max-w-md space-y-3">
                    @csrf
                    <x-newsletter-protection honeypot-id="home-newsletter-website" />
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <label for="home-newsletter-email" class="sr-only">Email address</label>
                        <input
                            id="home-newsletter-email"
                            type="email"
                            name="email"
                            value="{{ $newsletterHasFeedback ? old('email') : '' }}"
                            placeholder="your@email.com"
                            autocomplete="email"
                            required
                            @error('email', 'newsletter') aria-invalid="true" aria-describedby="home-newsletter-email-message" @enderror
                            class="newsletter-input font-body focus:border-gold/40 focus:ring-gold/60 min-h-[48px] flex-1 rounded-full border border-white/20 bg-white/10 px-5 py-3.5 text-base text-white transition-[border-color,box-shadow] duration-300 placeholder:text-white/35 focus:ring-2 focus:outline-none sm:text-sm"
                        />
                        <button
                            type="submit"
                            class="bg-gold font-body text-navy hover:bg-gold-light hover:shadow-gold/30 min-h-[48px] rounded-full px-7 py-3.5 text-base font-semibold transition-[transform,background-color,box-shadow] duration-300 hover:-translate-y-0.5 hover:scale-105 hover:shadow-lg active:scale-95 sm:text-sm"
                        >
                            Subscribe
                        </button>
                    </div>
                    @error('email', 'newsletter')
                        <p id="home-newsletter-email-message" role="alert" class="text-left text-sm text-red-300">
                            {{ $message }}
                        </p>
                    @enderror
                </form>
            @endif
            <div class="mt-10 flex flex-wrap items-center justify-center gap-6 border-t border-white/10 pt-8">
                @foreach ($podcast->distributionLinks() as $link)
                    <a
                        href="{{ $link['url'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-body hover:text-gold inline-flex min-h-12 items-center text-base text-white/55 transition-colors sm:text-sm"
                    >{{ $link['label'] }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Scroll animation observer --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            document.documentElement.classList.add('js-animate');

            // Staggered scroll animations
            const obs = new IntersectionObserver(
                (entries) => {
                    entries.forEach((e) => {
                        if (e.isIntersecting) {
                            const stagger = e.target.dataset.stagger;
                            const delay = stagger ? parseInt(stagger) * 120 : 0;
                            setTimeout(() => e.target.classList.add('is-visible'), delay);
                            obs.unobserve(e.target);
                        }
                    });
                },
                { threshold: 0.08, rootMargin: '0px 0px -60px 0px' },
            );
            document.querySelectorAll('[data-animate]').forEach((el) => obs.observe(el));

            // Subtle hero parallax on scroll
            const heroPhoto = document.querySelector('.hero-split-photo img');
            if (heroPhoto) {
                let ticking = false;
                window.addEventListener(
                    'scroll',
                    function () {
                        if (!ticking) {
                            requestAnimationFrame(function () {
                                const scroll = window.scrollY;
                                if (scroll < 800) {
                                    heroPhoto.style.transform = 'translateY(' + scroll * 0.15 + 'px) scale(1.05)';
                                }
                                ticking = false;
                            });
                            ticking = true;
                        }
                    },
                    { passive: true },
                );
            }
        });
    </script>
</x-layouts.app>
