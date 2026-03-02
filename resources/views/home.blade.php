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

        /* === Cinematic Reveal Animations === */
        @keyframes revealFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes revealRiseUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes revealWalkIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fogDrift {
            0%, 100% { transform: translateX(-15px); }
            50% { transform: translateX(15px); }
        }
        @keyframes fogDrift2 {
            0%, 100% { transform: translateX(10px); }
            50% { transform: translateX(-20px); }
        }
        @keyframes lightRaysRotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes shootingStar {
            0% { transform: translate(0, 0) scale(1); opacity: 0; }
            5% { opacity: 1; }
            15% { opacity: 1; }
            20% { transform: translate(-120px, 60px) scale(0.3); opacity: 0; }
            100% { opacity: 0; }
        }
        @keyframes shootingTrail {
            0% { stroke-dashoffset: 150; opacity: 0; }
            5% { opacity: 0.8; }
            15% { opacity: 0.6; }
            20% { stroke-dashoffset: 0; opacity: 0; }
            100% { opacity: 0; }
        }
        @keyframes lanternReveal {
            from { opacity: 0; transform: scale(0); }
            to { opacity: 0.5; transform: scale(1); }
        }
        @keyframes pixieDustFloat {
            0% { transform: translateY(0) scale(1); opacity: 0.8; }
            100% { transform: translateY(-50px) scale(0.2); opacity: 0; }
        }
        @keyframes mobileFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        /* Parallax layers */
        .scene-parallax-layer { will-change: transform; transition: transform 0.1s linear; }

        /* Cinematic reveal: hidden by default, revealed by .scene-loaded */
        .scene-container .reveal-stars { opacity: 0; }
        .scene-container .reveal-moonlight { opacity: 0; }
        .scene-container .reveal-castle { opacity: 0; transform: translateY(40px); }
        .scene-container .reveal-lightrays { opacity: 0; }
        .scene-container .reveal-family { opacity: 0; transform: translateX(-30px); }
        .scene-container .reveal-lanterns circle { opacity: 0; transform: scale(0); transform-origin: center; }
        .scene-container .reveal-fireworks { opacity: 0; }
        .scene-container .reveal-pixiedust { opacity: 0; }
        .scene-container .reveal-fog { opacity: 0; }

        .scene-loaded .reveal-stars {
            animation: revealFadeIn 0.8s ease-out 0.3s forwards;
        }
        .scene-loaded .reveal-moonlight {
            animation: revealFadeIn 1s ease-out 0.6s forwards;
        }
        .scene-loaded .reveal-castle {
            animation: revealRiseUp 1s ease-out 1s forwards;
        }
        .scene-loaded .reveal-lightrays {
            animation: revealFadeIn 0.8s ease-out 1.5s forwards;
        }
        .scene-loaded .reveal-family {
            animation: revealWalkIn 0.8s ease-out 1.8s forwards;
        }
        .scene-loaded .reveal-lanterns circle:nth-child(1) { animation: lanternReveal 0.3s ease-out 2.0s forwards; }
        .scene-loaded .reveal-lanterns circle:nth-child(2) { animation: lanternReveal 0.3s ease-out 2.1s forwards; }
        .scene-loaded .reveal-lanterns circle:nth-child(3) { animation: lanternReveal 0.3s ease-out 2.2s forwards; }
        .scene-loaded .reveal-lanterns circle:nth-child(4) { animation: lanternReveal 0.3s ease-out 2.15s forwards; }
        .scene-loaded .reveal-lanterns circle:nth-child(5) { animation: lanternReveal 0.3s ease-out 2.25s forwards; }
        .scene-loaded .reveal-lanterns circle:nth-child(6) { animation: lanternReveal 0.3s ease-out 2.35s forwards; }
        .scene-loaded .reveal-fireworks {
            animation: revealFadeIn 0.6s ease-out 2.3s forwards;
        }
        .scene-loaded .reveal-pixiedust {
            animation: revealFadeIn 0.8s ease-out 2.5s forwards;
        }
        .scene-loaded .reveal-fog {
            animation: revealFadeIn 1.2s ease-out 3.0s forwards;
        }

        /* Fog drift after reveal */
        .scene-loaded .fog-element-1 { animation: revealFadeIn 1.2s ease-out 3.0s forwards, fogDrift 13s ease-in-out 4.2s infinite; }
        .scene-loaded .fog-element-2 { animation: revealFadeIn 1.2s ease-out 3.2s forwards, fogDrift2 15s ease-in-out 4.4s infinite; }
        .scene-loaded .fog-element-3 { animation: revealFadeIn 1.2s ease-out 3.4s forwards, fogDrift 12s ease-in-out 4.6s infinite; }

        /* Light rays rotation after reveal */
        .scene-loaded .lightrays-rotate {
            animation: revealFadeIn 0.8s ease-out 1.5s forwards, lightRaysRotate 30s linear 2.3s infinite;
        }

        /* Shooting star */
        .shooting-star-head { animation: shootingStar 9s ease-in infinite; animation-delay: 3s; }
        .shooting-star-trail { stroke-dasharray: 150; animation: shootingTrail 9s ease-in infinite; animation-delay: 3s; }
        .shooting-star-2-head { animation: shootingStar 11s ease-in infinite; animation-delay: 7s; }
        .shooting-star-2-trail { stroke-dasharray: 150; animation: shootingTrail 11s ease-in infinite; animation-delay: 7s; }

        /* Pixie dust cursor particle */
        .pixie-cursor-particle {
            position: absolute;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            pointer-events: none;
            animation: pixieDustFloat var(--pd-dur, 1s) ease-out forwards;
        }

        /* Blend mode overlays */
        .moonlight-overlay {
            position: absolute; inset: 0; pointer-events: none; z-index: 2;
            background: radial-gradient(ellipse at 50% 20%, rgba(200,210,255,0.15) 0%, transparent 60%);
            mix-blend-mode: soft-light;
        }
        .warm-glow-overlay {
            position: absolute; pointer-events: none; z-index: 2;
            width: 80px; height: 60px;
            left: 46%; top: 56%;
            background: radial-gradient(ellipse, rgba(212,168,67,0.2) 0%, transparent 70%);
            mix-blend-mode: screen;
        }

        /* Mobile fallback — gentle float */
        @media (hover: none) {
            .scene-parallax-layer { animation: mobileFloat 6s ease-in-out infinite; }
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

                {{-- Right side: Magical illustrated scene --}}
                <div class="lg:w-[42%] relative w-full max-w-md lg:max-w-none mx-auto lg:mx-0">
                    <div class="scene-container relative h-[280px] sm:h-[350px] lg:h-[480px]" id="magicalScene">
                        {{-- Blend mode overlays --}}
                        <div class="moonlight-overlay" aria-hidden="true"></div>
                        <div class="warm-glow-overlay" aria-hidden="true"></div>

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
                                {{-- Moonlight glow --}}
                                <radialGradient id="moonGlow" cx="50%" cy="25%" r="45%">
                                    <stop offset="0%" stop-color="#dde4ff" stop-opacity="0.12"/>
                                    <stop offset="50%" stop-color="#b8c4ff" stop-opacity="0.05"/>
                                    <stop offset="100%" stop-color="#1a1040" stop-opacity="0"/>
                                </radialGradient>
                                {{-- Light rays gradient --}}
                                <radialGradient id="lightRays" cx="50%" cy="50%" r="50%">
                                    <stop offset="0%" stop-color="#f0c75e" stop-opacity="0.12"/>
                                    <stop offset="40%" stop-color="#d4a843" stop-opacity="0.05"/>
                                    <stop offset="100%" stop-color="#1a1040" stop-opacity="0"/>
                                </radialGradient>
                                {{-- Fog blur filter --}}
                                <filter id="fogBlur">
                                    <feGaussianBlur stdDeviation="8"/>
                                </filter>
                                {{-- Water reflection blur --}}
                                <filter id="waterBlur">
                                    <feGaussianBlur stdDeviation="3"/>
                                    <feComponentTransfer>
                                        <feFuncA type="linear" slope="0.15"/>
                                    </feComponentTransfer>
                                </filter>
                                {{-- Shooting star trail gradient --}}
                                <linearGradient id="trailGrad" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0%" stop-color="white" stop-opacity="0"/>
                                    <stop offset="80%" stop-color="white" stop-opacity="0.6"/>
                                    <stop offset="100%" stop-color="white" stop-opacity="1"/>
                                </linearGradient>
                                {{-- Rim lighting filter --}}
                                <filter id="rimLight">
                                    <feGaussianBlur stdDeviation="1.5"/>
                                    <feComponentTransfer>
                                        <feFuncA type="linear" slope="0.3"/>
                                    </feComponentTransfer>
                                </filter>
                                {{-- Sparkle shape --}}
                                <g id="sparkle4">
                                    <line x1="0" y1="-4" x2="0" y2="4" stroke="#f0c75e" stroke-width="1" stroke-linecap="round"/>
                                    <line x1="-4" y1="0" x2="4" y2="0" stroke="#f0c75e" stroke-width="1" stroke-linecap="round"/>
                                    <line x1="-2.5" y1="-2.5" x2="2.5" y2="2.5" stroke="#f0c75e" stroke-width="0.6" stroke-linecap="round"/>
                                    <line x1="2.5" y1="-2.5" x2="-2.5" y2="2.5" stroke="#f0c75e" stroke-width="0.6" stroke-linecap="round"/>
                                </g>
                                {{-- Water reflection mask --}}
                                <linearGradient id="waterMask" x1="0" y1="0" x2="1" y2="0">
                                    <stop offset="0%" stop-color="white" stop-opacity="0"/>
                                    <stop offset="30%" stop-color="white" stop-opacity="1"/>
                                    <stop offset="70%" stop-color="white" stop-opacity="1"/>
                                    <stop offset="100%" stop-color="white" stop-opacity="0"/>
                                </linearGradient>
                                <mask id="reflectionMask">
                                    <rect x="140" y="310" width="220" height="80" fill="url(#waterMask)"/>
                                </mask>
                            </defs>

                            {{-- Sky background --}}
                            <rect width="500" height="500" fill="url(#skyGrad)"/>

                            {{-- LAYER 1: Background stars (most parallax) --}}
                            <g class="scene-parallax-layer reveal-stars" data-parallax-layer="1">
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

                                {{-- Moonlight glow --}}
                                <g class="reveal-moonlight">
                                    <circle cx="250" cy="100" r="180" fill="url(#moonGlow)"/>
                                </g>

                                {{-- Shooting stars --}}
                                <g>
                                    <circle cx="420" cy="30" r="2" fill="white" class="shooting-star-head"/>
                                    <line x1="420" y1="30" x2="540" y2="-30" stroke="url(#trailGrad)" stroke-width="1.5" stroke-linecap="round" class="shooting-star-trail"/>
                                </g>
                                <g>
                                    <circle cx="380" cy="50" r="1.5" fill="white" class="shooting-star-2-head"/>
                                    <line x1="380" y1="50" x2="480" y2="0" stroke="url(#trailGrad)" stroke-width="1" stroke-linecap="round" class="shooting-star-2-trail"/>
                                </g>
                            </g>

                            {{-- LAYER 2: Fireworks --}}
                            <g class="scene-parallax-layer reveal-fireworks" data-parallax-layer="2">
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
                            </g>

                            {{-- LAYER 3: Castle + atmosphere --}}
                            <g class="scene-parallax-layer" data-parallax-layer="3">
                                {{-- Light rays behind castle --}}
                                <g class="reveal-lightrays lightrays-rotate" style="transform-origin: 250px 180px;">
                                    <ellipse cx="250" cy="180" rx="120" ry="120" fill="url(#lightRays)" opacity="0.13"/>
                                    {{-- Radial ray lines for conic effect --}}
                                    <line x1="250" y1="180" x2="250" y2="60" stroke="#f0c75e" stroke-width="0.5" opacity="0.06"/>
                                    <line x1="250" y1="180" x2="340" y2="90" stroke="#f0c75e" stroke-width="0.5" opacity="0.05"/>
                                    <line x1="250" y1="180" x2="370" y2="180" stroke="#f0c75e" stroke-width="0.5" opacity="0.06"/>
                                    <line x1="250" y1="180" x2="160" y2="90" stroke="#f0c75e" stroke-width="0.5" opacity="0.05"/>
                                    <line x1="250" y1="180" x2="130" y2="180" stroke="#f0c75e" stroke-width="0.5" opacity="0.06"/>
                                    <line x1="250" y1="180" x2="300" y2="70" stroke="white" stroke-width="0.3" opacity="0.04"/>
                                    <line x1="250" y1="180" x2="200" y2="70" stroke="white" stroke-width="0.3" opacity="0.04"/>
                                </g>

                                {{-- Castle silhouette --}}
                                <g fill="#2d1b69" class="reveal-castle">
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
                                <ellipse cx="250" cy="300" rx="20" ry="18" fill="url(#entranceGlow)" class="glow-pulse reveal-castle"/>

                                {{-- Window warm glow dots --}}
                                <circle cx="212" cy="255" r="2" fill="#d4a843" opacity="0.25" class="glow-pulse reveal-castle"/>
                                <circle cx="287" cy="255" r="2" fill="#d4a843" opacity="0.25" class="glow-pulse reveal-castle"/>
                                <circle cx="250" cy="275" r="1.5" fill="#d4a843" opacity="0.2" class="glow-pulse reveal-castle"/>

                                {{-- Water reflection below castle --}}
                                <g mask="url(#reflectionMask)" class="reveal-castle" opacity="0.15">
                                    <g filter="url(#waterBlur)" transform="translate(0, 620) scale(1, -1)">
                                        <polygon points="250,120 248,135 245,160 243,185 240,210 237,240 236,260 264,260 263,240 260,210 257,185 255,160 252,135" fill="#3a2a7a"/>
                                        <polygon points="250,100 247,120 253,120" fill="#3a2a7a"/>
                                        <rect x="230" y="260" width="40" height="50" rx="2" fill="#3a2a7a"/>
                                        <rect x="195" y="230" width="35" height="80" rx="2" fill="#3a2a7a"/>
                                        <polygon points="212,195 198,230 227,230" fill="#3a2a7a"/>
                                        <rect x="270" y="230" width="35" height="80" rx="2" fill="#3a2a7a"/>
                                        <polygon points="287,195 273,230 302,230" fill="#3a2a7a"/>
                                        <rect x="170" y="270" width="25" height="40" rx="2" fill="#3a2a7a"/>
                                        <rect x="305" y="270" width="25" height="40" rx="2" fill="#3a2a7a"/>
                                        <rect x="170" y="295" width="160" height="15" rx="1" fill="#3a2a7a"/>
                                    </g>
                                </g>

                                {{-- Fog / mist at castle base --}}
                                <g class="reveal-fog">
                                    <ellipse cx="220" cy="310" rx="60" ry="12" fill="rgba(180,170,220,0.2)" filter="url(#fogBlur)" class="fog-element-1" opacity="0"/>
                                    <ellipse cx="280" cy="315" rx="55" ry="10" fill="rgba(255,255,255,0.15)" filter="url(#fogBlur)" class="fog-element-2" opacity="0"/>
                                    <ellipse cx="250" cy="320" rx="70" ry="8" fill="rgba(200,190,240,0.18)" filter="url(#fogBlur)" class="fog-element-3" opacity="0"/>
                                </g>
                            </g>

                            {{-- LAYER 4: Family + foreground (least parallax) --}}
                            <g class="scene-parallax-layer" data-parallax-layer="4">
                                {{-- Ground/path area --}}
                                <rect x="0" y="390" width="500" height="110" fill="url(#groundGrad)"/>
                                <ellipse cx="250" cy="400" rx="250" ry="30" fill="#0f0b22" opacity="0.5"/>

                                {{-- Walkway path --}}
                                <path d="M250,310 Q248,340 240,370 Q230,400 200,440 Q180,465 140,490" stroke="#2d1b69" stroke-width="28" fill="none" stroke-linecap="round" opacity="0.3"/>
                                <path d="M250,310 Q252,340 260,370 Q270,400 300,440 Q320,465 360,490" stroke="#2d1b69" stroke-width="28" fill="none" stroke-linecap="round" opacity="0.3"/>

                                {{-- Path lanterns --}}
                                <g class="reveal-lanterns">
                                    <circle cx="228" cy="380" r="1.5" fill="#d4a843" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0s"/>
                                    <circle cx="215" cy="410" r="1.5" fill="#d4a843" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0.4s"/>
                                    <circle cx="195" cy="440" r="1.5" fill="#d4a843" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0.8s"/>
                                    <circle cx="272" cy="380" r="1.5" fill="#d4a843" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0.2s"/>
                                    <circle cx="285" cy="410" r="1.5" fill="#d4a843" class="star-twinkle" style="--tw-dur:2s;--tw-delay:0.6s"/>
                                    <circle cx="305" cy="440" r="1.5" fill="#d4a843" class="star-twinkle" style="--tw-dur:2s;--tw-delay:1s"/>
                                </g>

                                {{-- Lantern glow halos --}}
                                <g class="reveal-lanterns" opacity="0.15">
                                    <circle cx="228" cy="380" r="6" fill="#d4a843" filter="url(#fogBlur)"/>
                                    <circle cx="272" cy="380" r="6" fill="#d4a843" filter="url(#fogBlur)"/>
                                    <circle cx="215" cy="410" r="5" fill="#d4a843" filter="url(#fogBlur)"/>
                                    <circle cx="285" cy="410" r="5" fill="#d4a843" filter="url(#fogBlur)"/>
                                </g>

                                {{-- Family silhouette --}}
                                <g fill="#0e0a1f" class="reveal-family">
                                    {{-- Rim lighting (duplicate offset + blur) --}}
                                    <g filter="url(#rimLight)" opacity="0.25" fill="none" stroke="#7b5eb5" stroke-width="1.5">
                                        <ellipse cx="225" cy="378" rx="7" ry="7.5"/>
                                        <ellipse cx="240" cy="400" rx="6" ry="6.5"/>
                                        <ellipse cx="260" cy="383" rx="6.5" ry="7"/>
                                    </g>

                                    {{-- Taller parent (Jeffrey) — left --}}
                                    <ellipse cx="225" cy="378" rx="6" ry="6.5"/>
                                    <path d="M225,384 Q222,395 220,410 Q218,420 217,435 L220,435 Q221,425 223,415 L225,415 Q227,425 228,435 L231,435 Q230,420 228,410 Q226,395 225,384Z"/>
                                    <path d="M222,392 Q220,398 222,404 Q224,408 228,410" stroke="#0e0a1f" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                    <path d="M228,392 Q232,400 230,408" stroke="#0e0a1f" stroke-width="2.5" fill="none" stroke-linecap="round"/>

                                    {{-- Child (Viola) — center, reaching up --}}
                                    <ellipse cx="240" cy="400" rx="5" ry="5.5"/>
                                    <path d="M240,405 Q238,413 237,425 Q236,432 235,440 L238,440 Q238.5,433 239,427 L241,427 Q241.5,433 242,440 L245,440 Q244,432 243,425 Q242,413 240,405Z"/>
                                    <path d="M237,410 Q234,405 231,400 Q229,397 228,394" stroke="#0e0a1f" stroke-width="2" fill="none" stroke-linecap="round"/>
                                    <path d="M243,412 Q246,416 248,414" stroke="#0e0a1f" stroke-width="2" fill="none" stroke-linecap="round"/>

                                    {{-- Shorter parent (Cassie) — right --}}
                                    <ellipse cx="260" cy="383" rx="5.5" ry="6"/>
                                    <path d="M260,389 Q258,398 256,412 Q255,422 254,435 L257,435 Q257.5,425 259,417 L261,417 Q262.5,425 263,435 L266,435 Q265,422 263,412 Q261,398 260,389Z"/>
                                    <path d="M256,396 Q253,402 254,410" stroke="#0e0a1f" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                    <path d="M264,396 Q267,404 265,410" stroke="#0e0a1f" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                                    <path d="M255,383 Q254,388 255,392" stroke="#0e0a1f" stroke-width="2" fill="none" stroke-linecap="round"/>
                                    <path d="M265,383 Q266,388 265,392" stroke="#0e0a1f" stroke-width="2" fill="none" stroke-linecap="round"/>
                                </g>

                                {{-- Pixie dust / magical particles --}}
                                <g class="reveal-pixiedust">
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
                                    <circle cx="210" cy="300" r="0.8" fill="#f0c75e" class="pixie" style="--px-dur:3.3s;--px-delay:1.8s" opacity="0.5"/>
                                    <circle cx="290" cy="295" r="0.9" fill="#d4a843" class="pixie" style="--px-dur:2.7s;--px-delay:0.9s" opacity="0.5"/>
                                    <circle cx="270" cy="315" r="1" fill="#f0c75e" class="pixie" style="--px-dur:3.6s;--px-delay:2.2s" opacity="0.6"/>
                                </g>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hero scene interactivity --}}
        <script>
        (function() {
            const scene = document.getElementById('magicalScene');
            if (!scene) return;

            // Cinematic reveal
            setTimeout(function() { scene.classList.add('scene-loaded'); }, 100);

            const hasHover = window.matchMedia('(hover: hover)').matches;
            const layers = scene.querySelectorAll('[data-parallax-layer]');
            const depths = { '1': 18, '2': 11, '3': 6, '4': 2.5 };

            // Mouse-tracking parallax
            if (hasHover && layers.length) {
                let mx = 0, my = 0, cx = 0, cy = 0, rafId = null;
                scene.addEventListener('mousemove', function(e) {
                    const r = scene.getBoundingClientRect();
                    mx = ((e.clientX - r.left) / r.width - 0.5) * 2;
                    my = ((e.clientY - r.top) / r.height - 0.5) * 2;
                    if (!rafId) rafId = requestAnimationFrame(animateParallax);
                });
                scene.addEventListener('mouseleave', function() {
                    mx = 0; my = 0;
                    if (!rafId) rafId = requestAnimationFrame(animateParallax);
                });
                function animateParallax() {
                    cx += (mx - cx) * 0.08;
                    cy += (my - cy) * 0.08;
                    layers.forEach(function(l) {
                        var d = depths[l.dataset.parallaxLayer] || 5;
                        l.style.transform = 'translate(' + (-cx * d) + 'px,' + (-cy * d) + 'px)';
                    });
                    if (Math.abs(mx - cx) > 0.001 || Math.abs(my - cy) > 0.001) {
                        rafId = requestAnimationFrame(animateParallax);
                    } else { rafId = null; }
                }
            }

            // Interactive pixie dust cursor trail
            if (hasHover) {
                var particles = 0, lastSpawn = 0;
                scene.style.position = 'relative';
                scene.addEventListener('mousemove', function(e) {
                    var now = Date.now();
                    if (now - lastSpawn < 50 || particles >= 20) return;
                    lastSpawn = now;
                    var r = scene.getBoundingClientRect();
                    var x = e.clientX - r.left + (Math.random() - 0.5) * 20;
                    var y = e.clientY - r.top + (Math.random() - 0.5) * 20;
                    var dur = 0.8 + Math.random() * 0.4;
                    var size = 4 + Math.random() * 2;
                    var hue = 40 + Math.random() * 15;
                    var p = document.createElement('div');
                    p.className = 'pixie-cursor-particle';
                    p.style.cssText = 'left:' + x + 'px;top:' + y + 'px;width:' + size + 'px;height:' + size + 'px;background:hsl(' + hue + ',70%,60%);--pd-dur:' + dur + 's;z-index:10;';
                    scene.appendChild(p);
                    particles++;
                    p.addEventListener('animationend', function() { p.remove(); particles--; });
                });
            }
        })();
        </script>

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
