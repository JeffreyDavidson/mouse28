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
        @keyframes firework-burst {
            0% { transform: scale(0); opacity: 1; }
            60% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.3); opacity: 0; }
        }
        @keyframes pixie-rise {
            0% { transform: translateY(0) scale(1); opacity: 0.9; }
            100% { transform: translateY(-70px) scale(0.2); opacity: 0; }
        }
        @keyframes castle-glow {
            0%, 100% { opacity: 0.3; }
            50% { opacity: 0.7; }
        }
        .star-twinkle { animation: twinkle var(--tw-dur, 3s) ease-in-out infinite; animation-delay: var(--tw-delay, 0s); }
        .star-twinkle-alt { animation: twinkle-alt var(--tw-dur, 4s) ease-in-out infinite; animation-delay: var(--tw-delay, 0s); }
        .firework { transform-origin: center; animation: firework-burst var(--fw-dur, 4s) ease-out infinite; animation-delay: var(--fw-delay, 0s); }
        .pixie { animation: pixie-rise var(--px-dur, 3s) ease-out infinite; animation-delay: var(--px-delay, 0s); }
        .glow-pulse { animation: castle-glow 3s ease-in-out infinite; }
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
                        Sensory guides, accessibility tips, and real stories from a family who visits Disney every single week with our autistic daughter.
                    </p>
                    <div class="flex flex-wrap items-center gap-4 mb-8">
                        <a href="/guides" class="cta-primary bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-4 min-h-[48px] rounded-full shadow-lg shadow-gold/20 hover:shadow-gold/50 hover:scale-105 transition-all duration-300 hover:-translate-y-1 text-base font-body inline-flex items-center">
                            Explore Our Guides
                        </a>
                        @if($featuredPost)
                            <a href="/blog/{{ $featuredPost->slug }}" class="text-white/55 hover:text-gold text-sm font-medium font-body transition-colors duration-200">
                                or read the latest post →
                            </a>
                        @endif
                    </div>
                    @if($guidesCount > 0 || $storiesCount > 0)
                        <div class="flex items-center gap-5 text-white/35 text-sm font-body">
                            @if($guidesCount > 0)
                                <span>📖 {{ $guidesCount }} {{ Str::plural('guide', $guidesCount) }}</span>
                            @endif
                            @if($storiesCount > 0)
                                <span>💜 {{ $storiesCount }} {{ Str::plural('family', $storiesCount) }}</span>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Right side: Magical illustrated scene --}}
                <div class="lg:w-[42%] relative w-full max-w-md lg:max-w-none mx-auto lg:mx-0">
                    <div class="relative h-[280px] sm:h-[350px] lg:h-[480px]">
                        <svg viewBox="0 0 500 500" xmlns="http://www.w3.org/2000/svg" class="w-full h-full" preserveAspectRatio="xMidYMid meet">
                            <defs>
                                {{-- Sky gradient --}}
                                <linearGradient id="skyGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#0d0a24"/>
                                    <stop offset="40%" stop-color="#1a1040"/>
                                    <stop offset="100%" stop-color="#2d1b69"/>
                                </linearGradient>
                                {{-- Castle entrance glow --}}
                                <radialGradient id="entranceGlow" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#d4a843" stop-opacity="0.6"/>
                                    <stop offset="100%" stop-color="#d4a843" stop-opacity="0"/>
                                </radialGradient>
                                {{-- Ground gradient --}}
                                <linearGradient id="groundGrad" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#1a1040" stop-opacity="0"/>
                                    <stop offset="100%" stop-color="#0d0a24"/>
                                </linearGradient>
                                {{-- Sparkle shape --}}
                                <g id="sparkle4">
                                    <line x1="0" y1="-4" x2="0" y2="4" stroke="#f0c75e" stroke-width="1" stroke-linecap="round"/>
                                    <line x1="-4" y1="0" x2="4" y2="0" stroke="#f0c75e" stroke-width="1" stroke-linecap="round"/>
                                    <line x1="-2.5" y1="-2.5" x2="2.5" y2="2.5" stroke="#f0c75e" stroke-width="0.6" stroke-linecap="round"/>
                                    <line x1="2.5" y1="-2.5" x2="-2.5" y2="2.5" stroke="#f0c75e" stroke-width="0.6" stroke-linecap="round"/>
                                </g>
                            </defs>

                            {{-- Sky background --}}
                            <rect width="500" height="500" fill="url(#skyGrad)"/>

                            {{-- Stars layer --}}
                            <circle cx="45" cy="35" r="1.2" fill="white" class="star-twinkle" style="--tw-dur:2.8s;--tw-delay:0s"/>
                            <circle cx="120" cy="22" r="0.8" fill="white" class="star-twinkle" style="--tw-dur:4s;--tw-delay:1.2s"/>
                            <circle cx="190" cy="50" r="1.5" fill="white" class="star-twinkle-alt" style="--tw-dur:3.5s;--tw-delay:0.5s"/>
                            <circle cx="280" cy="18" r="1" fill="white" class="star-twinkle" style="--tw-dur:3s;--tw-delay:2s"/>
                            <circle cx="350" cy="40" r="1.3" fill="white" class="star-twinkle-alt" style="--tw-dur:4.2s;--tw-delay:0.8s"/>
                            <circle cx="420" cy="25" r="0.9" fill="white" class="star-twinkle" style="--tw-dur:2.5s;--tw-delay:1.5s"/>
                            <circle cx="470" cy="55" r="1.1" fill="white" class="star-twinkle-alt" style="--tw-dur:3.8s;--tw-delay:0.3s"/>
                            <circle cx="75" cy="80" r="0.7" fill="white" class="star-twinkle" style="--tw-dur:3.2s;--tw-delay:2.5s"/>
                            <circle cx="160" cy="95" r="1" fill="white" class="star-twinkle-alt" style="--tw-dur:4.5s;--tw-delay:1s"/>
                            <circle cx="310" cy="75" r="1.4" fill="white" class="star-twinkle" style="--tw-dur:2.6s;--tw-delay:0.7s"/>
                            <circle cx="400" cy="90" r="0.8" fill="white" class="star-twinkle-alt" style="--tw-dur:3.9s;--tw-delay:1.8s"/>
                            <circle cx="55" cy="130" r="1" fill="white" class="star-twinkle" style="--tw-dur:3.4s;--tw-delay:0.2s"/>
                            <circle cx="440" cy="120" r="1.2" fill="white" class="star-twinkle" style="--tw-dur:2.9s;--tw-delay:2.2s"/>
                            <circle cx="230" cy="65" r="0.6" fill="white" class="star-twinkle-alt" style="--tw-dur:5s;--tw-delay:0.6s"/>
                            <circle cx="480" cy="160" r="0.9" fill="white" class="star-twinkle" style="--tw-dur:3.7s;--tw-delay:1.3s"/>
                            <circle cx="25" cy="170" r="1.1" fill="white" class="star-twinkle-alt" style="--tw-dur:4.1s;--tw-delay:0.9s"/>
                            {{-- Sparkle stars --}}
                            <use href="#sparkle4" x="95" y="45" class="star-twinkle" style="--tw-dur:3s;--tw-delay:1s" opacity="0.5"/>
                            <use href="#sparkle4" x="380" y="60" class="star-twinkle-alt" style="--tw-dur:4s;--tw-delay:2s" opacity="0.4"/>
                            <use href="#sparkle4" x="460" y="95" class="star-twinkle" style="--tw-dur:3.5s;--tw-delay:0.4s" opacity="0.35"/>

                            {{-- Firework 1 — gold, upper left --}}
                            <g class="firework" style="--fw-dur:4.5s;--fw-delay:0s">
                                <circle cx="150" cy="80" r="2" fill="#f0c75e"/>
                                <line x1="150" y1="80" x2="150" y2="58" stroke="#f0c75e" stroke-width="1.2" stroke-linecap="round" opacity="0.9"/>
                                <line x1="150" y1="80" x2="150" y2="102" stroke="#f0c75e" stroke-width="1.2" stroke-linecap="round" opacity="0.9"/>
                                <line x1="150" y1="80" x2="128" y2="80" stroke="#f0c75e" stroke-width="1.2" stroke-linecap="round" opacity="0.9"/>
                                <line x1="150" y1="80" x2="172" y2="80" stroke="#f0c75e" stroke-width="1.2" stroke-linecap="round" opacity="0.9"/>
                                <line x1="150" y1="80" x2="135" y2="65" stroke="#d4a843" stroke-width="0.8" stroke-linecap="round" opacity="0.7"/>
                                <line x1="150" y1="80" x2="165" y2="65" stroke="#d4a843" stroke-width="0.8" stroke-linecap="round" opacity="0.7"/>
                                <line x1="150" y1="80" x2="135" y2="95" stroke="#d4a843" stroke-width="0.8" stroke-linecap="round" opacity="0.7"/>
                                <line x1="150" y1="80" x2="165" y2="95" stroke="#d4a843" stroke-width="0.8" stroke-linecap="round" opacity="0.7"/>
                                <circle cx="150" cy="58" r="1.5" fill="#f0c75e" opacity="0.8"/>
                                <circle cx="172" cy="80" r="1.5" fill="#f0c75e" opacity="0.8"/>
                                <circle cx="135" cy="65" r="1" fill="#d4a843" opacity="0.6"/>
                                <circle cx="165" cy="95" r="1" fill="#d4a843" opacity="0.6"/>
                            </g>

                            {{-- Firework 2 — purple-light, upper center --}}
                            <g class="firework" style="--fw-dur:5s;--fw-delay:1.8s">
                                <circle cx="270" cy="55" r="2" fill="#7b5eb5"/>
                                <line x1="270" y1="55" x2="270" y2="30" stroke="#7b5eb5" stroke-width="1.2" stroke-linecap="round" opacity="0.9"/>
                                <line x1="270" y1="55" x2="270" y2="80" stroke="#7b5eb5" stroke-width="1.2" stroke-linecap="round" opacity="0.9"/>
                                <line x1="270" y1="55" x2="245" y2="55" stroke="#7b5eb5" stroke-width="1.2" stroke-linecap="round" opacity="0.9"/>
                                <line x1="270" y1="55" x2="295" y2="55" stroke="#7b5eb5" stroke-width="1.2" stroke-linecap="round" opacity="0.9"/>
                                <line x1="270" y1="55" x2="253" y2="38" stroke="white" stroke-width="0.8" stroke-linecap="round" opacity="0.6"/>
                                <line x1="270" y1="55" x2="287" y2="38" stroke="white" stroke-width="0.8" stroke-linecap="round" opacity="0.6"/>
                                <line x1="270" y1="55" x2="253" y2="72" stroke="white" stroke-width="0.8" stroke-linecap="round" opacity="0.6"/>
                                <line x1="270" y1="55" x2="287" y2="72" stroke="white" stroke-width="0.8" stroke-linecap="round" opacity="0.6"/>
                                <circle cx="270" cy="30" r="1.5" fill="#7b5eb5" opacity="0.8"/>
                                <circle cx="295" cy="55" r="1.5" fill="#7b5eb5" opacity="0.8"/>
                                <circle cx="253" cy="38" r="1" fill="white" opacity="0.5"/>
                                <circle cx="287" cy="72" r="1" fill="white" opacity="0.5"/>
                            </g>

                            {{-- Firework 3 — white/gold, upper right --}}
                            <g class="firework" style="--fw-dur:4s;--fw-delay:3.2s">
                                <circle cx="370" cy="70" r="1.5" fill="white"/>
                                <line x1="370" y1="70" x2="370" y2="50" stroke="white" stroke-width="1" stroke-linecap="round" opacity="0.9"/>
                                <line x1="370" y1="70" x2="370" y2="90" stroke="white" stroke-width="1" stroke-linecap="round" opacity="0.9"/>
                                <line x1="370" y1="70" x2="350" y2="70" stroke="white" stroke-width="1" stroke-linecap="round" opacity="0.9"/>
                                <line x1="370" y1="70" x2="390" y2="70" stroke="white" stroke-width="1" stroke-linecap="round" opacity="0.9"/>
                                <line x1="370" y1="70" x2="356" y2="56" stroke="#f0c75e" stroke-width="0.7" stroke-linecap="round" opacity="0.6"/>
                                <line x1="370" y1="70" x2="384" y2="56" stroke="#f0c75e" stroke-width="0.7" stroke-linecap="round" opacity="0.6"/>
                                <line x1="370" y1="70" x2="356" y2="84" stroke="#f0c75e" stroke-width="0.7" stroke-linecap="round" opacity="0.6"/>
                                <line x1="370" y1="70" x2="384" y2="84" stroke="#f0c75e" stroke-width="0.7" stroke-linecap="round" opacity="0.6"/>
                                <circle cx="370" cy="50" r="1.2" fill="white" opacity="0.7"/>
                                <circle cx="356" cy="56" r="0.8" fill="#f0c75e" opacity="0.5"/>
                            </g>

                            {{-- Castle silhouette --}}
                            <g fill="#2d1b69">
                                {{-- Central tall spire --}}
                                <polygon points="250,120 248,135 245,160 243,185 240,210 237,240 236,260 264,260 263,240 260,210 257,185 255,160 252,135"/>
                                {{-- Spire tip --}}
                                <polygon points="250,100 247,120 253,120"/>
                                {{-- Spire flag --}}
                                <line x1="250" y1="100" x2="250" y2="92" stroke="#2d1b69" stroke-width="1.5"/>
                                <polygon points="250,92 260,96 250,100" fill="#5b3e9e" opacity="0.7"/>
                                {{-- Central tower body --}}
                                <rect x="230" y="260" width="40" height="50" rx="2"/>
                                {{-- Castle entrance arch --}}
                                <rect x="238" y="290" width="24" height="20" rx="12" fill="#1a1040"/>
                                {{-- Left main tower --}}
                                <rect x="195" y="230" width="35" height="80" rx="2"/>
                                <polygon points="212,195 198,230 227,230"/>
                                <polygon points="212,185 209,195 215,195"/>
                                {{-- Right main tower --}}
                                <rect x="270" y="230" width="35" height="80" rx="2"/>
                                <polygon points="287,195 273,230 302,230"/>
                                <polygon points="287,185 284,195 290,195"/>
                                {{-- Far left small tower --}}
                                <rect x="170" y="270" width="25" height="40" rx="2"/>
                                <polygon points="182,250 172,270 193,270"/>
                                <polygon points="182,242 179,250 185,250"/>
                                {{-- Far right small tower --}}
                                <rect x="305" y="270" width="25" height="40" rx="2"/>
                                <polygon points="317,250 307,270 328,270"/>
                                <polygon points="317,242 314,250 320,250"/>
                                {{-- Connecting walls --}}
                                <rect x="170" y="295" width="160" height="15" rx="1"/>
                                {{-- Battlements on walls --}}
                                <rect x="175" y="290" width="6" height="8" fill="#2d1b69"/>
                                <rect x="186" y="290" width="6" height="8" fill="#2d1b69"/>
                                <rect x="270" y="290" width="6" height="8" fill="#2d1b69"/>
                                <rect x="281" y="290" width="6" height="8" fill="#2d1b69"/>
                                <rect x="308" y="290" width="6" height="8" fill="#2d1b69"/>
                                <rect x="319" y="290" width="6" height="8" fill="#2d1b69"/>
                                {{-- Tower windows --}}
                                <ellipse cx="212" cy="255" rx="4" ry="6" fill="#1a1040" opacity="0.6"/>
                                <ellipse cx="287" cy="255" rx="4" ry="6" fill="#1a1040" opacity="0.6"/>
                                <ellipse cx="250" cy="275" rx="3" ry="5" fill="#1a1040" opacity="0.6"/>
                                {{-- Small accent spires --}}
                                <polygon points="200,230 198,222 202,222" fill="#3a2370"/>
                                <polygon points="224,230 222,222 226,222" fill="#3a2370"/>
                                <polygon points="276,230 274,222 278,222" fill="#3a2370"/>
                                <polygon points="300,230 298,222 302,222" fill="#3a2370"/>
                            </g>

                            {{-- Castle entrance glow --}}
                            <ellipse cx="250" cy="300" rx="20" ry="18" fill="url(#entranceGlow)" class="glow-pulse"/>

                            {{-- Window warm glow dots --}}
                            <circle cx="212" cy="255" r="2" fill="#d4a843" opacity="0.25" class="glow-pulse"/>
                            <circle cx="287" cy="255" r="2" fill="#d4a843" opacity="0.25" class="glow-pulse"/>
                            <circle cx="250" cy="275" r="1.5" fill="#d4a843" opacity="0.2" class="glow-pulse"/>

                            {{-- Ground/path area --}}
                            <rect x="0" y="390" width="500" height="110" fill="url(#groundGrad)"/>
                            <ellipse cx="250" cy="400" rx="250" ry="30" fill="#0f0b22" opacity="0.5"/>

                            {{-- Walkway path --}}
                            <path d="M250,310 Q248,340 240,370 Q230,400 200,440 Q180,465 140,490" stroke="#2d1b69" stroke-width="28" fill="none" stroke-linecap="round" opacity="0.3"/>
                            <path d="M250,310 Q252,340 260,370 Q270,400 300,440 Q320,465 360,490" stroke="#2d1b69" stroke-width="28" fill="none" stroke-linecap="round" opacity="0.3"/>
                            {{-- Path lanterns --}}
                            <circle cx="228" cy="380" r="1.5" fill="#d4a843" opacity="0.5" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0s"/>
                            <circle cx="215" cy="410" r="1.5" fill="#d4a843" opacity="0.5" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0.4s"/>
                            <circle cx="195" cy="440" r="1.5" fill="#d4a843" opacity="0.5" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0.8s"/>
                            <circle cx="272" cy="380" r="1.5" fill="#d4a843" opacity="0.5" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0.2s"/>
                            <circle cx="285" cy="410" r="1.5" fill="#d4a843" opacity="0.5" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0.6s"/>
                            <circle cx="305" cy="440" r="1.5" fill="#d4a843" opacity="0.5" class="star-twinkle" style="--tw-dur:2s;--tw-delay:1s"/>

                            {{-- Family silhouette --}}
                            <g fill="#0e0a1f">
                                {{-- Taller parent (Jeffrey) — left --}}
                                <ellipse cx="225" cy="378" rx="6" ry="6.5"/>{{-- head --}}
                                <path d="M225,384 Q222,395 220,410 Q218,420 217,435 L220,435 Q221,425 223,415 L225,415 Q227,425 228,435 L231,435 Q230,420 228,410 Q226,395 225,384Z"/>
                                {{-- Left arm reaching down to child --}}
                                <path d="M222,392 Q220,398 222,404 Q224,408 228,410" stroke="#0e0a1f" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                {{-- Right arm --}}
                                <path d="M228,392 Q232,400 230,408" stroke="#0e0a1f" stroke-width="2.5" fill="none" stroke-linecap="round"/>

                                {{-- Child (Viola) — center, reaching up --}}
                                <ellipse cx="240" cy="400" rx="5" ry="5.5"/>{{-- head --}}
                                <path d="M240,405 Q238,413 237,425 Q236,432 235,440 L238,440 Q238.5,433 239,427 L241,427 Q241.5,433 242,440 L245,440 Q244,432 243,425 Q242,413 240,405Z"/>
                                {{-- Left arm reaching UP to parent --}}
                                <path d="M237,410 Q234,405 231,400 Q229,397 228,394" stroke="#0e0a1f" stroke-width="2" fill="none" stroke-linecap="round"/>
                                {{-- Right arm --}}
                                <path d="M243,412 Q246,416 248,414" stroke="#0e0a1f" stroke-width="2" fill="none" stroke-linecap="round"/>

                                {{-- Shorter parent (Cassie) — right --}}
                                <ellipse cx="260" cy="383" rx="5.5" ry="6"/>{{-- head --}}
                                <path d="M260,389 Q258,398 256,412 Q255,422 254,435 L257,435 Q257.5,425 259,417 L261,417 Q262.5,425 263,435 L266,435 Q265,422 263,412 Q261,398 260,389Z"/>
                                {{-- Left arm --}}
                                <path d="M256,396 Q253,402 254,410" stroke="#0e0a1f" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                {{-- Right arm --}}
                                <path d="M264,396 Q267,404 265,410" stroke="#0e0a1f" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                {{-- Cassie's hair hint (slightly longer) --}}
                                <path d="M255,383 Q254,388 255,392" stroke="#0e0a1f" stroke-width="2" fill="none" stroke-linecap="round"/>
                                <path d="M265,383 Q266,388 265,392" stroke="#0e0a1f" stroke-width="2" fill="none" stroke-linecap="round"/>
                            </g>

                            {{-- Pixie dust / magical particles --}}
                            <circle cx="235" cy="375" r="1.5" fill="#f0c75e" class="pixie" style="--px-dur:3s;--px-delay:0s" opacity="0.8"/>
                            <circle cx="248" cy="395" r="1" fill="#f0c75e" class="pixie" style="--px-dur:3.5s;--px-delay:0.5s" opacity="0.7"/>
                            <circle cx="255" cy="380" r="1.2" fill="#d4a843" class="pixie" style="--px-dur:2.8s;--px-delay:1s" opacity="0.8"/>
                            <circle cx="222" cy="390" r="0.8" fill="#f0c75e" class="pixie" style="--px-dur:3.2s;--px-delay:1.5s" opacity="0.6"/>
                            <circle cx="265" cy="370" r="1.3" fill="#f0c75e" class="pixie" style="--px-dur:4s;--px-delay:0.3s" opacity="0.7"/>
                            <circle cx="240" cy="360" r="1" fill="#d4a843" class="pixie" style="--px-dur:2.5s;--px-delay:2s" opacity="0.9"/>
                            <circle cx="260" cy="350" r="0.9" fill="#f0c75e" class="pixie" style="--px-dur:3.8s;--px-delay:0.8s" opacity="0.6"/>
                            <circle cx="230" cy="345" r="1.1" fill="#f0c75e" class="pixie" style="--px-dur:3s;--px-delay:1.2s" opacity="0.7"/>
                            <circle cx="250" cy="330" r="0.7" fill="#d4a843" class="pixie" style="--px-dur:3.5s;--px-delay:2.5s" opacity="0.5"/>
                            <circle cx="245" cy="310" r="1" fill="#f0c75e" class="pixie" style="--px-dur:4s;--px-delay:0.6s" opacity="0.6"/>
                            {{-- Extra pixie dust near castle --}}
                            <circle cx="210" cy="300" r="0.8" fill="#f0c75e" class="pixie" style="--px-dur:3.3s;--px-delay:1.8s" opacity="0.5"/>
                            <circle cx="290" cy="295" r="0.9" fill="#d4a843" class="pixie" style="--px-dur:2.7s;--px-delay:0.9s" opacity="0.5"/>
                            <circle cx="270" cy="315" r="1" fill="#f0c75e" class="pixie" style="--px-dur:3.6s;--px-delay:2.2s" opacity="0.6"/>
                        </svg>
                    </div>
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

    {{-- Latest Posts Grid --}}
    <section class="py-16 md:py-24 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-end justify-between mb-12" data-animate>
                <div>
                    <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">Latest Stories</span>
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Tips, Guides &amp; Disney Life</h2>
                </div>
                <a href="/blog" class="hidden sm:inline-flex items-center gap-1 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                    View all
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if($latestPosts->count())
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
            @else
                <div class="text-center py-16 bg-white rounded-2xl border border-navy/5">
                    <span class="text-4xl mb-4 block">📝</span>
                    <p class="text-navy/65 text-lg font-body">Blog posts are on the way!</p>
                    <p class="text-navy/40 text-sm mt-2 font-body">We're writing up our best Disney tips and stories.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Guides Teaser --}}
    @if($popularGuides->count())
        <section class="py-14 md:py-20 bg-cream">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="flex items-end justify-between mb-10" data-animate>
                    <div>
                        <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">📖 Essential Reading</span>
                        <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-1">Park Guides</h2>
                    </div>
                    <a href="/guides" class="hidden sm:inline-flex items-center gap-1 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                        All guides
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($popularGuides as $guide)
                        <a href="/guides/{{ $guide->slug }}" class="group bg-white rounded-xl p-5 border border-navy/5 hover:border-gold/30 hover:shadow-lg transition-all duration-300 hover:-translate-y-1" data-animate>
                            <div class="flex items-start gap-3 mb-3">
                                @if($guide->icon)
                                    <span class="text-2xl flex-shrink-0">{{ $guide->icon }}</span>
                                @else
                                    <span class="text-2xl flex-shrink-0">📋</span>
                                @endif
                                <div class="min-w-0">
                                    @if($guide->park)
                                        <span class="text-purple/60 text-xs font-semibold uppercase tracking-wider font-body">{{ $guide->park }}</span>
                                    @endif
                                    <h3 class="font-heading text-base font-semibold text-navy group-hover:text-purple transition-colors leading-snug">{{ $guide->title }}</h3>
                                </div>
                            </div>
                            @if($guide->excerpt)
                                <p class="text-navy/50 text-sm leading-relaxed line-clamp-2 font-body">{{ Str::limit($guide->excerpt, 100) }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-8 sm:hidden">
                    <a href="/guides" class="text-purple hover:text-navy font-semibold text-sm transition-colors font-body">All guides →</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Wave: Cream → White --}}
    <div class="bg-white" aria-hidden="true">
        <svg viewBox="0 0 1440 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block -mt-px">
            <path d="M0 0C480 40 960 0 1440 30V40H0V0Z" fill="#fef9ef"/>
        </svg>
    </div>

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
                <div class="text-center py-10 bg-cream/50 rounded-2xl">
                    <span class="text-3xl mb-3 block">🎙️</span>
                    <p class="text-navy/65 font-body">Our first episode is coming soon!</p>
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
            <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                @csrf
                <input type="email" name="email" placeholder="your@email.com" required
                    class="newsletter-input flex-1 px-5 py-3.5 min-h-[48px] rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-gold/60 focus:border-gold/40 text-sm font-body transition-all duration-300">
                <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold px-7 py-3.5 min-h-[48px] rounded-full transition-all duration-300 text-sm font-body hover:shadow-lg hover:shadow-gold/30 hover:-translate-y-0.5 hover:scale-105 active:scale-95">
                    Subscribe
                </button>
            </form>
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
