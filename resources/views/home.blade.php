<x-layouts.app
    title="Mouse28 | Disney Parks Through Different Eyes"
    description="Accessibility tips, sensory-friendly park planning, family experiences, and the Mouse28 podcast from Jeffrey and Cassie Davidson."
    og-description="Accessibility tips, sensory-friendly Disney park planning, and honest family experiences from Jeffrey and Cassie Davidson."
    og-image="/images/hero-family.jpg"
    :canonical="route('home')"
    :show-footer-newsletter="false"
    :dispatch-layout="true"
>
    <div class="dispatch-cloth overflow-hidden">
        <section class="dispatch-hero relative px-4 pt-6 sm:px-6 sm:pt-8">
            <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
                <div class="dispatch-route-line absolute top-10 left-[4%] h-24 w-52 rotate-6"></div>
                <div class="dispatch-route-line absolute right-[3%] bottom-4 h-28 w-64 -rotate-8"></div>
            </div>
            <div class="relative mx-auto grid max-w-[86rem] gap-4 md:grid-cols-[8fr_12fr] md:items-stretch">
                <div
                    class="dispatch-paper dispatch-paper-map relative z-10 flex flex-col justify-center overflow-hidden p-7 sm:p-10 md:-rotate-1 md:p-9 lg:px-20 lg:py-12"
                    data-dispatch-motion="hero-paper"
                >
                    <h1 class="font-heading text-navy max-w-[13ch] text-4xl [font-weight:680] tracking-[-0.025em] text-balance sm:text-5xl lg:text-6xl xl:text-[3.5rem]">
                        Disney Parks Through Different Eyes
                    </h1>
                    <div class="text-gold my-5 flex items-center gap-3" aria-hidden="true">
                        <span class="h-px w-16 bg-current"></span><span class="font-heading text-2xl">✦</span
                        ><span class="h-px w-16 bg-current"></span>
                    </div>
                    <p class="text-navy/75 max-w-[42ch] text-base/7 text-pretty sm:text-[0.9375rem]/6">
                        Accessibility tips, sensory-friendly park planning, and honest family experiences from Jeffrey
                        and Cassie Davidson.
                    </p>
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <a
                            href="{{ route('blog.index') }}"
                            class="dispatch-button bg-gold text-navy hover:bg-gold-light inline-flex min-h-12 items-center justify-center px-6 py-3 text-base font-semibold sm:text-sm"
                        >Read the Blog</a>
                        <a
                            href="{{ route('episodes.index') }}"
                            class="text-purple decoration-gold/70 hover:text-navy inline-flex min-h-12 items-center text-base font-semibold underline underline-offset-8 transition-colors sm:text-sm"
                        >Listen to the podcast</a>
                    </div>
                </div>
                <div
                    class="hero-split-photo dispatch-photo-frame min-h-80 sm:min-h-96 md:min-h-0 md:rotate-1"
                    data-dispatch-motion="hero-photo"
                >
                    <picture>
                        <source
                            type="image/webp"
                            srcset="/images/hero-family-640.webp 640w, /images/hero-family-1024.webp 1024w, /images/hero-family.webp 1600w"
                            sizes="(min-width: 768px) 60vw, 100vw"
                        />
                        <img
                            src="/images/hero-family.webp"
                            alt="Jeffrey and Cassie Davidson on Kilimanjaro Safaris at Disney's Animal Kingdom"
                            width="1600"
                            height="1600"
                            fetchpriority="high"
                        />
                    </picture>
                    <span
                        class="dispatch-paperclip border-gold absolute -top-2 right-8 z-10 hidden h-20 w-7 rotate-12 rounded-full border-[3px] md:block"
                        aria-hidden="true"
                    ></span>
                </div>
            </div>
        </section>

        <section class="dispatch-blog-cluster relative z-10 px-4 pb-5 sm:px-6" data-dispatch-reveal="story-folio">
            <div class="mx-auto grid max-w-[86rem] items-start md:grid-cols-[8fr_12fr]">
                <div class="relative z-20 mx-auto w-[82%] max-w-sm md:mx-0 md:w-[82%] md:max-w-none md:translate-x-40 md:-translate-y-8 md:-rotate-2">
                    <span class="sr-only">Latest from the Blog</span>
                    @if ($featuredPost)
                        <a
                            href="{{ route('blog.show', $featuredPost) }}"
                            aria-label="Read {{ $featuredPost->title }}"
                            class="dispatch-feature-book group block"
                        >
                            <div class="dispatch-book-title">
                                <p class="text-purple text-base font-semibold sm:text-sm">Featured story</p>
                                <p class="font-heading text-navy mt-2 text-2xl [font-weight:620] tracking-[-0.02em] text-balance lg:text-3xl">
                                    {{ $featuredPost->title }}
                                </p>
                            </div>
                            <div class="overflow-hidden rounded-lg">
                                <x-post-artwork
                                    :post="$featuredPost"
                                    class="aspect-[5/4] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.02] md:aspect-square"
                                />
                            </div>
                        </a>
                    @else
                        <div class="dispatch-feature-book">
                            <div class="dispatch-book-title">
                                <p class="text-purple text-base font-semibold sm:text-sm">From the blog</p>
                                <p class="font-heading text-navy mt-2 text-3xl [font-weight:620] tracking-[-0.02em] text-balance">
                                    Our first dispatch is being prepared.
                                </p>
                            </div>
                            <div class="from-purple/15 to-gold/25 text-navy flex aspect-[5/4] flex-col items-center justify-center rounded-lg bg-linear-to-br p-8 text-center md:aspect-square">
                                <span class="font-heading text-4xl [font-weight:680] tracking-[-0.03em]">Mouse28</span>
                                <a
                                    href="{{ route('blog.index') }}"
                                    class="text-purple decoration-gold/70 hover:text-navy mt-4 inline-flex min-h-12 items-center text-base font-semibold underline underline-offset-8 sm:text-sm"
                                >Visit the blog</a>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="dispatch-latest-sheet relative z-10 p-5 pt-7 sm:p-7 md:ml-4 md:pl-14 lg:p-8 lg:pl-16">
                    <div class="border-gold/45 flex items-center justify-between gap-4 border-b pb-4">
                        <h2 class="font-heading text-navy text-3xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-4xl">
                            Latest Stories
                        </h2>
                        <a
                            href="{{ route('blog.index') }}"
                            class="text-purple decoration-gold/70 hover:text-navy hidden min-h-12 items-center text-sm font-semibold underline underline-offset-8 sm:inline-flex"
                        >View all posts</a>
                    </div>
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        @forelse ($latestPosts->take(3) as $post)
                            <a
                                href="{{ route('blog.show', $post) }}"
                                class="dispatch-story-card group flex h-full flex-col overflow-hidden"
                            >
                                <x-post-artwork
                                    :post="$post"
                                    :compact="true"
                                    class="aspect-[4/3] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.035]"
                                />
                                <div class="flex flex-1 flex-col p-3">
                                    <p class="text-purple text-sm font-semibold sm:text-xs">
                                        {{ $post->category_label }}
                                    </p>
                                    <h3 class="font-heading text-navy group-hover:text-purple mt-1 line-clamp-3 text-lg [font-weight:560] tracking-[-0.012em] text-balance">
                                        {{ $post->title }}
                                    </h3>
                                    <p class="text-navy/65 mt-auto pt-2 text-sm tabular-nums sm:text-xs">
                                        {{ $post->published_at->format('M j, Y') }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <div class="border-gold/35 bg-cream/70 col-span-full rounded-xl border p-6 text-center sm:p-8">
                                <p class="font-heading text-navy text-2xl [font-weight:620] tracking-[-0.02em]">
                                    More stories are on the way.
                                </p>
                                <p class="text-navy/70 mx-auto mt-2 max-w-lg text-base/7 sm:text-[0.9375rem]/6">
                                    We are preparing practical park notes and honest family stories for this space.
                                </p>
                            </div>
                        @endforelse
                    </div>
                    <a
                        href="{{ route('blog.index') }}"
                        class="text-purple decoration-gold/70 mt-4 inline-flex min-h-12 items-center text-base font-semibold underline underline-offset-8 sm:hidden"
                    >View all posts</a>
                    <span class="dispatch-index-tab bg-purple text-cream absolute top-20 -right-1 hidden md:block">Latest dispatches</span>
                </div>
            </div>
        </section>

        <section class="relative z-10 px-4 pb-5 sm:px-6 md:-mt-4">
            <div class="dispatch-guide-shell relative isolate mx-auto max-w-[86rem]">
                <div class="dispatch-guide-spread grid overflow-hidden md:grid-cols-[7fr_13fr]">
                    <div class="dispatch-paper-map border-navy/10 relative flex flex-col justify-center border-b p-7 sm:p-9 md:border-r md:border-b-0 lg:p-11">
                        <h2 class="font-heading text-navy max-w-[9ch] text-3xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-4xl">
                            Your Guide to the Parks
                        </h2>
                        <p class="text-navy/70 mt-4 max-w-[40ch] text-base/7 text-pretty sm:text-[0.9375rem]/6">
                            Practical, regularly reviewed guidance for more comfortable and accessible Disney park days.
                        </p>
                        @if ($latestGuides->isNotEmpty())
                            <a
                                href="{{ route('guides.index') }}"
                                class="dispatch-button bg-gold text-navy hover:bg-gold-light mt-6 inline-flex min-h-12 w-fit items-center px-6 py-3 text-base font-semibold sm:text-sm"
                            >Browse all guides</a>
                        @else
                            <a
                                href="{{ route('blog.index') }}"
                                class="dispatch-button bg-gold text-navy hover:bg-gold-light mt-6 inline-flex min-h-12 w-fit items-center px-6 py-3 text-base font-semibold sm:text-sm"
                            >Explore planning stories</a>
                        @endif
                        <div class="dispatch-map-trail mt-7" aria-hidden="true"></div>
                    </div>
                    <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-6">
                        @forelse ($latestGuides->take(2) as $guide)
                            <a
                                href="{{ route('guides.show', $guide) }}"
                                class="dispatch-guide-card group overflow-hidden"
                                ><x-guide-artwork
                                    :guide="$guide"
                                    class="aspect-[4/3] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.035]" />
                                <div class="p-5">
                                    <p class="text-purple text-sm font-semibold sm:text-xs">Guide</p>
                                    <h3 class="font-heading text-navy group-hover:text-purple mt-1 text-2xl [font-weight:580] tracking-[-0.015em] text-balance">
                                        {{ $guide->title }}
                                    </h3>
                                    @if ($guide->excerpt)
                                        <p class="text-navy/70 mt-3 line-clamp-2 text-base/7 text-pretty sm:text-[0.9375rem]/6">
                                            {{ $guide->excerpt }}
                                        </p>
                                    @endif</div
                            ></a>
                        @empty
                            <div class="col-span-full">
                                <div class="mb-4 flex items-end justify-between gap-4 px-1">
                                    <div>
                                        <p class="text-purple text-xs font-bold tracking-[0.16em] uppercase">
                                            While the guidebook grows
                                        </p>
                                        <h3 class="font-heading text-navy mt-1 text-2xl [font-weight:620] tracking-[-0.02em]">
                                            Start planning with these stories
                                        </h3>
                                    </div>
                                </div>
                                @if ($planningPosts->isNotEmpty())
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        @foreach ($planningPosts as $post)
                                            <a
                                                href="{{ route('blog.show', $post) }}"
                                                class="dispatch-guide-card group overflow-hidden"
                                            >
                                                <x-post-artwork
                                                    :post="$post"
                                                    class="aspect-[4/3] w-full object-cover transition-transform duration-700 ease-out group-hover:scale-[1.035]"
                                                />
                                                <div class="p-5">
                                                    <p class="text-purple text-xs font-semibold">
                                                        {{ $post->category_label }}
                                                    </p>
                                                    <p class="font-heading text-navy group-hover:text-purple mt-1 text-xl [font-weight:580] tracking-[-0.015em] text-balance">
                                                        {{ $post->title }}
                                                    </p>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="border-gold/35 bg-cream/70 flex min-h-52 flex-col items-center justify-center rounded-xl border p-6 text-center sm:p-8">
                                        <p class="text-navy/70 max-w-lg text-base/7">
                                            Our first planning stories and reviewed guides are being prepared.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="absolute top-10 -right-3 z-[-1] hidden flex-col gap-2 md:flex" aria-hidden="true">
                    <span class="dispatch-guide-tab bg-purple">Accessibility</span>
                    <span class="dispatch-guide-tab bg-gold text-navy">Sensory</span>
                    <span class="dispatch-guide-tab bg-purple-dark">Family</span>
                </div>
            </div>
        </section>

        <section class="relative z-10 px-4 pb-5 sm:px-6">
            <div class="dispatch-podcast-panel mx-auto max-w-[86rem] p-6 sm:p-6">
                <div class="border-cream/15 flex items-center justify-between gap-5 border-b pb-4">
                    <h2 class="font-heading text-cream text-3xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-4xl">
                        From the Podcast
                    </h2>
                    <a
                        href="{{ route('episodes.index') }}"
                        class="text-gold hover:text-gold-light hidden min-h-12 items-center text-sm font-semibold underline underline-offset-8 sm:inline-flex"
                    >All episodes</a>
                </div>
                <div class="mt-4 grid gap-5 md:grid-cols-[5fr_7fr_8fr] md:items-center">
                    <div class="dispatch-podcast-frame mx-auto w-full max-w-72 p-3 md:mx-0">
                        <img
                            src="/images/podcast/mouse28-cover.webp"
                            alt="Mouse28 podcast artwork"
                            width="1200"
                            height="1200"
                            loading="lazy"
                            decoding="async"
                            class="aspect-square w-full object-cover"
                        />
                    </div>
                    @if ($latestEpisodes->first())
                        @php($leadEpisode = $latestEpisodes->first())
                        <div>
                            <p class="text-gold text-sm font-semibold sm:text-xs">Latest episode</p>
                            <h3 class="font-heading text-cream mt-2 text-2xl [font-weight:580] tracking-[-0.015em] text-balance">
                                {{ $leadEpisode->title }}
                            </h3>
                            @if ($leadEpisode->description)
                                <p class="text-cream/70 mt-3 line-clamp-3 text-base/7 text-pretty sm:text-[0.9375rem]/6">
                                    {{ $leadEpisode->description }}
                                </p>
                            @endif
                            <a
                                href="{{ route('episodes.show', $leadEpisode) }}"
                                class="text-gold mt-4 inline-flex min-h-12 items-center text-base font-semibold underline underline-offset-8 sm:text-sm"
                            >Episode details</a>
                        </div>
                    @else
                        <div>
                            <h3 class="font-heading text-cream text-2xl [font-weight:580] tracking-[-0.015em]">
                                Episode One Is Coming
                            </h3>
                            <p class="text-cream/70 mt-3 text-base/7 text-pretty sm:text-[0.9375rem]/6">
                                Jeffrey and Cassie are recording an introduction to Mouse28 and what to expect from the
                                show.
                            </p>
                        </div>
                    @endif
                    <div class="divide-cream/15 border-cream/15 divide-y border-y">
                        @forelse ($latestEpisodes->skip(1)->take(3) as $episode)
                            <a
                                href="{{ route('episodes.show', $episode) }}"
                                class="group flex min-h-20 items-center gap-4 py-3"
                                ><span
                                    class="dispatch-play-button border-gold/60 text-gold group-hover:bg-gold group-hover:text-navy flex size-10 shrink-0 items-center justify-center rounded-full border"
                                    ><svg aria-hidden="true" class="ml-0.5 size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z" /></svg
                                ></span>
                                <div class="min-w-0">
                                    <p class="text-gold text-sm tabular-nums sm:text-xs">
                                        Episode {{ $episode->episode_number }}
                                        @if ($episode->duration_seconds) ·{{ $episode->formatted_duration }}@endif
                                    </p>
                                    <h3 class="font-heading text-cream group-hover:text-gold mt-1 line-clamp-2 text-lg [font-weight:560] tracking-[-0.012em]">
                                        {{ $episode->title }}
                                    </h3>
                                </div></a>
                        @empty
                            <p class="text-cream/70 py-6 text-base/7 sm:text-[0.9375rem]/6">
                                New conversations and practical park stories are on the way.
                            </p>
                        @endforelse
                    </div>
                </div>
                <a
                    href="{{ route('episodes.index') }}"
                    class="text-gold mt-5 inline-flex min-h-12 items-center text-base font-semibold underline underline-offset-8 sm:hidden"
                >All episodes</a>
            </div>
        </section>

        <section class="relative z-10 px-4 pb-5 sm:px-6">
            <div class="dispatch-about-sheet mx-auto grid max-w-[86rem] items-center gap-7 p-5 sm:p-7 md:grid-cols-[8fr_12fr] md:gap-10 lg:p-8">
                <div class="dispatch-photo-stack relative mx-auto w-full max-w-lg md:mx-0">
                    <img
                        src="/images/meet-jeffrey-and-cassie.webp"
                        alt="Jeffrey and Cassie Davidson at Disney"
                        width="800"
                        height="1200"
                        loading="lazy"
                        decoding="async"
                        class="aspect-[4/3] w-full object-cover object-center"
                    />
                </div>
                <div class="dispatch-paper-map relative p-3 sm:p-5 md:pr-20">
                    <h2 class="font-heading text-navy text-3xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-4xl">
                        Meet Jeffrey & Cassie
                    </h2>
                    <div class="text-navy/70 mt-4 max-w-[65ch] space-y-3 text-base/7 text-pretty sm:text-[0.9375rem]/6">
                        <p>
                            We're a Florida family who visits Disney every week with our daughter Viola. She's autistic
                            and nonverbal, and she's taught us to experience the parks in ways we never expected.
                        </p>
                        <p>
                            Mouse28 is where we share what we've learned: accessibility tips, sensory-friendly spots,
                            and the real moments that make it all worth it.
                        </p>
                    </div>
                    <div class="mt-5 flex flex-wrap gap-4">
                        <a
                            href="{{ route('about') }}"
                            class="dispatch-button bg-navy text-cream hover:bg-navy-light inline-flex min-h-12 items-center px-6 py-3 text-base font-semibold sm:text-sm"
                            >Our Full Story</a
                        ><a
                            href="{{ route('contact.show') }}"
                            class="text-purple decoration-gold/70 hover:text-navy inline-flex min-h-12 items-center text-base font-semibold underline underline-offset-8 sm:text-sm"
                            >Say hello</a>
                    </div>
                    <div
                        class="dispatch-about-route absolute right-1 bottom-2 hidden h-28 w-16 md:block"
                        aria-hidden="true"
                    >
                        <span></span>
                    </div>
                </div>
            </div>
        </section>

        <section id="newsletter" class="relative z-20 px-4 pb-10 sm:px-6 sm:pb-14 md:-mt-16">
            @php($newsletterHasFeedback = $errors->newsletter->isNotEmpty() || session('newsletter_error'))
            <div class="dispatch-envelope relative mx-auto max-w-[82rem] overflow-hidden p-6 sm:p-8 md:min-h-64">
                <div class="relative z-10 grid items-center gap-6 md:grid-cols-[8fr_12fr]">
                    <div>
                        <h2 class="font-heading text-navy text-3xl [font-weight:640] tracking-[-0.02em] text-balance sm:text-4xl">
                            Stay in the Loop
                        </h2>
                        <p class="text-navy/70 mt-3 max-w-[42ch] text-base/7 text-pretty sm:text-[0.9375rem]/6">
                            New posts, podcast episodes, and practical park tips delivered to your inbox.
                        </p>
                    </div>
                    <div>
                        @if (session('newsletter_success'))
                            <div
                                role="status"
                                class="bg-gold/15 text-navy ring-gold/50 rounded-xl px-6 py-4 text-base font-medium ring-1"
                            >
                                You're subscribed. We'll send you the good stuff.
                            </div>
                        @else
                            @if (session('newsletter_error'))
                                <div role="alert" class="mb-4 rounded-xl bg-red-100 px-5 py-3 text-base text-red-800">
                                    {{ session('newsletter_error') }}
                                </div>
                            @endif
                            <form action="{{ route('newsletter.store') }}" method="POST" class="space-y-3">
                                @csrf
                                <x-newsletter-protection honeypot-id="home-newsletter-website" />
                                <div class="flex flex-col gap-3 sm:flex-row">
                                    <label for="home-newsletter-email" class="sr-only">Email address</label
                                    ><input
                                        id="home-newsletter-email"
                                        type="email"
                                        name="email"
                                        value="{{ $newsletterHasFeedback ? old('email') : '' }}"
                                        placeholder="your@email.com"
                                        autocomplete="email"
                                        required
                                        @error('email', 'newsletter') aria-invalid="true" aria-describedby="home-newsletter-email-message" @enderror
                                        @error('email', 'newsletter') autofocus @enderror
                                        class="bg-cream/90 text-navy ring-navy/15 placeholder:text-navy/60 focus:outline-purple min-h-12 min-w-0 flex-1 rounded-xl px-5 py-3 text-base shadow-sm ring-1 focus:outline-2 focus:-outline-offset-1"
                                    /><button
                                        type="submit"
                                        class="dispatch-button bg-gold text-navy hover:bg-gold-light min-h-12 px-7 py-3 text-base font-semibold sm:text-sm"
                                    >
                                        Subscribe
                                    </button>
                                </div>
                                @error('email', 'newsletter')
                                    <p
                                        id="home-newsletter-email-message"
                                        role="alert"
                                        class="text-base text-red-800 sm:text-sm"
                                    >
                                        {{ $message }}
                                    </p>
                                @enderror
                                <p class="text-navy/65 text-base sm:text-sm">
                                    We use your email to send Mouse28 updates.
                                </p>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="dispatch-stamp absolute right-6 bottom-5 hidden rotate-6 p-2 sm:block" aria-hidden="true">
                    <span class="font-heading text-navy text-lg [font-weight:700] tracking-[-0.03em]">M28</span>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (prefersReducedMotion) return;

            document.documentElement.classList.add('js-dispatch-motion');

            if (!('IntersectionObserver' in window)) {
                document.querySelectorAll('[data-dispatch-reveal]').forEach((element) => {
                    element.classList.add('is-visible');
                });

                return;
            }

            const observer = new IntersectionObserver(
                (entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    });
                },
                { threshold: 0.12, rootMargin: '0px 0px -40px 0px' },
            );

            document.querySelectorAll('[data-dispatch-reveal]').forEach((element) => observer.observe(element));
        });
    </script>
</x-layouts.app>
