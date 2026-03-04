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
            object-position: center 20%;
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
            <img src="/images/jeffrey.jpg" alt="Jeffrey Davidson at Epcot Japan Pavilion">
            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(to top, rgba(26,16,64,0.3), transparent); z-index: 1;"></div>
        </div>
    </section>

    {{-- Gold divider --}}
    <div style="height: 4px; background: linear-gradient(90deg, #d4a843, #b8922e, #d4a843);"></div>

{{-- Featured Post --}}
    @if($featuredPost)
        <section class="bg-navy" data-animate>
            <a href="/blog/{{ $featuredPost->slug }}" class="featured-link group block relative overflow-hidden">
                <div class="max-w-7xl mx-auto">
                    <div class="relative min-h-[400px] md:min-h-[500px] flex items-end">
                        @if($featuredPost->cover_image)
                            <img src="{{ $featuredPost->cover_image }}" alt="{{ $featuredPost->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="featured-overlay absolute inset-0 bg-gradient-to-t from-navy via-navy/70 to-navy/20"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-navy-light via-purple/30 to-navy"></div>
                        @endif
                        <div class="relative z-10 p-8 md:p-14 max-w-3xl border-l-4 border-gold ml-4 md:ml-10">
                            <div class="flex items-center gap-3 mb-4 flex-wrap">
                                <span class="bg-gold/90 text-navy text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider font-body">Featured</span>
                                @if($featuredPost->category)
                                    <span class="bg-white/15 backdrop-blur-sm text-white/80 text-xs font-semibold px-3 py-1 rounded-full font-body">{{ $featuredPost->category_label }}</span>
                                @endif
                                <span class="text-white/40 text-sm font-body">{{ $featuredPost->published_at->format('M j, Y') }}</span>
                            </div>
                            <h2 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight mb-4 group-hover:text-gold transition-colors duration-300">
                                {{ $featuredPost->title }}
                            </h2>
                            @if($featuredPost->excerpt)
                                <p class="text-white/65 text-lg leading-relaxed line-clamp-2 font-body">{{ $featuredPost->excerpt }}</p>
                            @endif
                            <span class="inline-flex items-center gap-2 mt-6 text-gold font-semibold text-sm font-body group-hover:gap-3 transition-all">
                                Read article
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </section>
    @endif

    {{-- Latest Posts / Coming Soon --}}
    @if($latestPosts->count())
        <section class="py-16 md:py-24 bg-cream">
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
                                @if($post->cover_image)
                                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="card-img w-full h-52 object-cover">
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
    @else
        <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 40%, #1a1040 100%); padding: 6rem 0;">
            {{-- Ambient glow --}}
            <div style="position: absolute; top: -20%; right: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(212, 168, 67, 0.08) 0%, transparent 60%); pointer-events: none;"></div>
            <div style="position: absolute; bottom: -20%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(91, 62, 158, 0.15) 0%, transparent 60%); pointer-events: none;"></div>

            <div class="max-w-5xl mx-auto px-4 sm:px-6 relative">
                <div class="grid md:grid-cols-2 gap-12 md:gap-16 items-center">
                    {{-- Left: Content --}}
                    <div>
                        <div style="display: inline-block; border: 1px solid rgba(212, 168, 67, 0.3); border-radius: 9999px; padding: 0.35rem 1rem; margin-bottom: 1.5rem;">
                            <span style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: #d4a843; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 600;">Coming Soon</span>
                        </div>
                        <h2 style="font-family: 'Playfair Display', serif; font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 700; color: #fef9ef; line-height: 1.15; margin-bottom: 1.25rem;">
                            Stories from inside<br>the parks
                        </h2>
                        <p style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.55); font-size: 1.05rem; line-height: 1.8; margin-bottom: 2rem;">
                            We visit Disney every week with our autistic daughter. We're turning those experiences into honest, useful content: accessibility insights, sensory tips, food reviews, and the real moments that make it all worth it.
                        </p>
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #d4a843; flex-shrink: 0;"></div>
                                <span style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.7); font-size: 0.9rem;">Park accessibility &amp; DAS pass tips</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #d4a843; flex-shrink: 0;"></div>
                                <span style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.7); font-size: 0.9rem;">Sensory-friendly ride &amp; dining guides</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #d4a843; flex-shrink: 0;"></div>
                                <span style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.7); font-size: 0.9rem;">Honest food &amp; resort reviews</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #d4a843; flex-shrink: 0;"></div>
                                <span style="font-family: 'Poppins', sans-serif; color: rgba(254, 249, 239, 0.7); font-size: 0.9rem;">Real family stories from the parks</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Visual --}}
                    <div style="position: relative;">
                        <div style="
                            background: rgba(45, 27, 105, 0.4);
                            border: 1px solid rgba(212, 168, 67, 0.15);
                            border-radius: 1.5rem;
                            padding: 2.5rem;
                            backdrop-filter: blur(10px);
                        ">
                            {{-- Faux article preview --}}
                            <div style="margin-bottom: 2rem;">
                                <div style="height: 10px; width: 40%; background: rgba(212, 168, 67, 0.25); border-radius: 99px; margin-bottom: 0.75rem;"></div>
                                <div style="height: 16px; width: 90%; background: rgba(254, 249, 239, 0.15); border-radius: 99px; margin-bottom: 0.5rem;"></div>
                                <div style="height: 16px; width: 75%; background: rgba(254, 249, 239, 0.1); border-radius: 99px; margin-bottom: 1.25rem;"></div>
                                <div style="height: 8px; width: 100%; background: rgba(254, 249, 239, 0.05); border-radius: 99px; margin-bottom: 0.4rem;"></div>
                                <div style="height: 8px; width: 95%; background: rgba(254, 249, 239, 0.05); border-radius: 99px; margin-bottom: 0.4rem;"></div>
                                <div style="height: 8px; width: 60%; background: rgba(254, 249, 239, 0.05); border-radius: 99px;"></div>
                            </div>

                            <div style="border-top: 1px solid rgba(212, 168, 67, 0.1); padding-top: 1.5rem;">
                                <div style="height: 10px; width: 35%; background: rgba(212, 168, 67, 0.2); border-radius: 99px; margin-bottom: 0.75rem;"></div>
                                <div style="height: 14px; width: 85%; background: rgba(254, 249, 239, 0.12); border-radius: 99px; margin-bottom: 0.5rem;"></div>
                                <div style="height: 14px; width: 65%; background: rgba(254, 249, 239, 0.08); border-radius: 99px; margin-bottom: 1rem;"></div>
                                <div style="height: 8px; width: 100%; background: rgba(254, 249, 239, 0.04); border-radius: 99px; margin-bottom: 0.4rem;"></div>
                                <div style="height: 8px; width: 80%; background: rgba(254, 249, 239, 0.04); border-radius: 99px;"></div>
                            </div>
                        </div>

                        {{-- Floating accent --}}
                        <div style="position: absolute; top: -12px; right: -12px; width: 60px; height: 60px; background: linear-gradient(135deg, #d4a843, #b8922e); border-radius: 1rem; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 25px rgba(212, 168, 67, 0.3);">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#1a1040" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
                        </div>
                    </div>
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

    {{-- Wave: White → Cream --}}
    {{-- The Story in Numbers --}}
    <section style="background: #fef9ef; position: relative;">
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
            <div class="text-center" style="margin-top: 2.5rem;">
                <a href="/about" class="font-body inline-flex items-center gap-1.5" style="color: #5b3e9e; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseenter="this.style.color='#1a1040'" onmouseleave="this.style.color='#5b3e9e'">
                    Meet Jeffrey &amp; Cassie
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
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
