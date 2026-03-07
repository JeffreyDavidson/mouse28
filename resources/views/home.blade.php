@extends('layouts.app')

@section('title', 'Mouse28 — Disney Parks Through Different Eyes')

@section('content')
    <style>
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @keyframes gentlePulse {
            0%, 100% { box-shadow: 0 4px 15px rgba(212,168,67,0.2); }
            50% { box-shadow: 0 4px 25px rgba(212,168,67,0.45); }
        }
        .cta-primary {
            animation: gentlePulse 3s ease-in-out infinite;
        }
        .cta-primary:hover {
            animation: none;
        }
        .card-shimmer::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, transparent 40%, rgba(212,168,67,0.08) 50%, transparent 60%);
            transform: translateX(-100%);
            transition: none;
        }
        .group:hover .card-shimmer::after {
            animation: shimmer 0.8s ease forwards;
        }
        .post-card {
            border-color: rgba(26,16,64,0.05);
            transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .post-card:hover {
            border-color: rgba(212,168,67,0.3);
            background: linear-gradient(to bottom, #fff, #fffdf8);
        }
        .post-card .card-img {
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .post-card:hover .card-img {
            transform: scale(1.07);
        }
        .post-card .card-overlay {
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        .post-card:hover .card-overlay {
            opacity: 1;
        }
        .featured-link .featured-overlay {
            background: linear-gradient(to top, rgba(26,16,64,0.95), rgba(26,16,64,0.7), rgba(26,16,64,0.2));
            transition: background 0.5s ease;
        }
        .featured-link:hover .featured-overlay {
            background: linear-gradient(to top, rgba(26,16,64,0.95), rgba(45,27,105,0.75), rgba(91,62,158,0.25));
        }
        .newsletter-input:focus {
            box-shadow: 0 0 0 3px rgba(212,168,67,0.25), 0 0 20px rgba(212,168,67,0.1);
        }
        [data-animate] { opacity: 1; transform: translateY(0); }
        .js-animate [data-animate] {
            opacity: 0; transform: translateY(24px);
            transition: opacity 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .js-animate [data-animate].is-visible { opacity: 1; transform: translateY(0); }
        .wave-divider svg { display: block; filter: none; }

        /* Ticket stubs */
        .ticket {
            position: relative;
            border-radius: 1rem;
            overflow: hidden;
            transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.4s ease;
            display: block;
            text-decoration: none;
        }
        .ticket:hover {
            transform: translateY(-6px) rotate(-1deg);
            box-shadow: 0 20px 40px rgba(26,16,64,0.15);
        }
        .ticket-tear {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 20px;
            background: radial-gradient(circle at 10px -5px, transparent 12px, currentColor 12px);
            background-size: 20px 20px;
            background-position: -5px bottom;
        }
        .ticket-hole {
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px dashed currentColor;
            opacity: 0.3;
        }
        .ticket::before {
            content: '';
            position: absolute;
            right: 50px;
            top: 0;
            bottom: 0;
            width: 1px;
            background: repeating-linear-gradient(to bottom, transparent, transparent 4px, currentColor 4px, currentColor 8px);
            opacity: 0.15;
        }

        /* Hero split */
        .hero-split {
            display: grid;
            grid-template-columns: 1fr;
            min-height: 0;
        }
        @media (min-width: 768px) {
            .hero-split {
                grid-template-columns: 1fr 1fr;
                min-height: min(70vh, 640px);
            }
        }
        .hero-split-text {
            background: linear-gradient(135deg, #1a1040 0%, #2d1b69 100%);
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 4rem 2rem;
        }
        @media (min-width: 768px) {
            .hero-split-text { padding: 4rem 3rem 4rem 0; }
        }
        .hero-split-photo {
            position: relative;
            min-height: 300px;
            overflow: hidden;
        }
        .hero-split-photo img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center 30%;
        }
        /* Diagonal edge on desktop */
        @media (min-width: 768px) {
            .hero-split-photo::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                bottom: 0;
                width: 80px;
                background: linear-gradient(135deg, #1a1040 0%, #2d1b69 100%);
                clip-path: polygon(0 0, 100% 0, 0 100%);
                z-index: 2;
            }
        }
        /* Gradient fade on mobile */
        @media (max-width: 767px) {
            .hero-split-photo::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0;
                height: 60px;
                background: linear-gradient(to bottom, #2d1b69, transparent);
                z-index: 2;
            }
        }
    </style>

        {{-- Hero Section — Split Identity --}}
    <section class="hero-split">
        {{-- Left: Text --}}
        <div class="hero-split-text">
            <div style="position: absolute; bottom: -30%; right: -20%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(212, 168, 67, 0.06) 0%, transparent 60%); pointer-events: none;"></div>
            <span class="sparkle absolute top-[15%] left-[10%] text-gold/25 text-[10px]">✦</span>
            <span class="sparkle-delay absolute bottom-[20%] right-[15%] text-gold/15 text-sm">✧</span>

            <div class="relative z-10 max-w-lg ml-auto">
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(212, 168, 67, 0.3); border-radius: 9999px; padding: 0.35rem 1rem; margin-bottom: 1.5rem;">
                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #d4a843;"></span>
                    <span class="font-body" style="font-size: 0.7rem; color: #d4a843; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 600;">Autism Family · Disney Every Week</span>
                </div>

                <h1 class="font-heading font-bold text-white" style="font-size: clamp(2.25rem, 4vw, 3.5rem); line-height: 1.08; margin-bottom: 1.25rem;">
                    Disney Parks<br>Through<br>
                    <span class="text-gold">Different Eyes</span>
                </h1>

                <p class="font-body" style="color: rgba(254, 249, 239, 0.5); font-size: 1rem; line-height: 1.75; margin-bottom: 2rem;">
                    Accessibility tips, sensory-friendly recommendations, and real stories from a family who visits Disney every single week with our autistic daughter.
                </p>

                <div class="flex flex-wrap items-center gap-4">
                    <a href="/blog" class="cta-primary bg-gold hover:bg-gold-light text-navy font-semibold px-7 py-3.5 rounded-full shadow-lg shadow-gold/20 hover:shadow-gold/50 hover:scale-105 transition-all duration-300 hover:-translate-y-1 text-sm font-body inline-flex items-center">
                        Read Our Blog
                    </a>
                    <a href="/episodes" class="font-body inline-flex items-center gap-2 text-sm font-medium transition-colors duration-200" style="color: rgba(254, 249, 239, 0.45);" onmouseenter="this.style.color='#d4a843'" onmouseleave="this.style.color='rgba(254, 249, 239, 0.45)'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        Listen to the podcast
                    </a>
                </div>
            </div>
        </div>

        {{-- Right: Photo --}}
        <div class="hero-split-photo">
            <img src="/images/hero-family.jpg" alt="Jeffrey and Cassie Davidson on Kilimanjaro Safaris at Disney's Animal Kingdom">
            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, rgba(26,16,64,0.3), transparent); z-index: 1;"></div>
        </div>
    </section>

    {{-- Gold divider --}}
    <div style="height: 4px; background: linear-gradient(90deg, #d4a843, #b8922e, #d4a843);"></div>

    {{-- What We Cover — Ticket Stubs --}}
    <section class="py-16 md:py-24 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12" data-animate>
                <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Your Guide to the Parks</h2>
            </div>
            <div class="grid sm:grid-cols-2 gap-5" data-animate>
                {{-- Ticket: Accessibility --}}
                <a href="/blog" class="ticket" style="background: linear-gradient(135deg, #e88d9a, #d4627a); color: #fce4e8;">
                    <div class="p-7 pb-10 pr-16 relative">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] font-body mb-1" style="color: rgba(255,255,255,0.5);">Mouse28 · Admit One</div>
                        <h3 class="font-heading text-2xl font-bold text-white mb-2">Accessibility</h3>
                        <p class="font-body text-sm leading-relaxed" style="color: rgba(255,255,255,0.7);">DAS tips, sensory guides, and honest advice for neurodivergent families navigating the parks.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                        </div>
                    </div>
                    <div class="ticket-hole" style="color: #fce4e8;"></div>
                    <div class="ticket-tear" style="color: #ffffff;"></div>
                </a>

                {{-- Ticket: Park Strategy --}}
                <a href="/blog" class="ticket" style="background: linear-gradient(135deg, #7b5eb5, #5b3e9e); color: #ede4f7;">
                    <div class="p-7 pb-10 pr-16 relative">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] font-body mb-1" style="color: rgba(255,255,255,0.5);">Mouse28 · Admit One</div>
                        <h3 class="font-heading text-2xl font-bold text-white mb-2">Park Strategy</h3>
                        <p class="font-body text-sm leading-relaxed" style="color: rgba(255,255,255,0.7);">Ride tips, rope drop plans, and weekly-tested strategies from locals who know every shortcut.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                        </div>
                    </div>
                    <div class="ticket-hole" style="color: #ede4f7;"></div>
                    <div class="ticket-tear" style="color: #ffffff;"></div>
                </a>

                {{-- Ticket: Food & Reviews --}}
                <a href="/blog" class="ticket" style="background: linear-gradient(135deg, #f0c75e, #d4a843); color: #fdf3dc;">
                    <div class="p-7 pb-10 pr-16 relative">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] font-body mb-1" style="color: rgba(26,16,64,0.4);">Mouse28 · Admit One</div>
                        <h3 class="font-heading text-2xl font-bold text-navy mb-2">Food & Reviews</h3>
                        <p class="font-body text-sm leading-relaxed" style="color: rgba(26,16,64,0.6);">Honest reviews of restaurants, snacks, and resorts — including diabetic-friendly options.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="bg-navy/15 text-navy text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                        </div>
                    </div>
                    <div class="ticket-hole" style="color: #fdf3dc;"></div>
                    <div class="ticket-tear" style="color: #ffffff;"></div>
                </a>

                {{-- Ticket: Family Stories --}}
                <a href="/blog" class="ticket" style="background: linear-gradient(135deg, #5ba4ad, #4a90a4); color: #ddf0f5;">
                    <div class="p-7 pb-10 pr-16 relative">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] font-body mb-1" style="color: rgba(255,255,255,0.5);">Mouse28 · Admit One</div>
                        <h3 class="font-heading text-2xl font-bold text-white mb-2">Family Stories</h3>
                        <p class="font-body text-sm leading-relaxed" style="color: rgba(255,255,255,0.7);">Real moments from our weekly trips — the magical, the messy, and everything in between.</p>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                        </div>
                    </div>
                    <div class="ticket-hole" style="color: #ddf0f5;"></div>
                    <div class="ticket-tear" style="color: #ffffff;"></div>
                </a>
            </div>
        </div>
    </section>

    {{-- Thin gold line divider --}}
    <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(212,168,67,0.25), transparent);"></div>

{{-- Featured Post --}}
    @if($featuredPost)
        <section class="py-16 md:py-20 bg-cream" data-animate>
            <div class="max-w-5xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-10">
                    <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">Latest from the Blog</span>
                </div>
                <a href="/blog/{{ $featuredPost->slug }}" class="group block bg-gradient-to-br from-navy to-navy-light rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex flex-col md:flex-row">
                        {{-- Left: Cover image or gradient --}}
                        <div class="relative md:w-2/5 min-h-[220px] md:min-h-[320px] overflow-hidden flex-shrink-0">
                            @if($featuredPost->cover_image_url)
                                <img src="{{ $featuredPost->cover_image_url }}" alt="{{ $featuredPost->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent to-navy/30 md:bg-gradient-to-r md:from-transparent md:to-navy"></div>
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-purple/40 to-navy flex items-center justify-center">
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
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gold/25 to-purple/15 flex items-center justify-center text-gold text-[10px] font-bold font-heading border border-gold/20">
                                        {{ collect(explode(' ', $featuredPost->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
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
                                    <div class="card-overlay absolute inset-0 bg-gradient-to-t from-purple/20 to-transparent"></div>
                                @else
                                    <div class="w-full h-52 bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center">
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
                                    <p class="text-navy/65 text-sm leading-[1.7] line-clamp-2 font-body mb-4">{{ Str::limit($post->excerpt, 130) }}</p>
                                @endif
                                <div class="flex items-center gap-2 pt-3 border-t border-navy/5">
                                    <div class="w-7 h-7 rounded-full bg-purple/10 flex items-center justify-center text-purple text-[10px] font-bold flex-shrink-0">
                                        {{ collect(explode(' ', $post->author_name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
                                    </div>
                                    <span class="text-navy/40 text-xs font-medium font-body">{{ $post->author_name }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-10 sm:hidden">
                    <a href="/blog" class="text-purple hover:text-navy font-semibold text-sm transition-colors font-body">View all posts →</a>
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
                    <a href="/episodes" class="text-purple hover:text-navy font-semibold text-sm transition-colors font-body">All episodes →</a>
                </div>
            @else
                <div style="
                    background: linear-gradient(135deg, #1a1040, #2d1b69);
                    border-radius: 1.5rem;
                    padding: 3rem 2.5rem;
                    position: relative;
                    overflow: hidden;
                    border: 1px solid rgba(212, 168, 67, 0.12);
                ">
                    {{-- Ambient glow --}}
                    <div style="position: absolute; top: -40%; right: -20%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(212, 168, 67, 0.1) 0%, transparent 60%); pointer-events: none;"></div>

                    <div style="display: flex; align-items: center; gap: 2.5rem; flex-wrap: wrap; position: relative;">
                        {{-- Waveform visual --}}
                        <div style="flex-shrink: 0;">
                            <div style="width: 100px; height: 100px; border-radius: 1.25rem; background: linear-gradient(135deg, #d4a843, #b8922e); display: flex; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(212, 168, 67, 0.25);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#1a1040" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><line x1="12" x2="12" y1="19" y2="22"/></svg>
                            </div>
                        </div>

                        <div style="flex: 1; min-width: 240px;">
                            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fef9ef; margin-bottom: 0.5rem;">
                                Episode One Is Coming
                            </h3>
                            <p style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.5); font-size: 0.95rem; line-height: 1.7; margin-bottom: 1.25rem;">
                                Jeffrey &amp; Cassie are recording their first episode, an intro to who they are, why they started Mouse28, and what to expect from the show.
                            </p>
                            {{-- Faux waveform bars --}}
                            <div style="display: flex; align-items: end; gap: 3px; height: 28px; opacity: 0.3;">
                                @for($i = 0; $i < 40; $i++)
                                    <div style="width: 3px; background: #d4a843; border-radius: 2px; height: {{ rand(15, 100) }}%;"></div>
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
                        <div class="rounded-2xl overflow-hidden shadow-xl" style="border: 3px solid rgba(212,168,67,0.2);">
                            <img src="/images/meet-jeffrey-and-cassie.jpg" alt="Jeffrey and Cassie Davidson at Disney" class="w-full h-auto object-cover" style="aspect-ratio: 4/5;">
                        </div>
                        {{-- Decorative corner accent --}}
                        <div class="hidden md:block absolute -bottom-3 -right-3 w-24 h-24 border-b-2 border-r-2 rounded-br-2xl" style="border-color: rgba(212,168,67,0.25);"></div>
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
                        <a href="/contact" class="inline-flex items-center gap-1.5 text-purple hover:text-navy font-semibold text-sm font-body transition-colors">
                            Say hello →
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- The Story in Numbers --}}
    <section style="background: #ffffff; position: relative;">
        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(212,168,67,0.2), transparent);"></div>
        <div class="max-w-5xl mx-auto px-4 sm:px-6" style="padding: 4.5rem 1rem;">
            <div class="text-center" style="margin-bottom: 2.5rem;">
                <span class="font-body" style="font-size: 0.7rem; color: #d4a843; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 600;">The Family Behind Mouse28</span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                <div class="text-center">
                    <div class="font-heading" style="font-size: clamp(2.5rem, 5vw, 3.5rem); font-weight: 800; color: #1a1040; line-height: 1;">20</div>
                    <p class="font-body" style="color: rgba(26,16,64,0.45); font-size: 0.8rem; margin-top: 0.5rem; line-height: 1.5;">Minutes from<br>the Magic Kingdom</p>
                </div>
                <div class="text-center">
                    <div class="font-heading" style="font-size: clamp(2.5rem, 5vw, 3.5rem); font-weight: 800; color: #1a1040; line-height: 1;">2</div>
                    <p class="font-body" style="color: rgba(26,16,64,0.45); font-size: 0.8rem; margin-top: 0.5rem; line-height: 1.5;">Voices, one mic,<br>zero filter</p>
                </div>
                <div class="text-center">
                    <div class="font-heading" style="font-size: clamp(2.5rem, 5vw, 3.5rem); font-weight: 800; color: #d4a843; line-height: 1;">52</div>
                    <p class="font-body" style="color: rgba(26,16,64,0.45); font-size: 0.8rem; margin-top: 0.5rem; line-height: 1.5;">Park days a year<br>(at least)</p>
                </div>
                <div class="text-center">
                    <div class="font-heading" style="font-size: clamp(2.5rem, 5vw, 3.5rem); font-weight: 800; color: #1a1040; line-height: 1;">∞</div>
                    <p class="font-body" style="color: rgba(26,16,64,0.45); font-size: 0.8rem; margin-top: 0.5rem; line-height: 1.5;">Buckets of maple popcorn<br>(and counting)</p>
                </div>
            </div>
        </div>
        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(212,168,67,0.2), transparent);"></div>
    </section>

    {{-- Newsletter CTA --}}
    <section id="newsletter" class="py-16 md:py-24 bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden">
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
                    <input type="email" name="email" placeholder="your@email.com" required
                        class="newsletter-input flex-1 px-5 py-3.5 min-h-[48px] rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-gold/60 focus:border-gold/40 text-sm font-body transition-all duration-300">
                    <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold px-7 py-3.5 min-h-[48px] rounded-full transition-all duration-300 text-sm font-body hover:shadow-lg hover:shadow-gold/30 hover:-translate-y-0.5 hover:scale-105 active:scale-95">
                        Subscribe
                    </button>
                </form>
            @else
                <form action="/newsletter" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    @csrf
                    <input type="email" name="email" placeholder="your@email.com" required
                        class="newsletter-input flex-1 px-5 py-3.5 min-h-[48px] rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-gold/60 focus:border-gold/40 text-sm font-body transition-all duration-300">
                    <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold px-7 py-3.5 min-h-[48px] rounded-full transition-all duration-300 text-sm font-body hover:shadow-lg hover:shadow-gold/30 hover:-translate-y-0.5 hover:scale-105 active:scale-95">
                        Subscribe
                    </button>
                </form>
            @endif
            <div class="flex items-center justify-center gap-6 mt-10 pt-8 border-t border-white/10">
                <a href="#" class="text-white/40 hover:text-white/65 text-sm font-body transition-colors">🎧 Apple Podcasts</a>
                <a href="#" class="text-white/40 hover:text-white/65 text-sm font-body transition-colors">💚 Spotify</a>
            </div>
        </div>
    </section>

    {{-- Scroll animation observer --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
