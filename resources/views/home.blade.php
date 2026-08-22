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
            <span class="sparkle absolute top-[15%] left-[10%] text-[10px] text-gold/25">✦</span>
            <span class="sparkle-delay absolute right-[15%] bottom-[20%] text-sm text-gold/15">✧</span>

            <div class="relative z-10 ml-auto max-w-lg">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-gold/30 px-4 py-[0.35rem]">
                    <span class="size-1.5 rounded-full bg-gold"></span>
                    <span class="font-body text-[0.7rem] font-semibold tracking-[0.15em] text-gold uppercase">Autism Family · Disney Every Week</span>
                </div>

                <h1 class="mb-5 font-heading text-[clamp(2.25rem,4vw,3.5rem)] leading-[1.08] font-bold text-white">
                    Disney Parks<br>Through<br>
                    <span class="text-gold">Different Eyes</span>
                </h1>

                <p class="mb-8 font-body text-base/7 text-cream/50">
                    Accessibility tips, sensory-friendly recommendations, and real stories from a family who visits Disney every single week with our autistic daughter.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    <a href="{{ route('blog.index') }}" class="cta-primary inline-flex min-h-12 items-center rounded-full bg-gold px-7 py-3.5 font-body text-base font-semibold text-navy shadow-lg shadow-gold/20 transition-[transform,background-color,box-shadow] duration-300 hover:-translate-y-1 hover:scale-105 hover:bg-gold-light hover:shadow-gold/50 sm:text-sm">
                        Read Our Blog
                    </a>
                    <a href="{{ route('episodes.index') }}" class="inline-flex min-h-12 items-center gap-2 font-body text-base font-medium text-cream/45 transition-colors duration-200 hover:text-gold sm:text-sm">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        Listen to the podcast
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Photo --}}
        <div class="hero-split-photo">
            <img src="/images/hero-family.jpg" alt="Jeffrey and Cassie Davidson on Kilimanjaro Safaris at Disney's Animal Kingdom" width="2048" height="2048" fetchpriority="high">
            <div class="absolute inset-x-0 bottom-0 z-1 h-20 bg-linear-to-t from-navy/30 to-transparent"></div>
        </div>
    </section>

    {{-- Gold divider --}}
    <div class="h-1 bg-linear-to-r from-gold via-gold-dark to-gold"></div>

{{-- Featured Post --}}
    @if($featuredPost)
        <section class="bg-cream py-16 md:py-24" data-animate>
            <div class="mx-auto max-w-5xl px-4 sm:px-6">
                <div class="mb-10 text-center">
                    <span class="font-body text-sm font-semibold tracking-[0.15em] text-gold uppercase">Latest from the Blog</span>
                </div>
                <a href="{{ route('blog.show', $featuredPost) }}" class="group block overflow-hidden rounded-2xl bg-linear-to-br from-navy to-navy-light shadow-xl transition-[transform,box-shadow] duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="flex flex-col md:flex-row">
                        {{-- Left: Cover image or gradient --}}
                        <div class="relative min-h-[220px] shrink-0 overflow-hidden md:min-h-[320px] md:w-2/5">
                            @if($featuredPost->cover_image_url)
                                <img src="{{ $featuredPost->cover_image_url }}" alt="{{ $featuredPost->title }}" class="absolute inset-0 size-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute inset-y-0 right-0 left-1/2 md:bg-linear-to-r md:from-transparent md:via-transparent md:to-navy/40"></div>
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-linear-to-br from-purple/40 to-navy">
                                    <div class="flex size-20 items-center justify-center rounded-2xl border border-white/10 bg-white/10">
                                        <svg class="size-10 text-gold/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Right: Content --}}
                        <div class="flex flex-1 flex-col justify-center p-8 md:p-10 lg:p-12">
                            <div class="mb-5 flex flex-wrap items-center gap-3">
                                <span class="rounded-full bg-gold/20 px-3 py-1 text-[10px] font-bold tracking-wider text-gold uppercase">Featured</span>
                                @if($featuredPost->category)
                                    <span class="rounded-full bg-white/10 px-3 py-1 text-[10px] font-bold tracking-wider text-white/70 uppercase">{{ $featuredPost->category_label }}</span>
                                @endif
                                <span class="text-xs text-white/30">{{ $featuredPost->reading_time }} min read</span>
                            </div>
                            <h2 class="mb-4 font-heading text-2xl/snug font-bold text-white transition-colors duration-300 group-hover:text-gold md:text-3xl lg:text-4xl">
                                {{ $featuredPost->title }}
                            </h2>
                            @if($featuredPost->excerpt)
                                <p class="mb-6 line-clamp-3 text-base/relaxed text-white/55">{{ $featuredPost->excerpt }}</p>
                            @endif
                            <div class="mt-auto flex items-center justify-between border-t border-white/10 pt-6">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-full border border-gold/20 bg-linear-to-br from-gold/25 to-purple/15 font-heading text-[10px] font-bold text-gold">
                                        {{ $featuredPost->author_initials }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-white">{{ $featuredPost->author_name }}</p>
                                        <p class="text-xs text-white/40">{{ $featuredPost->published_at->format('F j, Y') }}</p>
                                    </div>
                                </div>
                                <span class="hidden items-center gap-1.5 text-sm font-semibold text-gold transition-[gap] group-hover:gap-2.5 sm:inline-flex">
                                    Read Article
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </section>
    @endif

    {{-- Latest Posts / Coming Soon --}}
    @if($latestPosts->count())
        <section class="bg-white py-16 md:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="mb-12 flex items-end justify-between" data-animate>
                    <div>
                        <span class="font-body text-sm font-semibold tracking-[0.15em] text-gold uppercase">Latest Stories</span>
                        <h2 class="mt-2 font-heading text-3xl font-bold text-navy md:text-4xl">From the Blog</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="hidden items-center gap-1 font-body text-sm font-semibold text-purple transition-colors hover:text-navy sm:inline-flex">
                        View all
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($latestPosts as $post)
                        <a href="{{ route('blog.show', $post) }}" class="group post-card relative overflow-hidden rounded-2xl border border-navy/5 bg-white shadow-sm hover:-translate-y-2 hover:shadow-xl" data-animate data-stagger="{{ $loop->index }}">
                            <div class="card-shimmer relative overflow-hidden">
                                @if($post->cover_image_url)
                                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="card-img h-52 w-full object-cover">
                                    <div class="card-overlay absolute inset-0 bg-linear-to-t from-purple/20 to-transparent"></div>
                                @else
                                    <div class="flex h-52 w-full items-center justify-center bg-linear-to-br from-purple/10 to-gold/10">
                                        <span class="text-3xl">✨</span>
                                    </div>
                                @endif
                                @if($post->category)
                                    <span class="absolute top-3 left-3 rounded-full bg-navy/80 px-3 py-1 font-body text-xs font-bold tracking-wider text-white uppercase backdrop-blur-sm">{{ $post->category_label }}</span>
                                @endif
                            </div>
                            <div class="p-6">
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="font-body text-xs text-navy/40">{{ $post->published_at->format('M j, Y') }}</span>
                                    <span class="font-body text-xs text-navy/40">{{ $post->reading_time }} min read</span>
                                </div>
                                <h3 class="mb-2 font-heading text-xl/snug font-bold text-navy transition-colors duration-200 group-hover:text-purple">{{ $post->title }}</h3>
                                @if($post->excerpt)
                                    <p class="mb-4 line-clamp-2 font-body text-base leading-[1.7] text-navy/65 sm:text-sm">{{ Str::limit($post->excerpt, 130) }}</p>
                                @endif
                                <div class="flex items-center gap-2 border-t border-navy/5 pt-3">
                                    <div class="flex size-7 shrink-0 items-center justify-center rounded-full bg-purple/10 text-[10px] font-bold text-purple">
                                        {{ $post->author_initials }}
                                    </div>
                                    <span class="font-body text-xs font-medium text-navy/40">{{ $post->author_name }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="mt-10 text-center sm:hidden">
                    <a href="{{ route('blog.index') }}" class="inline-flex min-h-12 items-center font-body text-base font-semibold text-purple transition-colors hover:text-navy sm:text-sm">View all posts →</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Guides --}}
    <section class="bg-cream py-16 md:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            <div class="mb-12 text-center" data-animate>
                <span class="font-body text-sm font-semibold tracking-[0.15em] text-gold uppercase">Plan With Confidence</span>
                <h2 class="mt-2 font-heading text-3xl font-bold text-navy md:text-4xl">Your Guide to the Parks</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base/relaxed text-navy/50">Practical, regularly reviewed guidance for more comfortable and accessible Disney park days.</p>
            </div>

            @if ($latestGuides->isNotEmpty())
                <div class="grid gap-5 sm:grid-cols-2" data-animate>
                    @foreach ($latestGuides as $guide)
                        <a href="{{ route('guides.show', $guide) }}" class="group rounded-3xl border border-navy/5 bg-white p-7 shadow-sm transition-[transform,box-shadow] hover:-translate-y-1 hover:shadow-xl">
                            <span class="text-xs font-bold tracking-widest text-gold uppercase">{{ $guide->category_label }}</span>
                            <h3 class="mt-3 font-heading text-2xl font-bold text-navy transition-colors group-hover:text-purple">{{ $guide->title }}</h3>
                            @if ($guide->excerpt)
                                <p class="mt-3 text-base/relaxed text-navy/55">{{ $guide->excerpt }}</p>
                            @endif
                            <span class="mt-5 inline-flex min-h-12 items-center text-sm font-semibold text-purple">Read guide →</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="grid gap-5 sm:grid-cols-2" data-animate>
                    @foreach (\App\Models\Guide::CATEGORIES as $slug => $label)
                        <a href="{{ route('guides.index', ['category' => $slug]) }}" class="group rounded-3xl border border-navy/5 bg-white p-7 shadow-sm transition-[transform,box-shadow] hover:-translate-y-1 hover:shadow-xl">
                            <h3 class="font-heading text-2xl font-bold text-navy transition-colors group-hover:text-purple">{{ $label }}</h3>
                            <span class="mt-4 inline-flex min-h-12 items-center text-sm font-semibold text-gold">Explore guides →</span>
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-10 text-center">
                <a href="{{ route('guides.index') }}" class="inline-flex min-h-12 items-center rounded-full bg-navy px-7 py-3 text-base font-semibold text-white transition-colors hover:bg-purple">Browse all guides</a>
            </div>
        </div>
    </section>

    {{-- Podcast Section --}}
    <section class="relative bg-white py-16 md:py-24">
        <div class="mx-auto max-w-4xl px-4 sm:px-6">
            <div class="mb-12 flex items-end justify-between" data-animate>
                <div>
                    <span class="font-body text-sm font-semibold tracking-[0.15em] text-purple/65 uppercase">🎙️ Also Listen</span>
                    <h2 class="mt-2 font-heading text-3xl font-bold text-navy md:text-4xl">From the Podcast</h2>
                </div>
                <a href="{{ route('episodes.index') }}" class="hidden items-center gap-1 font-body text-sm font-semibold text-purple transition-colors hover:text-navy sm:inline-flex">
                    All episodes
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if($latestEpisodes->count())
                <div class="divide-y divide-navy/8">
                    @foreach($latestEpisodes as $episode)
                        <a href="{{ route('episodes.show', $episode) }}" class="group -mx-4 flex min-h-[56px] items-center gap-5 rounded-xl px-4 py-5 transition-[transform,background-color] duration-250 hover:translate-x-1 hover:bg-cream/50" data-animate data-stagger="{{ $loop->index }}">
                            <div class="relative flex size-12 shrink-0 items-center justify-center rounded-full bg-purple/10 transition-colors group-hover:bg-purple/20">
                                <span class="font-body text-sm font-bold text-purple transition-opacity group-hover:opacity-0">{{ $episode->episode_number }}</span>
                                <svg class="absolute size-5 text-purple opacity-0 transition-opacity group-hover:opacity-100" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-heading text-base font-semibold text-navy transition-colors group-hover:text-purple">{{ $episode->title }}</h3>
                                <p class="mt-0.5 font-body text-sm text-navy/40">
                                    {{ $episode->published_at->format('M j, Y') }}
                                    @if($episode->duration_seconds)
                                        <span class="mx-1.5">·</span>{{ $episode->formatted_duration }}
                                    @endif
                                </p>
                            </div>
                            <svg class="size-5 shrink-0 text-navy/25 transition-[transform,color] group-hover:translate-x-1 group-hover:text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
                <div class="mt-6 text-center sm:hidden">
                    <a href="{{ route('episodes.index') }}" class="inline-flex min-h-12 items-center font-body text-base font-semibold text-purple transition-colors hover:text-navy sm:text-sm">All episodes →</a>
                </div>
            @else
                @php
                    $homeWaveformHeights = ['h-[20%]', 'h-[35%]', 'h-[55%]', 'h-[75%]', 'h-full', 'h-[65%]', 'h-[45%]', 'h-[30%]'];
                @endphp
                <div class="relative overflow-hidden rounded-3xl border border-gold/12 bg-linear-to-br from-navy to-navy-light px-6 py-12 sm:px-10">
                    {{-- Ambient glow --}}
                    <div class="pointer-events-none absolute top-[-40%] right-[-20%] size-[300px] bg-[radial-gradient(circle,rgb(212_168_67/10%)_0%,transparent_60%)]"></div>

                    <div class="relative flex flex-wrap items-center gap-10">
                        {{-- Waveform visual --}}
                        <div class="shrink-0">
                            <div class="flex size-[100px] items-center justify-center rounded-[1.25rem] bg-linear-to-br from-gold to-gold-dark shadow-[0_10px_30px_rgb(212_168_67/25%)]">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1a1040" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                            </div>
                        </div>

                        <div class="min-w-60 flex-1">
                            <h3 class="mb-2 font-heading text-2xl font-bold text-cream">
                                Episode One Is Coming
                            </h3>
                            <p class="mb-5 font-body text-[0.95rem] leading-[1.7] text-cream/50">
                                Jeffrey &amp; Cassie are recording their first episode, an intro to who they are, why they started Mouse28, and what to expect from the show.
                            </p>
                            {{-- Faux waveform bars --}}
                            <div class="flex h-7 items-end gap-[3px] opacity-30">
                                @for($i = 0; $i < 40; $i++)
                                    <div class="w-[3px] rounded-sm bg-gold {{ $homeWaveformHeights[$i % count($homeWaveformHeights)] }}"></div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3" aria-label="Subscribe to the Mouse28 podcast">
                @foreach ($podcast->distributionLinks() as $link)
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-12 items-center rounded-full border border-purple/15 bg-purple/5 px-5 py-3 text-sm font-semibold text-purple transition-colors hover:border-purple/30 hover:bg-purple/10">{{ $link['label'] }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Meet the Family --}}
    <section class="relative overflow-hidden bg-cream py-16 md:py-24">
        <div class="mx-auto max-w-5xl px-4 sm:px-6">
            <div class="flex flex-col items-center gap-10 md:flex-row md:gap-14" data-animate>
                {{-- Photo --}}
                <div class="w-full shrink-0 md:w-2/5">
                    <div class="relative">
                        <div class="overflow-hidden rounded-2xl border-[3px] border-gold/20 shadow-xl">
                            <img src="/images/meet-jeffrey-and-cassie.jpg" alt="Jeffrey and Cassie Davidson at Disney" width="1024" height="1536" loading="lazy" decoding="async" class="aspect-4/5 h-auto w-full object-cover">
                        </div>
                        {{-- Decorative corner accent --}}
                        <div class="absolute -right-3 -bottom-3 hidden size-24 rounded-br-2xl border-r-2 border-b-2 border-gold/25 md:block"></div>
                    </div>
                </div>

                {{-- Text --}}
                <div class="flex-1">
                    <span class="font-body text-sm font-semibold tracking-[0.15em] text-gold uppercase">The Family Behind Mouse28</span>
                    <h2 class="mt-2 mb-4 font-heading text-3xl font-bold text-navy md:text-4xl">Meet Jeffrey & Cassie</h2>
                    <div class="space-y-4 font-body leading-relaxed text-navy/60">
                        <p>We're a Florida family who visits Disney every single week with our daughter Viola. She's autistic and nonverbal, and she's taught us to experience the parks in ways we never expected.</p>
                        <p>Mouse28 is where we share what we've learned — the accessibility tips nobody tells you, the sensory-friendly spots, the real moments that make it all worth it. Two voices, no filter, lots of maple popcorn.</p>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <a href="{{ route('about') }}" class="inline-flex items-center gap-2 rounded-full bg-navy px-6 py-3 font-body text-sm font-semibold text-white transition-[transform,background-color,box-shadow] duration-300 hover:-translate-y-0.5 hover:bg-navy-light hover:shadow-lg">
                            Our Full Story
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('contact.show') }}" class="inline-flex min-h-12 items-center gap-1.5 font-body text-base font-semibold text-purple transition-colors hover:text-navy sm:text-sm">
                            Say hello →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- The Story in Numbers --}}
    <section class="relative bg-white">
        <div class="h-px bg-linear-to-r from-transparent via-gold/20 to-transparent"></div>
        <div class="mx-auto max-w-5xl px-4 py-18 sm:px-6">
            <div class="mb-10 text-center">
                <span class="font-body text-[0.7rem] font-semibold tracking-[0.15em] text-gold uppercase">The Family Behind Mouse28</span>
            </div>
            <div class="grid grid-cols-2 gap-6 md:grid-cols-4 md:gap-8">
                <div class="text-center">
                    <div class="font-heading text-[clamp(2.5rem,5vw,3.5rem)] leading-none font-extrabold text-navy">20</div>
                    <p class="mt-2 font-body text-base/6 text-navy/45 sm:text-[0.8rem]/6">Minutes from<br>the Magic Kingdom</p>
                </div>
                <div class="text-center">
                    <div class="font-heading text-[clamp(2.5rem,5vw,3.5rem)] leading-none font-extrabold text-navy">2</div>
                    <p class="mt-2 font-body text-base/6 text-navy/45 sm:text-[0.8rem]/6">Voices, one mic,<br>zero filter</p>
                </div>
                <div class="text-center">
                    <div class="font-heading text-[clamp(2.5rem,5vw,3.5rem)] leading-none font-extrabold text-gold">52</div>
                    <p class="mt-2 font-body text-base/6 text-navy/45 sm:text-[0.8rem]/6">Park days a year<br>(at least)</p>
                </div>
                <div class="text-center">
                    <div class="font-heading text-[clamp(2.5rem,5vw,3.5rem)] leading-none font-extrabold text-navy">∞</div>
                    <p class="mt-2 font-body text-base/6 text-navy/45 sm:text-[0.8rem]/6">Buckets of maple popcorn<br>(and counting)</p>
                </div>
            </div>
        </div>
        <div class="h-px bg-linear-to-r from-transparent via-gold/20 to-transparent"></div>
    </section>

    {{-- Newsletter CTA --}}
    <section id="newsletter" class="relative overflow-hidden bg-linear-to-br from-navy via-navy-light to-navy py-16 md:py-24">
        @php
            $newsletterHasFeedback = $errors->newsletter->isNotEmpty() || session('newsletter_error');
        @endphp
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <span class="sparkle absolute top-[20%] left-[15%] text-sm text-gold/30">✦</span>
            <span class="sparkle-delay absolute right-[20%] bottom-[25%] text-lg text-gold/20">✧</span>
            <span class="sparkle-delay-2 absolute top-[50%] left-[70%] text-xs text-gold/15">✦</span>
        </div>

        <div class="relative z-10 mx-auto max-w-2xl px-4 text-center sm:px-6" data-animate>
            <h2 class="mb-4 font-heading text-3xl font-bold text-white md:text-4xl">Stay in the Loop</h2>
            <p class="mb-8 font-body text-lg leading-[1.7] text-white/55">New posts, podcast episodes, and park tips straight to your inbox. No spam, just pixie dust.</p>
            @if(session('newsletter_success'))
                <div class="mx-auto max-w-md rounded-xl border border-green-400/30 bg-green-500/20 px-6 py-4">
                    <p class="font-body text-sm text-green-300">✨ You're subscribed! We'll send you the good stuff.</p>
                </div>
            @elseif(session('newsletter_error'))
                <div class="mx-auto mb-4 max-w-md rounded-xl border border-red-400/30 bg-red-500/20 px-6 py-4">
                    <p class="font-body text-sm text-red-300">{{ session('newsletter_error') }}</p>
                </div>
                <form action="{{ route('newsletter.store') }}" method="POST" class="mx-auto max-w-md space-y-3">
                    @csrf
                    <x-newsletter-protection honeypot-id="home-newsletter-website-error" />
                    <div class="flex flex-col gap-3 sm:flex-row">
                    <label for="home-newsletter-email-error" class="sr-only">Email address</label>
                    <input id="home-newsletter-email-error" type="email" name="email" value="{{ $newsletterHasFeedback ? old('email') : '' }}" placeholder="your@email.com" autocomplete="email" required
                        @error('email', 'newsletter') aria-invalid="true" aria-describedby="home-newsletter-email-error-message" @enderror
                        class="newsletter-input min-h-[48px] flex-1 rounded-full border border-white/20 bg-white/10 px-5 py-3.5 font-body text-base text-white transition-[border-color,box-shadow] duration-300 placeholder:text-white/35 focus:border-gold/40 focus:ring-2 focus:ring-gold/60 focus:outline-none sm:text-sm">
                    <button type="submit" class="min-h-[48px] rounded-full bg-gold px-7 py-3.5 font-body text-base font-semibold text-navy transition-[transform,background-color,box-shadow] duration-300 hover:-translate-y-0.5 hover:scale-105 hover:bg-gold-light hover:shadow-lg hover:shadow-gold/30 active:scale-95 sm:text-sm">
                        Subscribe
                    </button>
                    </div>
                    @error('email', 'newsletter')
                        <p id="home-newsletter-email-error-message" role="alert" class="text-left text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </form>
            @else
                <form action="{{ route('newsletter.store') }}" method="POST" class="mx-auto max-w-md space-y-3">
                    @csrf
                    <x-newsletter-protection honeypot-id="home-newsletter-website" />
                    <div class="flex flex-col gap-3 sm:flex-row">
                    <label for="home-newsletter-email" class="sr-only">Email address</label>
                    <input id="home-newsletter-email" type="email" name="email" value="{{ $newsletterHasFeedback ? old('email') : '' }}" placeholder="your@email.com" autocomplete="email" required
                        @error('email', 'newsletter') aria-invalid="true" aria-describedby="home-newsletter-email-message" @enderror
                        class="newsletter-input min-h-[48px] flex-1 rounded-full border border-white/20 bg-white/10 px-5 py-3.5 font-body text-base text-white transition-[border-color,box-shadow] duration-300 placeholder:text-white/35 focus:border-gold/40 focus:ring-2 focus:ring-gold/60 focus:outline-none sm:text-sm">
                    <button type="submit" class="min-h-[48px] rounded-full bg-gold px-7 py-3.5 font-body text-base font-semibold text-navy transition-[transform,background-color,box-shadow] duration-300 hover:-translate-y-0.5 hover:scale-105 hover:bg-gold-light hover:shadow-lg hover:shadow-gold/30 active:scale-95 sm:text-sm">
                        Subscribe
                    </button>
                    </div>
                    @error('email', 'newsletter')
                        <p id="home-newsletter-email-message" role="alert" class="text-left text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </form>
            @endif
            <div class="mt-10 flex flex-wrap items-center justify-center gap-6 border-t border-white/10 pt-8">
                @foreach ($podcast->distributionLinks() as $link)
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-12 items-center font-body text-base text-white/55 transition-colors hover:text-gold sm:text-sm">{{ $link['label'] }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Scroll animation observer --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            document.documentElement.classList.add('js-animate');

            // Staggered scroll animations
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        const stagger = e.target.dataset.stagger;
                        const delay = stagger ? parseInt(stagger) * 120 : 0;
                        setTimeout(() => e.target.classList.add('is-visible'), delay);
                        obs.unobserve(e.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });
            document.querySelectorAll('[data-animate]').forEach(el => obs.observe(el));

            // Subtle hero parallax on scroll
            const heroPhoto = document.querySelector('.hero-split-photo img');
            if (heroPhoto) {
                let ticking = false;
                window.addEventListener('scroll', function() {
                    if (!ticking) {
                        requestAnimationFrame(function() {
                            const scroll = window.scrollY;
                            if (scroll < 800) {
                                heroPhoto.style.transform = 'translateY(' + (scroll * 0.15) + 'px) scale(1.05)';
                            }
                            ticking = false;
                        });
                        ticking = true;
                    }
                }, { passive: true });
            }
        });
    </script>
</x-layouts.app>
