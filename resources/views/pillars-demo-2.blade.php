@extends('layouts.app')

@section('title', 'Content Pillars — Creative Options (Page 2)')

@section('content')
<style>
    .option-label {
        background: linear-gradient(135deg, #1a1040, #2d1b69);
        color: #d4a843;
        font-family: 'Poppins', sans-serif;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.15em;
        text-transform: uppercase;
        padding: 0.75rem 2rem;
        text-align: center;
    }
    .option-divider {
        height: 3px;
        background: linear-gradient(90deg, transparent, #d4a843, transparent);
    }

    /* ═══ OPTION F: Park Ticket Stubs ═══ */
    .ticket {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94), box-shadow 0.4s ease;
        cursor: pointer;
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

    /* ═══ OPTION G: Storybook Chapters ═══ */
    .chapter {
        position: relative;
        padding: 2.5rem 2rem 2.5rem 5rem;
        border-left: 3px solid rgba(212,168,67,0.15);
        transition: all 0.35s ease;
    }
    .chapter:hover {
        border-left-color: #d4a843;
        background: rgba(212,168,67,0.03);
    }
    .chapter-number {
        position: absolute;
        left: -22px;
        top: 2.5rem;
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #d4a843, #b8922e);
        color: #1a1040;
        font-family: 'Playfair Display', serif;
        font-weight: 800;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(212,168,67,0.3);
        transition: transform 0.3s ease;
    }
    .chapter:hover .chapter-number {
        transform: scale(1.15);
    }
    .chapter-ornament {
        font-family: 'Playfair Display', serif;
        color: rgba(212,168,67,0.2);
        font-size: 0.8rem;
        letter-spacing: 0.3em;
    }

    /* ═══ OPTION H: Orbit ═══ */
    .orbit-container {
        position: relative;
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        aspect-ratio: 1;
    }
    .orbit-ring {
        position: absolute;
        inset: 10%;
        border-radius: 50%;
        border: 1px dashed rgba(212,168,67,0.15);
    }
    .orbit-ring-2 {
        position: absolute;
        inset: 25%;
        border-radius: 50%;
        border: 1px dashed rgba(91,62,158,0.1);
    }
    .orbit-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
        z-index: 5;
    }
    .orbit-node {
        position: absolute;
        width: 140px;
        text-align: center;
        z-index: 10;
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    .orbit-node:hover {
        transform: scale(1.1);
    }
    .orbit-node:hover .orbit-icon {
        box-shadow: 0 8px 25px rgba(212,168,67,0.3);
    }
    .orbit-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        margin: 0 auto 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: box-shadow 0.3s ease;
    }
    /* Positioning the 4 nodes */
    .orbit-n-top { top: 2%; left: 50%; transform: translateX(-50%); }
    .orbit-n-right { top: 50%; right: 0; transform: translateY(-50%); }
    .orbit-n-bottom { bottom: 2%; left: 50%; transform: translateX(-50%); }
    .orbit-n-left { top: 50%; left: 0; transform: translateY(-50%); }
    .orbit-n-top:hover { transform: translateX(-50%) scale(1.1); }
    .orbit-n-right:hover { transform: translateY(-50%) scale(1.1); }
    .orbit-n-bottom:hover { transform: translateX(-50%) scale(1.1); }
    .orbit-n-left:hover { transform: translateY(-50%) scale(1.1); }

    /* ═══ OPTION I: Flip Cards ═══ */
    .flip-card {
        perspective: 1000px;
        height: 280px;
        cursor: pointer;
    }
    .flip-inner {
        position: relative;
        width: 100%;
        height: 100%;
        transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
    }
    .flip-card:hover .flip-inner {
        transform: rotateY(180deg);
    }
    .flip-front, .flip-back {
        position: absolute;
        inset: 0;
        backface-visibility: hidden;
        border-radius: 1.25rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    .flip-front {
        background: white;
        border: 1px solid rgba(26,16,64,0.06);
    }
    .flip-back {
        transform: rotateY(180deg);
        text-align: center;
    }

    /* ═══ OPTION J: Animated Counter Reveal ═══ */
    .reveal-card {
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 2.5rem 2rem;
        background: white;
        border: 1px solid rgba(26,16,64,0.05);
        transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .reveal-card:hover {
        border-color: rgba(212,168,67,0.3);
        box-shadow: 0 12px 30px rgba(26,16,64,0.08);
    }
    .reveal-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--accent);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .reveal-card:hover::before {
        transform: scaleX(1);
    }
    .reveal-card .reveal-desc {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: max-height 0.4s ease, opacity 0.3s ease, margin 0.3s ease;
        margin-top: 0;
    }
    .reveal-card:hover .reveal-desc {
        max-height: 100px;
        opacity: 1;
        margin-top: 0.75rem;
    }
    .reveal-number {
        font-family: 'Playfair Display', serif;
        font-size: 4rem;
        font-weight: 800;
        line-height: 1;
        transition: all 0.4s ease;
        background: linear-gradient(135deg, var(--accent), var(--accent-light));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .reveal-card:hover .reveal-number {
        font-size: 2.5rem;
    }
</style>

{{-- Page Header --}}
<section style="background: linear-gradient(135deg, #1a1040, #2d1b69); padding: 3rem 1rem; text-align: center;">
    <h1 class="font-heading text-3xl font-bold text-white mb-2">Creative Options (Page 2)</h1>
    <p class="font-body text-white/50 text-sm">Five more creative approaches. Options F–J.</p>
    <a href="/pillars-demo" class="font-body text-gold/60 hover:text-gold text-sm mt-2 inline-block transition-colors">← Back to Options A–E</a>
</section>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION F: Park Ticket Stubs --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option F — Park Ticket Stubs</div>

<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Your Guide to the Parks</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            {{-- Ticket 1 --}}
            <div class="ticket" style="background: linear-gradient(135deg, #e88d9a, #d4627a); color: #fce4e8;">
                <div class="p-7 pr-16 relative">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] font-body mb-1" style="color: rgba(255,255,255,0.5);">Mouse28 · Admit One</div>
                    <h3 class="font-heading text-2xl font-bold text-white mb-2">Accessibility</h3>
                    <p class="font-body text-sm leading-relaxed" style="color: rgba(255,255,255,0.7);">DAS tips, sensory guides, and honest advice for neurodivergent families navigating the parks.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                    </div>
                </div>
                <div class="ticket-hole" style="color: #fce4e8;"></div>
                <div class="ticket-tear" style="color: #fef9ef;"></div>
            </div>

            {{-- Ticket 2 --}}
            <div class="ticket" style="background: linear-gradient(135deg, #7b5eb5, #5b3e9e); color: #ede4f7;">
                <div class="p-7 pr-16 relative">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] font-body mb-1" style="color: rgba(255,255,255,0.5);">Mouse28 · Admit One</div>
                    <h3 class="font-heading text-2xl font-bold text-white mb-2">Park Strategy</h3>
                    <p class="font-body text-sm leading-relaxed" style="color: rgba(255,255,255,0.7);">Ride tips, rope drop plans, and weekly-tested strategies from locals who know every shortcut.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                    </div>
                </div>
                <div class="ticket-hole" style="color: #ede4f7;"></div>
                <div class="ticket-tear" style="color: #fef9ef;"></div>
            </div>

            {{-- Ticket 3 --}}
            <div class="ticket" style="background: linear-gradient(135deg, #f0c75e, #d4a843); color: #fdf3dc;">
                <div class="p-7 pr-16 relative">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] font-body mb-1" style="color: rgba(26,16,64,0.4);">Mouse28 · Admit One</div>
                    <h3 class="font-heading text-2xl font-bold text-navy mb-2">Food & Reviews</h3>
                    <p class="font-body text-sm leading-relaxed" style="color: rgba(26,16,64,0.6);">Honest reviews of restaurants, snacks, and resorts — including diabetic-friendly options.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="bg-navy/15 text-navy text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                    </div>
                </div>
                <div class="ticket-hole" style="color: #fdf3dc;"></div>
                <div class="ticket-tear" style="color: #fef9ef;"></div>
            </div>

            {{-- Ticket 4 --}}
            <div class="ticket" style="background: linear-gradient(135deg, #5ba4ad, #4a90a4); color: #ddf0f5;">
                <div class="p-7 pr-16 relative">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] font-body mb-1" style="color: rgba(255,255,255,0.5);">Mouse28 · Admit One</div>
                    <h3 class="font-heading text-2xl font-bold text-white mb-2">Family Stories</h3>
                    <p class="font-body text-sm leading-relaxed" style="color: rgba(255,255,255,0.7);">Real moments from our weekly trips — the magical, the messy, and everything in between.</p>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full font-body uppercase tracking-wider">Explore →</span>
                    </div>
                </div>
                <div class="ticket-hole" style="color: #ddf0f5;"></div>
                <div class="ticket-tear" style="color: #fef9ef;"></div>
            </div>
        </div>
    </div>
</section>

<div class="option-divider"></div>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION G: Storybook Chapters --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option G — Storybook Chapters</div>

<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-2xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
            <div class="chapter-ornament mb-3">— ✦ —</div>
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">The Mouse28 Story</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2" style="font-style: italic;">What We Cover</h2>
            <div class="chapter-ornament mt-3">— ✧ —</div>
        </div>

        <div class="relative ml-5">
            <div class="chapter">
                <div class="chapter-number">I</div>
                <h3 class="font-heading text-xl font-bold text-navy mb-2">Accessibility</h3>
                <p class="text-navy/55 font-body leading-relaxed text-sm">DAS tips, sensory guides, and honest advice for neurodivergent families navigating Disney parks. We share what works for our daughter and what we've learned from years of weekly visits.</p>
                <div class="chapter-ornament mt-4">· · ·</div>
            </div>

            <div class="chapter">
                <div class="chapter-number">II</div>
                <h3 class="font-heading text-xl font-bold text-navy mb-2">Park Strategy</h3>
                <p class="text-navy/55 font-body leading-relaxed text-sm">Ride tips, rope drop plans, and weekly-tested strategies from locals who know every shortcut. These aren't theory — they're field-tested 52 weeks a year.</p>
                <div class="chapter-ornament mt-4">· · ·</div>
            </div>

            <div class="chapter">
                <div class="chapter-number">III</div>
                <h3 class="font-heading text-xl font-bold text-navy mb-2">Food & Reviews</h3>
                <p class="text-navy/55 font-body leading-relaxed text-sm">Honest reviews of restaurants, snacks, and resorts — including diabetic-friendly options. We eat our way through the parks so you know what's worth your money.</p>
                <div class="chapter-ornament mt-4">· · ·</div>
            </div>

            <div class="chapter">
                <div class="chapter-number">IV</div>
                <h3 class="font-heading text-xl font-bold text-navy mb-2">Family Stories</h3>
                <p class="text-navy/55 font-body leading-relaxed text-sm">Real moments from our weekly Disney trips — the magical, the messy, and everything in between. No filter, just our family figuring it out one park day at a time.</p>
            </div>
        </div>
    </div>
</section>

<div class="option-divider"></div>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION H: Constellation Orbit --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option H — Constellation Orbit</div>

<section class="py-16 md:py-24" style="background: linear-gradient(135deg, #1a1040, #2d1b69);">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-6">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
        </div>

        {{-- Desktop: orbit layout --}}
        <div class="hidden md:block">
            <div class="orbit-container">
                <div class="orbit-ring"></div>
                <div class="orbit-ring-2"></div>

                {{-- Subtle connecting lines --}}
                <svg class="absolute inset-0 w-full h-full" style="z-index: 1; opacity: 0.08;">
                    <line x1="50%" y1="15%" x2="85%" y2="50%" stroke="#d4a843" stroke-width="1"/>
                    <line x1="85%" y1="50%" x2="50%" y2="85%" stroke="#d4a843" stroke-width="1"/>
                    <line x1="50%" y1="85%" x2="15%" y2="50%" stroke="#d4a843" stroke-width="1"/>
                    <line x1="15%" y1="50%" x2="50%" y2="15%" stroke="#d4a843" stroke-width="1"/>
                </svg>

                {{-- Center --}}
                <div class="orbit-center">
                    <div class="w-20 h-20 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: linear-gradient(135deg, #d4a843, #b8922e); box-shadow: 0 0 40px rgba(212,168,67,0.3);">
                        <span class="font-heading text-2xl font-bold text-navy">M28</span>
                    </div>
                    <h2 class="font-heading text-xl font-bold text-white">Disney,<br>Your Way</h2>
                </div>

                {{-- Top: Accessibility --}}
                <div class="orbit-node orbit-n-top">
                    <div class="orbit-icon" style="background: linear-gradient(135deg, #e88d9a, #d4627a); box-shadow: 0 4px 15px rgba(212,99,122,0.3);">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                    </div>
                    <h3 class="font-heading text-base font-bold text-white">Accessibility</h3>
                    <p class="font-body text-[11px] leading-snug mt-1" style="color: rgba(254,249,239,0.4);">DAS tips & sensory guides</p>
                </div>

                {{-- Right: Park Strategy --}}
                <div class="orbit-node orbit-n-right">
                    <div class="orbit-icon" style="background: linear-gradient(135deg, #7b5eb5, #5b3e9e); box-shadow: 0 4px 15px rgba(91,62,158,0.3);">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                    </div>
                    <h3 class="font-heading text-base font-bold text-white">Park Strategy</h3>
                    <p class="font-body text-[11px] leading-snug mt-1" style="color: rgba(254,249,239,0.4);">Weekly-tested tips</p>
                </div>

                {{-- Bottom: Food & Reviews --}}
                <div class="orbit-node orbit-n-bottom">
                    <div class="orbit-icon" style="background: linear-gradient(135deg, #f0c75e, #d4a843); box-shadow: 0 4px 15px rgba(212,168,67,0.3);">
                        <svg class="w-7 h-7 text-navy" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                    </div>
                    <h3 class="font-heading text-base font-bold text-white">Food & Reviews</h3>
                    <p class="font-body text-[11px] leading-snug mt-1" style="color: rgba(254,249,239,0.4);">Honest Disney dining</p>
                </div>

                {{-- Left: Family Stories --}}
                <div class="orbit-node orbit-n-left">
                    <div class="orbit-icon" style="background: linear-gradient(135deg, #5ba4ad, #4a90a4); box-shadow: 0 4px 15px rgba(74,144,164,0.3);">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                    </div>
                    <h3 class="font-heading text-base font-bold text-white">Family Stories</h3>
                    <p class="font-body text-[11px] leading-snug mt-1" style="color: rgba(254,249,239,0.4);">Real weekly moments</p>
                </div>
            </div>
        </div>

        {{-- Mobile: simple grid fallback --}}
        <div class="md:hidden">
            <h2 class="font-heading text-2xl font-bold text-white text-center mb-8">Disney, Your Way</h2>
            <div class="grid grid-cols-2 gap-4">
                @foreach([
                    ['Accessibility', '#e88d9a', '#d4627a', 'DAS & sensory', 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z'],
                    ['Park Strategy', '#7b5eb5', '#5b3e9e', 'Tips & plans', 'M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z'],
                    ['Food & Reviews', '#f0c75e', '#d4a843', 'Honest dining', 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z'],
                    ['Family Stories', '#5ba4ad', '#4a90a4', 'Real moments', 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z']
                ] as $item)
                    <div class="text-center p-5 rounded-xl" style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.06);">
                        <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center" style="background: linear-gradient(135deg, {{ $item[1] }}, {{ $item[2] }});">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item[4] }}"/></svg>
                        </div>
                        <h3 class="font-heading text-sm font-bold text-white">{{ $item[0] }}</h3>
                        <p class="font-body text-[11px] mt-1" style="color: rgba(254,249,239,0.4);">{{ $item[3] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<div class="option-divider"></div>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION I: 3D Flip Cards --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option I — 3D Flip Cards (Hover to Reveal)</div>

<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Disney, Your Way</h2>
            <p class="text-navy/40 text-sm mt-2 font-body">Hover each card to learn more</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Flip 1 --}}
            <div class="flip-card">
                <div class="flip-inner">
                    <div class="flip-front">
                        <div class="w-20 h-20 rounded-2xl mb-5 flex items-center justify-center" style="background: linear-gradient(135deg, #fce4e8, #f8d0d6);">
                            <svg class="w-10 h-10" style="color: #d4627a;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                        </div>
                        <h3 class="font-heading text-xl font-bold text-navy">Accessibility</h3>
                    </div>
                    <div class="flip-back" style="background: linear-gradient(135deg, #e88d9a, #d4627a);">
                        <svg class="w-8 h-8 text-white/30 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                        <h3 class="font-heading text-xl font-bold text-white mb-2">Accessibility</h3>
                        <p class="text-white/80 text-sm font-body leading-relaxed">DAS tips, sensory guides, and honest advice for neurodivergent families.</p>
                        <span class="mt-4 inline-flex items-center gap-1 text-white text-sm font-semibold font-body">Read more →</span>
                    </div>
                </div>
            </div>

            {{-- Flip 2 --}}
            <div class="flip-card">
                <div class="flip-inner">
                    <div class="flip-front">
                        <div class="w-20 h-20 rounded-2xl mb-5 flex items-center justify-center" style="background: linear-gradient(135deg, #ede4f7, #ddd0f0);">
                            <svg class="w-10 h-10 text-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                        </div>
                        <h3 class="font-heading text-xl font-bold text-navy">Park Strategy</h3>
                    </div>
                    <div class="flip-back" style="background: linear-gradient(135deg, #7b5eb5, #5b3e9e);">
                        <svg class="w-8 h-8 text-white/30 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                        <h3 class="font-heading text-xl font-bold text-white mb-2">Park Strategy</h3>
                        <p class="text-white/80 text-sm font-body leading-relaxed">Rope drop plans and weekly-tested tips from locals.</p>
                        <span class="mt-4 inline-flex items-center gap-1 text-white text-sm font-semibold font-body">Read more →</span>
                    </div>
                </div>
            </div>

            {{-- Flip 3 --}}
            <div class="flip-card">
                <div class="flip-inner">
                    <div class="flip-front">
                        <div class="w-20 h-20 rounded-2xl mb-5 flex items-center justify-center" style="background: linear-gradient(135deg, #fdf3dc, #f8e8c4);">
                            <svg class="w-10 h-10 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                        </div>
                        <h3 class="font-heading text-xl font-bold text-navy">Food & Reviews</h3>
                    </div>
                    <div class="flip-back" style="background: linear-gradient(135deg, #f0c75e, #d4a843);">
                        <svg class="w-8 h-8 text-navy/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                        <h3 class="font-heading text-xl font-bold text-navy mb-2">Food & Reviews</h3>
                        <p class="text-navy/70 text-sm font-body leading-relaxed">Honest reviews — including diabetic-friendly options.</p>
                        <span class="mt-4 inline-flex items-center gap-1 text-navy text-sm font-semibold font-body">Read more →</span>
                    </div>
                </div>
            </div>

            {{-- Flip 4 --}}
            <div class="flip-card">
                <div class="flip-inner">
                    <div class="flip-front">
                        <div class="w-20 h-20 rounded-2xl mb-5 flex items-center justify-center" style="background: linear-gradient(135deg, #ddf0f5, #c8e6ef);">
                            <svg class="w-10 h-10" style="color: #4a90a4;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                        </div>
                        <h3 class="font-heading text-xl font-bold text-navy">Family Stories</h3>
                    </div>
                    <div class="flip-back" style="background: linear-gradient(135deg, #5ba4ad, #4a90a4);">
                        <svg class="w-8 h-8 text-white/30 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                        <h3 class="font-heading text-xl font-bold text-white mb-2">Family Stories</h3>
                        <p class="text-white/80 text-sm font-body leading-relaxed">The magical, messy, and in between.</p>
                        <span class="mt-4 inline-flex items-center gap-1 text-white text-sm font-semibold font-body">Read more →</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="option-divider"></div>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION J: Expanding Number Reveal --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option J — Expanding Number Reveal (Hover to Expand)</div>

<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Disney, Your Way</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="reveal-card text-center" style="--accent: #d4627a; --accent-light: #e88d9a;">
                <div class="reveal-number">01</div>
                <h3 class="font-heading text-lg font-bold text-navy mt-3">Accessibility</h3>
                <p class="reveal-desc text-navy/50 text-sm font-body leading-relaxed">DAS tips, sensory guides, and honest advice for neurodivergent families navigating the parks.</p>
            </div>

            <div class="reveal-card text-center" style="--accent: #5b3e9e; --accent-light: #7b5eb5;">
                <div class="reveal-number">02</div>
                <h3 class="font-heading text-lg font-bold text-navy mt-3">Park Strategy</h3>
                <p class="reveal-desc text-navy/50 text-sm font-body leading-relaxed">Ride tips, rope drop plans, and weekly-tested strategies from locals who know every shortcut.</p>
            </div>

            <div class="reveal-card text-center" style="--accent: #d4a843; --accent-light: #f0c75e;">
                <div class="reveal-number">03</div>
                <h3 class="font-heading text-lg font-bold text-navy mt-3">Food & Reviews</h3>
                <p class="reveal-desc text-navy/50 text-sm font-body leading-relaxed">Honest reviews of restaurants, snacks, and resorts — including diabetic-friendly options.</p>
            </div>

            <div class="reveal-card text-center" style="--accent: #4a90a4; --accent-light: #5ba4ad;">
                <div class="reveal-number">04</div>
                <h3 class="font-heading text-lg font-bold text-navy mt-3">Family Stories</h3>
                <p class="reveal-desc text-navy/50 text-sm font-body leading-relaxed">Real moments from our weekly trips — the magical, the messy, and everything in between.</p>
            </div>
        </div>
    </div>
</section>

<div style="height: 80px; background: #fef9ef;"></div>
@endsection
