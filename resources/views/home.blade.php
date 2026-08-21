@extends('layouts.app')

@section('title', 'Mouse28 — Disney Parks Through Different Eyes')

@section('content')
    {{-- Hero Section — Split Identity --}}
    <section class="hero-split">
        {{-- Left: Text --}}
        <div class="hero-split-text">
            <div class="pointer-events-none absolute right-[-20%] bottom-[-30%] size-[400px] bg-[radial-gradient(circle,rgb(212_168_67/6%)_0%,transparent_60%)]"></div>
            <span class="sparkle absolute top-[15%] left-[10%] text-gold/25 text-[10px]">✦</span>
            <span class="sparkle-delay absolute bottom-[20%] right-[15%] text-gold/15 text-sm">✧</span>

            <div class="relative z-10 max-w-lg ml-auto">
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
                    <a href="/blog" class="cta-primary inline-flex min-h-12 items-center rounded-full bg-gold px-7 py-3.5 font-body text-base font-semibold text-navy shadow-lg shadow-gold/20 transition-all duration-300 hover:-translate-y-1 hover:scale-105 hover:bg-gold-light hover:shadow-gold/50 sm:text-sm">
                        Read Our Blog
                    </a>
                    <a href="/episodes" class="inline-flex min-h-12 items-center gap-2 font-body text-base font-medium text-cream/45 transition-colors duration-200 hover:text-gold sm:text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
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

    {{-- What We Cover — Ticket Stubs --}}
    <section class="py-16 md:py-24 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-animate>
                <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Your Guide to the Parks</h2>
            </div>
            <div class="grid sm:grid-cols-2 gap-5" data-animate>
                {{-- Ticket: Accessibility --}}
                <a href="/blog" class="ticket bg-linear-to-br from-[#e88d9a] to-[#d4627a] text-[#fce4e8]">
                    <div class="p-7 pb-10 pr-16 relative">
                        <div class="mb-1 font-body text-[10px] font-bold tracking-[0.2em] text-white/50 uppercase">Mouse28 · Admit One</div>
                        <h3 class="font-heading text-2xl font-bold text-white mb-2">Accessibility</h3>
                        <p class="font-body text-base/relaxed text-white/70 sm:text-sm/relaxed">DAS tips, sensory guides, and honest advice for neurodivergent families navigating the parks.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                        </div>
                    </div>
                    <div class="ticket-hole"></div>
                    <div class="ticket-tear text-white"></div>
                </a>

                {{-- Ticket: Park Strategy --}}
                <a href="/blog" class="ticket bg-linear-to-br from-purple-light to-purple text-[#ede4f7]">
                    <div class="p-7 pb-10 pr-16 relative">
                        <div class="mb-1 font-body text-[10px] font-bold tracking-[0.2em] text-white/50 uppercase">Mouse28 · Admit One</div>
                        <h3 class="font-heading text-2xl font-bold text-white mb-2">Park Strategy</h3>
                        <p class="font-body text-base/relaxed text-white/70 sm:text-sm/relaxed">Ride tips, rope drop plans, and weekly-tested strategies from locals who know every shortcut.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                        </div>
                    </div>
                    <div class="ticket-hole"></div>
                    <div class="ticket-tear text-white"></div>
                </a>

                {{-- Ticket: Food & Reviews --}}
                <a href="/blog" class="ticket bg-linear-to-br from-gold-light to-gold text-[#fdf3dc]">
                    <div class="p-7 pb-10 pr-16 relative">
                        <div class="mb-1 font-body text-[10px] font-bold tracking-[0.2em] text-navy/40 uppercase">Mouse28 · Admit One</div>
                        <h3 class="font-heading text-2xl font-bold text-navy mb-2">Food & Reviews</h3>
                        <p class="font-body text-base/relaxed text-navy/60 sm:text-sm/relaxed">Honest reviews of restaurants, snacks, and resorts — including diabetic-friendly options.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="bg-navy/15 text-navy text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                        </div>
                    </div>
                    <div class="ticket-hole"></div>
                    <div class="ticket-tear text-white"></div>
                </a>

                {{-- Ticket: Family Stories --}}
                <a href="/blog" class="ticket bg-linear-to-br from-[#5ba4ad] to-[#4a90a4] text-[#ddf0f5]">
                    <div class="p-7 pb-10 pr-16 relative">
                        <div class="mb-1 font-body text-[10px] font-bold tracking-[0.2em] text-white/50 uppercase">Mouse28 · Admit One</div>
                        <h3 class="font-heading text-2xl font-bold text-white mb-2">Family Stories</h3>
                        <p class="font-body text-base/relaxed text-white/70 sm:text-sm/relaxed">Real moments from our weekly trips — the magical, the messy, and everything in between.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                        </div>
                    </div>
                    <div class="ticket-hole"></div>
                    <div class="ticket-tear text-white"></div>
                </a>
            </div>
        </div>
    </section>

    {{-- Thin gold line divider --}}
    <div class="h-px bg-linear-to-r from-transparent via-gold/25 to-transparent"></div>

{{-- Featured Post --}}
    @if($featuredPost)
        <section class="py-16 md:py-24 bg-cream" data-animate>
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-10">
                    <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">Latest from the Blog</span>
                </div>
                <a href="/blog/{{ $featuredPost->slug }}" class="group block overflow-hidden rounded-2xl bg-linear-to-br from-navy to-navy-light shadow-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="flex flex-col md:flex-row">
                        {{-- Left: Cover image or gradient --}}
                        <div class="relative md:w-2/5 min-h-[220px] md:min-h-[320px] overflow-hidden flex-shrink-0">
                            @if($featuredPost->cover_image_url)
                                <img src="{{ $featuredPost->cover_image_url }}" alt="{{ $featuredPost->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute inset-y-0 right-0 left-1/2 md:bg-linear-to-r md:from-transparent md:via-transparent md:to-navy/40"></div>
                            @else
                                <div class="absolute inset-0 flex items-center justify-center bg-linear-to-br from-purple/40 to-navy">
                                    <div class="w-20 h-20 rounded-2xl bg-white/10 flex items-center justify-center border border-white/10">
                                        <svg class="w-10 h-10 text-gold/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Right: Content --}}
                        <div class="flex-1 p-8 md:p-10 lg:p-12 flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-5 flex-wrap">
                                <span class="bg-gold/20 text-gold text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">Featured</span>
                                @if($featuredPost->category)
                                    <span class="bg-white/10 text-white/70 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">{{ $featuredPost->category_label }}</span>
                                @endif
                                <span class="text-white/30 text-xs">{{ $featuredPost->reading_time }} min read</span>
                            </div>
                            <h2 class="font-heading text-2xl md:text-3xl lg:text-4xl font-bold text-white leading-snug mb-4 group-hover:text-gold transition-colors duration-300">
                                {{ $featuredPost->title }}
                            </h2>
                            @if($featuredPost->excerpt)
                                <p class="text-white/55 text-base leading-relaxed line-clamp-3 mb-6">{{ $featuredPost->excerpt }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-auto pt-6 border-t border-white/10">
                                <div class="flex items-center gap-3">
                                    <div class="flex size-9 items-center justify-center rounded-full border border-gold/20 bg-linear-to-br from-gold/25 to-purple/15 font-heading text-[10px] font-bold text-gold">
                                        {{ $featuredPost->author_initials }}
                                    </div>
                                    <div>
                                        <p class="text-white text-sm font-semibold">{{ $featuredPost->author_name }}</p>
                                        <p class="text-white/40 text-xs">{{ $featuredPost->published_at->format('F j, Y') }}</p>
                                    </div>
                                </div>
                                <span class="hidden sm:inline-flex items-center gap-1.5 text-gold text-sm font-semibold group-hover:gap-2.5 transition-all">
                                    Read Article
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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
        <section class="py-16 md:py-24 bg-white">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="flex items-end justify-between mb-12" data-animate>
                    <div>
                        <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">Latest Stories</span>
                        <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">From the Blog</h2>
                    </div>
                    <a href="/blog" class="hidden sm:inline-flex items-center gap-1 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                        View all
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($latestPosts as $post)
                        <a href="/blog/{{ $post->slug }}" class="post-card group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 border border-navy/5 relative" data-animate data-stagger="{{ $loop->index }}">
                            <div class="relative overflow-hidden card-shimmer">
                                @if($post->cover_image_url)
                                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="card-img w-full h-52 object-cover">
                                    <div class="card-overlay absolute inset-0 bg-linear-to-t from-purple/20 to-transparent"></div>
                                @else
                                    <div class="flex h-52 w-full items-center justify-center bg-linear-to-br from-purple/10 to-gold/10">
                                        <span class="text-3xl">✨</span>
                                    </div>
                                @endif
                                @if($post->category)
                                    <span class="absolute top-3 left-3 bg-navy/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider font-body">{{ $post->category_label }}</span>
                                @endif
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-navy/40 text-xs font-body">{{ $post->published_at->format('M j, Y') }}</span>
                                    <span class="text-navy/40 text-xs font-body">{{ $post->reading_time }} min read</span>
                                </div>
                                <h3 class="font-heading text-xl font-bold text-navy group-hover:text-purple transition-colors duration-200 mb-2 leading-snug">{{ $post->title }}</h3>
                                @if($post->excerpt)
                                    <p class="mb-4 line-clamp-2 font-body text-base leading-[1.7] text-navy/65 sm:text-sm">{{ Str::limit($post->excerpt, 130) }}</p>
                                @endif
                                <div class="flex items-center gap-2 pt-3 border-t border-navy/5">
                                    <div class="w-7 h-7 rounded-full bg-purple/10 flex items-center justify-center text-purple text-[10px] font-bold flex-shrink-0">
                                        {{ $post->author_initials }}
                                    </div>
                                    <span class="text-navy/40 text-xs font-medium font-body">{{ $post->author_name }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-10 sm:hidden">
                    <a href="/blog" class="inline-flex min-h-12 items-center font-body text-base font-semibold text-purple transition-colors hover:text-navy sm:text-sm">View all posts →</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Podcast Section --}}
    <section class="py-16 md:py-24 bg-white relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-end justify-between mb-12" data-animate>
                <div>
                    <span class="text-purple/65 text-sm font-semibold tracking-[0.15em] uppercase font-body">🎙️ Also Listen</span>
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">From the Podcast</h2>
                </div>
                <a href="/episodes" class="hidden sm:inline-flex items-center gap-1 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                    All episodes
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if($latestEpisodes->count())
                <div class="divide-y divide-navy/8">
                    @foreach($latestEpisodes as $episode)
                        <a href="/episodes/{{ $episode->slug }}" class="group flex items-center gap-5 py-5 min-h-[56px] hover:bg-cream/50 -mx-4 px-4 rounded-xl transition-all duration-250 hover:translate-x-1" data-animate data-stagger="{{ $loop->index }}">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-purple/10 flex items-center justify-center group-hover:bg-purple/20 transition-colors relative">
                                <span class="text-purple font-bold text-sm font-body group-hover:opacity-0 transition-opacity">{{ $episode->episode_number }}</span>
                                <svg class="w-5 h-5 text-purple absolute opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-heading text-base font-semibold text-navy group-hover:text-purple transition-colors truncate">{{ $episode->title }}</h3>
                                <p class="text-navy/40 text-sm font-body mt-0.5">
                                    {{ $episode->published_at->format('M j, Y') }}
                                    @if($episode->duration_seconds)
                                        <span class="mx-1.5">·</span>{{ $episode->formatted_duration }}
                                    @endif
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-navy/25 group-hover:text-purple group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-6 sm:hidden">
                    <a href="/episodes" class="inline-flex min-h-12 items-center font-body text-base font-semibold text-purple transition-colors hover:text-navy sm:text-sm">All episodes →</a>
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
        </div>
    </section>

    {{-- Meet the Family --}}
    <section class="py-16 md:py-24 bg-cream relative overflow-hidden">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row items-center gap-10 md:gap-14" data-animate>
                {{-- Photo --}}
                <div class="flex-shrink-0 w-full md:w-2/5">
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
                    <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">The Family Behind Mouse28</span>
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2 mb-4">Meet Jeffrey & Cassie</h2>
                    <div class="space-y-4 text-navy/60 font-body leading-relaxed">
                        <p>We're a Florida family who visits Disney every single week with our daughter Viola. She's autistic and nonverbal, and she's taught us to experience the parks in ways we never expected.</p>
                        <p>Mouse28 is where we share what we've learned — the accessibility tips nobody tells you, the sensory-friendly spots, the real moments that make it all worth it. Two voices, no filter, lots of maple popcorn.</p>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center gap-4">
                        <a href="/about" class="inline-flex items-center gap-2 bg-navy hover:bg-navy-light text-white font-semibold px-6 py-3 rounded-full text-sm font-body transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5">
                            Our Full Story
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                        <a href="/contact" class="inline-flex min-h-12 items-center gap-1.5 font-body text-base font-semibold text-purple transition-colors hover:text-navy sm:text-sm">
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
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
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[20%] left-[15%] text-gold/30 text-sm">✦</span>
            <span class="sparkle-delay absolute bottom-[25%] right-[20%] text-gold/20 text-lg">✧</span>
            <span class="sparkle-delay-2 absolute top-[50%] left-[70%] text-gold/15 text-xs">✦</span>
        </div>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center relative z-10" data-animate>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-white mb-4">Stay in the Loop</h2>
            <p class="text-white/55 text-lg mb-8 leading-[1.7] font-body">New posts, podcast episodes, and park tips straight to your inbox. No spam, just pixie dust.</p>
            @if(session('newsletter_success'))
                <div class="bg-green-500/20 border border-green-400/30 rounded-xl px-6 py-4 max-w-md mx-auto">
                    <p class="text-green-300 font-body text-sm">✨ You're subscribed! We'll send you the good stuff.</p>
                </div>
            @elseif(session('newsletter_error'))
                <div class="bg-red-500/20 border border-red-400/30 rounded-xl px-6 py-4 max-w-md mx-auto mb-4">
                    <p class="text-red-300 font-body text-sm">{{ session('newsletter_error') }}</p>
                </div>
                <form action="/newsletter" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    @csrf
                    <label for="home-newsletter-email-error" class="sr-only">Email address</label>
                    <input id="home-newsletter-email-error" type="email" name="email" placeholder="your@email.com" autocomplete="email" required
                        class="newsletter-input flex-1 px-5 py-3.5 min-h-[48px] rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-gold/60 focus:border-gold/40 text-base sm:text-sm font-body transition-all duration-300">
                    <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold px-7 py-3.5 min-h-[48px] rounded-full transition-all duration-300 text-base sm:text-sm font-body hover:shadow-lg hover:shadow-gold/30 hover:-translate-y-0.5 hover:scale-105 active:scale-95">
                        Subscribe
                    </button>
                </form>
            @else
                <form action="/newsletter" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    @csrf
                    <label for="home-newsletter-email" class="sr-only">Email address</label>
                    <input id="home-newsletter-email" type="email" name="email" placeholder="your@email.com" autocomplete="email" required
                        class="newsletter-input flex-1 px-5 py-3.5 min-h-[48px] rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-gold/60 focus:border-gold/40 text-base sm:text-sm font-body transition-all duration-300">
                    <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold px-7 py-3.5 min-h-[48px] rounded-full transition-all duration-300 text-base sm:text-sm font-body hover:shadow-lg hover:shadow-gold/30 hover:-translate-y-0.5 hover:scale-105 active:scale-95">
                        Subscribe
                    </button>
                </form>
            @endif
            <div class="mt-10 flex items-center justify-center gap-6 border-t border-white/10 pt-8">
                <span class="font-body text-base text-white/40 sm:text-sm">🎧 Apple Podcasts · Soon</span>
                <span class="font-body text-base text-white/40 sm:text-sm">💚 Spotify · Soon</span>
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
@endsection
