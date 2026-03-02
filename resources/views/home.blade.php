@extends('layouts.app')

@section('title', 'Mouse28 — Disney Parks Through Different Eyes')

@section('content')
    <style>
        @keyframes heroGradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @keyframes gentlePulse {
            0%, 100% { box-shadow: 0 4px 15px rgba(212,168,67,0.2); }
            50% { box-shadow: 0 4px 25px rgba(212,168,67,0.45); }
        }
        @keyframes successPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
        .hero-animated-bg {
            background: linear-gradient(270deg, #1a1040, #2d1b69, #1a1040, #3a2370);
            background-size: 400% 400%;
            animation: heroGradient 12s ease infinite;
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
        /* Card hover enhancements */
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
        /* Featured post overlay */
        .featured-link .featured-overlay {
            background: linear-gradient(to top, rgba(26,16,64,0.95), rgba(26,16,64,0.7), rgba(26,16,64,0.2));
            transition: background 0.5s ease;
        }
        .featured-link:hover .featured-overlay {
            background: linear-gradient(to top, rgba(26,16,64,0.95), rgba(45,27,105,0.75), rgba(91,62,158,0.25));
        }
        /* Newsletter input glow */
        .newsletter-input:focus {
            box-shadow: 0 0 0 3px rgba(212,168,67,0.25), 0 0 20px rgba(212,168,67,0.1);
        }
        /* Scroll animations with stagger support */
        [data-animate] {
            opacity: 1;
            transform: translateY(0);
        }
        .js-animate [data-animate] {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .js-animate [data-animate].is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        /* Wave dividers — smoother rendering */
        .wave-divider svg {
            display: block;
            filter: none;
        }
        /* Hero floating card animations */
        @keyframes float1 {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-12px) rotate(-1deg); }
        }
        @keyframes float2 {
            0%, 100% { transform: translateY(0) rotate(3deg); }
            50% { transform: translateY(-8px) rotate(2deg); }
        }
        @keyframes float3 {
            0%, 100% { transform: translateY(0) rotate(1deg); }
            50% { transform: translateY(-10px) rotate(2deg); }
        }
        .hero-float-card {
            will-change: transform;
            cursor: pointer;
        }
        /* Magical scene animations */
        @keyframes twinkle {
            0%, 100% { opacity: 0.2; }
            50% { opacity: 1; }
        }
        @keyframes twinkle-alt {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.15; }
        }

    </style>

    {{-- Hero Section --}}
    <section class="relative hero-animated-bg overflow-hidden">
        {{-- Stars / sparkles scattered across hero --}}
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[8%] left-[5%] text-gold/40 text-[10px]">✦</span>
            <span class="sparkle-delay absolute top-[12%] left-[25%] text-gold/25 text-lg">✧</span>
            <span class="sparkle absolute top-[6%] left-[52%] text-gold/30 text-xs">⭐</span>
            <span class="sparkle-delay-2 absolute top-[18%] right-[12%] text-gold/20 text-sm">✦</span>
            <span class="sparkle absolute top-[30%] left-[8%] text-gold/15 text-base">✧</span>
            <span class="sparkle-delay absolute top-[22%] left-[42%] text-gold/35 text-[10px]">✦</span>
            <span class="sparkle-delay-2 absolute top-[35%] right-[25%] text-gold/20 text-xs">⭐</span>
            <span class="sparkle absolute top-[50%] left-[15%] text-gold/25 text-sm">✧</span>
            <span class="sparkle-delay absolute top-[45%] right-[8%] text-gold/30 text-[10px]">✦</span>
            <span class="sparkle-delay-2 absolute top-[55%] left-[35%] text-gold/15 text-lg">✧</span>
            <span class="sparkle absolute top-[65%] right-[18%] text-gold/20 text-xs">✦</span>
            <span class="sparkle-delay absolute top-[70%] left-[60%] text-gold/25 text-[10px]">⭐</span>
            <span class="sparkle-delay-2 absolute top-[75%] left-[10%] text-gold/20 text-sm">✦</span>
            <span class="sparkle absolute top-[80%] right-[35%] text-gold/15 text-xs">✧</span>
            <span class="sparkle-delay-2 absolute top-[40%] left-[70%] text-gold/30 text-[8px]">✦</span>
        </div>

        {{-- Warm glow behind cards area --}}
        <div class="absolute top-1/2 right-[15%] -translate-y-1/2 w-[500px] h-[500px] rounded-full pointer-events-none hidden lg:block" style="background: radial-gradient(circle, rgba(212,168,67,0.07) 0%, transparent 70%);" aria-hidden="true"></div>

        <div class="hero-content max-w-7xl mx-auto px-4 sm:px-6 py-20 md:py-28 lg:py-36 relative z-10">
            <div class="flex flex-col lg:flex-row items-center lg:items-start gap-12 lg:gap-16">

                {{-- Left side: Text content (55-60%) --}}
                <div class="lg:w-[58%] lg:pt-8">
                    <span class="inline-block text-gold/80 text-xs font-semibold tracking-[0.2em] uppercase mb-5 font-body">Autism Family · Disney Every Week</span>
                    <h1 class="font-heading text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.08] mb-6">
                        Disney Parks Through<br>
                        <span class="text-gold">Different Eyes</span>
                    </h1>
                    <p class="text-white/60 text-lg md:text-xl max-w-xl mb-8 leading-[1.7] font-body">
                        Accessibility tips, sensory-friendly recommendations, and real stories from a family who visits Disney every single week with our autistic daughter.
                    </p>
                    <div class="flex flex-wrap items-center gap-4 mb-8">
                        <a href="/blog" class="cta-primary bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-4 min-h-[48px] rounded-full shadow-lg shadow-gold/20 hover:shadow-gold/50 hover:scale-105 transition-all duration-300 hover:-translate-y-1 text-base font-body inline-flex items-center">
                            Read Our Blog
                        </a>
                        @if($featuredPost)
                            <a href="/blog/{{ $featuredPost->slug }}" class="text-white/55 hover:text-gold text-sm font-medium font-body transition-colors duration-200">
                                or read the latest post →
                            </a>
                        @endif
                    </div>
                    @if($storiesCount > 0)
                        <div class="flex items-center gap-5 text-white/35 text-sm font-body">
                            <span>💜 {{ $storiesCount }} {{ Str::plural('family', $storiesCount) }}</span>
                        </div>
                    @endif
                </div>

                {{-- Right side: Castle silhouette with stars --}}
                <div class="lg:w-[42%] relative w-full max-w-md lg:max-w-none mx-auto lg:mx-0">
                    <div class="relative h-[280px] sm:h-[350px] lg:h-[480px]" style="-webkit-mask-image: radial-gradient(ellipse 90% 85% at center 55%, black 50%, transparent 100%); mask-image: radial-gradient(ellipse 90% 85% at center 55%, black 50%, transparent 100%);">
                        <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-full" preserveAspectRatio="xMidYMid meet">
                            <defs>
                                <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#0d0a24"/>
                                    <stop offset="50%" stop-color="#1a1040"/>
                                    <stop offset="100%" stop-color="#2d1b69"/>
                                </linearGradient>
                                <radialGradient id="moonGlow" cx="70%" cy="15%" r="25%">
                                    <stop offset="0%" stop-color="#fef9ef" stop-opacity="0.12"/>
                                    <stop offset="100%" stop-color="#1a1040" stop-opacity="0"/>
                                </radialGradient>
                                <radialGradient id="castleGlow" cx="50%" cy="85%" r="30%">
                                    <stop offset="0%" stop-color="#d4a843" stop-opacity="0.15"/>
                                    <stop offset="100%" stop-color="#1a1040" stop-opacity="0"/>
                                </radialGradient>
                            </defs>

                            {{-- Sky --}}
                            <rect width="500" height="500" fill="url(#skyGrad)"/>
                            <rect width="500" height="500" fill="url(#moonGlow)"/>

                            {{-- Stars --}}
                            @for($i = 0; $i < 60; $i++)
                                <circle
                                    cx="{{ rand(10, 490) }}"
                                    cy="{{ rand(10, 280) }}"
                                    r="{{ rand(3, 8) / 10 }}"
                                    fill="#fef9ef"
                                    opacity="{{ rand(2, 8) / 10 }}"
                                >
                                    <animate attributeName="opacity"
                                        values="{{ rand(2, 4) / 10 }};{{ rand(7, 10) / 10 }};{{ rand(2, 4) / 10 }}"
                                        dur="{{ rand(20, 50) / 10 }}s"
                                        begin="{{ rand(0, 30) / 10 }}s"
                                        repeatCount="indefinite"/>
                                </circle>
                            @endfor

                            {{-- Distant trees/treeline --}}
                            <path d="M0 380 Q50 360 80 370 Q100 350 130 365 Q160 345 190 360 Q210 340 240 355 Q270 335 300 350 Q330 340 350 355 Q370 338 400 350 Q430 340 460 355 Q480 345 500 360 L500 500 L0 500 Z" fill="#0d0a24" opacity="0.5"/>

                            {{-- Castle silhouette --}}
                            <g transform="translate(250, 350)" style="filter: drop-shadow(0 0 20px rgba(212, 168, 67, 0.1));">
                                {{-- Main structure --}}
                                <rect x="-80" y="-80" width="160" height="80" fill="#0a0620" rx="2"/>
                                {{-- Center tower --}}
                                <rect x="-15" y="-170" width="30" height="90" fill="#0a0620"/>
                                <polygon points="-20,-170 0,-200 20,-170" fill="#0a0620"/>
                                {{-- Spire --}}
                                <line x1="0" y1="-200" x2="0" y2="-215" stroke="#d4a843" stroke-width="1.5" opacity="0.6"/>
                                {{-- Left tower --}}
                                <rect x="-75" y="-130" width="25" height="50" fill="#0a0620"/>
                                <polygon points="-78,-130 -62,-155 -47,-130" fill="#0a0620"/>
                                <line x1="-62" y1="-155" x2="-62" y2="-165" stroke="#d4a843" stroke-width="1" opacity="0.5"/>
                                {{-- Right tower --}}
                                <rect x="50" y="-130" width="25" height="50" fill="#0a0620"/>
                                <polygon points="47,-130 62,-155 78,-130" fill="#0a0620"/>
                                <line x1="62" y1="-155" x2="62" y2="-165" stroke="#d4a843" stroke-width="1" opacity="0.5"/>
                                {{-- Far left turret --}}
                                <rect x="-95" y="-110" width="18" height="30" fill="#0a0620"/>
                                <polygon points="-97,-110 -86,-128 -75,-110" fill="#0a0620"/>
                                {{-- Far right turret --}}
                                <rect x="77" y="-110" width="18" height="30" fill="#0a0620"/>
                                <polygon points="75,-110 86,-128 97,-110" fill="#0a0620"/>
                                {{-- Windows (warm glow) --}}
                                <rect x="-8" y="-100" width="6" height="10" rx="3" fill="#d4a843" opacity="0.4"/>
                                <rect x="2" y="-100" width="6" height="10" rx="3" fill="#d4a843" opacity="0.35"/>
                                <rect x="-50" y="-65" width="5" height="8" rx="2" fill="#d4a843" opacity="0.25"/>
                                <rect x="-35" y="-65" width="5" height="8" rx="2" fill="#d4a843" opacity="0.3"/>
                                <rect x="30" y="-65" width="5" height="8" rx="2" fill="#d4a843" opacity="0.25"/>
                                <rect x="45" y="-65" width="5" height="8" rx="2" fill="#d4a843" opacity="0.3"/>
                                {{-- Entrance arch --}}
                                <path d="M-12,-10 L-12,-35 Q0,-48 12,-35 L12,-10" fill="#0a0620" stroke="#d4a843" stroke-width="0.5" stroke-opacity="0.3"/>
                                <rect x="-10" y="-28" width="20" height="28" fill="#d4a843" opacity="0.08"/>
                            </g>

                            {{-- Castle base glow --}}
                            <rect x="0" y="340" width="500" height="160" fill="url(#castleGlow)"/>

                            {{-- Ground --}}
                            <path d="M0 400 Q125 385 250 390 Q375 395 500 388 L500 500 L0 500 Z" fill="#0d0a24"/>

                            {{-- Shooting star --}}
                            <line x1="0" y1="0" x2="30" y2="15" stroke="white" stroke-width="1" opacity="0">
                                <animate attributeName="x1" values="400;100" dur="3s" begin="4s" repeatCount="indefinite"/>
                                <animate attributeName="y1" values="50;150" dur="3s" begin="4s" repeatCount="indefinite"/>
                                <animate attributeName="x2" values="430;130" dur="3s" begin="4s" repeatCount="indefinite"/>
                                <animate attributeName="y2" values="65;165" dur="3s" begin="4s" repeatCount="indefinite"/>
                                <animate attributeName="opacity" values="0;0;0.8;0" dur="3s" begin="4s" repeatCount="indefinite"/>
                            </line>
                        </svg>
                    </div>
            </div>
        </div>


        {{-- Castle silhouette divider --}}
        <div class="absolute bottom-0 left-0 right-0 z-10" aria-hidden="true">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block" preserveAspectRatio="none">
                {{-- Background base --}}
                <rect y="100" width="1440" height="20" fill="#1a1040"/>
                {{-- Castle silhouette --}}
                <path d="
                    M0 120 L0 105 Q100 100 200 102 L250 100 L260 85 L265 100 L290 98 L295 80 L300 98
                    L350 95 L380 90 L385 70 L388 55 L390 45 L392 55 L395 70 L400 90 L430 95
                    L460 98 L470 85 L475 75 L478 65 L480 55 L482 45 L484 35 L486 28 L488 22 L490 18
                    L492 22 L494 28 L496 35 L498 45 L500 55 L502 65 L505 75 L510 85 L520 98
                    L560 95 L590 90 L600 75 L610 65 L615 58 L618 50 L620 42 L622 36 L624 30
                    L626 26 L628 22 L630 20 L632 18 L634 15 L636 12 L638 10 L640 8
                    L641 5 L642 3 L643 2 L644 1 L645 0 L646 0 L647 0 L648 0
                    L649 1 L650 2 L651 3 L652 5 L654 8 L656 10 L658 12 L660 15
                    L662 18 L664 20 L666 22 L668 26 L670 30 L672 36 L674 42 L676 50
                    L678 58 L680 65 L690 75 L700 90 L720 95
                    L740 90 L750 82 L755 72 L758 65 L760 58 L762 50 L764 42 L766 36
                    L768 32 L770 28 L772 32 L774 36 L776 42 L778 50 L780 58 L782 65
                    L785 72 L790 82 L800 90 L820 95
                    L860 98 L880 92 L885 82 L888 72 L890 65 L892 58 L894 52 L896 48
                    L898 52 L900 58 L902 65 L905 72 L910 82 L915 92 L940 98
                    L980 100 L1000 95 L1010 85 L1015 95 L1040 98 L1060 95 L1070 80 L1075 95
                    L1100 98 L1120 100 L1160 102 L1200 100 L1230 98 L1240 88 L1245 98
                    L1280 100 L1320 102 Q1380 105 1440 108 L1440 120 Z
                " fill="rgba(45,27,105,0.3)"/>
                {{-- Solid base strip --}}
                <path d="M0 110 Q360 100 720 108 Q1080 116 1440 108 L1440 120 L0 120 Z" fill="#1a1040"/>
            </svg>
        </div>
    </section>

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
                                <span class="bg-white/10 backdrop-blur-sm text-white/65 text-xs font-semibold px-3 py-1 rounded-full font-body">{{ $featuredPost->reading_time }} min read</span>
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

    {{-- Wave: Navy → Cream --}}
    <div class="bg-cream" aria-hidden="true">
        <svg viewBox="0 0 1440 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block -mt-px">
            <path d="M0 0V28C360 0 720 48 1080 24C1260 12 1380 4 1440 0V0H0Z" fill="#1a1040"/>
        </svg>
    </div>

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
                            We visit Disney every week with our autistic daughter. We're turning those experiences into honest, useful content — accessibility insights, sensory tips, food reviews, and the real moments that make it all worth it.
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
                                Jeffrey &amp; Cassie are recording their first episode — an intro to who they are, why they started Mouse28, and what to expect from the show.
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
    <div class="bg-cream" aria-hidden="true">
        <svg viewBox="0 0 1440 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block -mt-px">
            <path d="M0 0C360 35 720 5 1080 30C1260 42 1380 15 1440 20V40H0V0Z" fill="white"/>
        </svg>
    </div>

    {{-- About the Hosts --}}
    <section class="py-16 md:py-24 bg-cream">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row gap-12 items-center" data-animate>
                <div class="flex-shrink-0">
                    <img src="/images/logo.jpg" alt="Jeffrey & Cassie Davidson" class="w-36 h-36 md:w-44 md:h-44 rounded-2xl shadow-lg object-cover">
                </div>
                <div>
                    <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">Meet the Family</span>
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2 mb-5">Jeffrey &amp; Cassie Davidson</h2>
                    <div class="relative pl-8 mb-4">
                        <span class="absolute left-0 top-0 font-heading text-5xl text-gold/25 leading-none select-none" aria-hidden="true">"</span>
                        <p class="text-navy/75 text-lg italic leading-relaxed font-body">
                            She's taught us to see magic in ways we never imagined.
                        </p>
                        <span class="absolute -bottom-2 right-0 font-heading text-5xl text-gold/25 leading-none select-none" aria-hidden="true">"</span>
                    </div>
                    <p class="text-navy/65 leading-[1.7] font-body max-w-xl">
                        We're a Central Florida family who visits Disney every week. Our daughter Viola has autism and experiences the parks differently. Mouse'28 is where we share what we've learned: DAS tips, sensory-friendly spots, and the joy that makes it all worth it.
                    </p>
                    <a href="/about" class="inline-flex items-center gap-1 mt-4 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                        Our full story
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Families Like Yours --}}
    @if($communityStories->count())
        <section class="py-14 md:py-20 bg-cream">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <div class="text-center mb-10" data-animate>
                    <span class="text-purple/60 text-sm font-semibold tracking-[0.15em] uppercase font-body">💜 From Our Community</span>
                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-1">Families Like Yours</h2>
                </div>
                <div class="grid sm:grid-cols-2 gap-6">
                    @foreach($communityStories as $story)
                        <div class="bg-white rounded-2xl p-6 border border-navy/5 relative" data-animate>
                            <span class="absolute top-4 right-5 font-heading text-4xl text-gold/20 leading-none select-none" aria-hidden="true">"</span>
                            <p class="text-navy/70 text-sm leading-relaxed font-body mb-4 line-clamp-3 italic">{{ Str::limit($story->story, 180) }}</p>
                            <div class="flex items-center gap-3 pt-3 border-t border-navy/5">
                                <div class="w-8 h-8 rounded-full bg-purple/10 flex items-center justify-center text-purple text-xs font-bold flex-shrink-0">
                                    {{ strtoupper(substr($story->name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-navy font-semibold text-sm font-body">{{ $story->name }}</span>
                                    @if($story->child_name && $story->child_age)
                                        <p class="text-navy/40 text-xs font-body">Parent of {{ $story->child_name }}, age {{ $story->child_age }}</p>
                                    @endif
                                </div>
                                @if($story->favorite_park)
                                    <span class="ml-auto text-navy/30 text-xs font-body">{{ $story->favorite_park }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="text-center mt-8">
                    <a href="/stories" class="text-purple hover:text-navy font-semibold text-sm transition-colors font-body">Read more stories →</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Wave: Cream → Navy --}}
    <div class="bg-gradient-to-br from-navy via-navy-light to-navy" aria-hidden="true">
        <svg viewBox="0 0 1440 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block -mt-px">
            <path d="M0 0C480 48 960 0 1440 36V48H0V0Z" fill="#fef9ef"/>
        </svg>
    </div>

    {{-- Newsletter CTA --}}
    <section class="py-16 md:py-24 bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[20%] left-[15%] text-gold/30 text-sm">✦</span>
            <span class="sparkle-delay absolute bottom-[25%] right-[20%] text-gold/20 text-lg">✧</span>
            <span class="sparkle-delay-2 absolute top-[50%] left-[70%] text-gold/15 text-xs">✦</span>
        </div>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center relative z-10" data-animate>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-white mb-4">Get Disney Tips in Your Inbox</h2>
            <p class="text-white/55 text-lg mb-8 leading-[1.7] font-body">Weekly park tips, accessibility guides, and family stories. No spam, just pixie dust.</p>
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
                <a href="#" class="text-white/40 hover:text-white/65 text-sm font-body transition-colors">▶️ YouTube</a>
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

            // Subtle hero parallax on scroll (content only, not the background)
            const heroContent = document.querySelector('.hero-content');
            if (heroContent) {
                let ticking = false;
                window.addEventListener('scroll', function() {
                    if (!ticking) {
                        requestAnimationFrame(function() {
                            const scroll = window.scrollY;
                            if (scroll < 600) {
                                heroContent.style.transform = 'translateY(' + (scroll * 0.1) + 'px)';
                                heroContent.style.opacity = Math.max(0.4, 1 - scroll / 800);
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
