@extends('layouts.app')

@section('title', 'Content Pillars — Design Options')

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
        margin: 0;
    }

    /* Option A hover effects */
    .pillar-a {
        transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        border-top: 3px solid transparent;
        position: relative;
    }
    .pillar-a:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(26,16,64,0.1);
    }
    .pillar-a:hover .pillar-a-icon {
        transform: translateY(-4px);
    }
    .pillar-a-icon {
        transition: transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .pillar-a-pink:hover { border-top-color: #e88d9a; }
    .pillar-a-purple:hover { border-top-color: #5b3e9e; }
    .pillar-a-gold:hover { border-top-color: #d4a843; }
    .pillar-a-teal:hover { border-top-color: #4a90a4; }

    /* Option B photo cards */
    .pillar-b {
        position: relative;
        border-radius: 1rem;
        overflow: hidden;
        min-height: 280px;
        transition: transform 0.4s ease, box-shadow 0.4s ease;
    }
    .pillar-b:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(26,16,64,0.2);
    }
    .pillar-b img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    .pillar-b:hover img {
        transform: scale(1.05);
    }
    .pillar-b-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(26,16,64,0.9) 0%, rgba(26,16,64,0.5) 50%, rgba(26,16,64,0.2) 100%);
        transition: background 0.4s ease;
    }
    .pillar-b:hover .pillar-b-overlay {
        background: linear-gradient(to top, rgba(26,16,64,0.95) 0%, rgba(45,27,105,0.6) 50%, rgba(26,16,64,0.15) 100%);
    }

    /* Option C strips */
    .pillar-c {
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
    }
    .pillar-c:hover {
        background: white;
        box-shadow: 0 4px 20px rgba(26,16,64,0.06);
        transform: translateX(4px);
    }
    .pillar-c-pink:hover { border-left-color: #e88d9a; }
    .pillar-c-purple:hover { border-left-color: #5b3e9e; }
    .pillar-c-gold:hover { border-left-color: #d4a843; }
    .pillar-c-teal:hover { border-left-color: #4a90a4; }

    /* Option E tabs */
    .pillar-tab {
        transition: all 0.25s ease;
        cursor: pointer;
        border-bottom: 3px solid transparent;
    }
    .pillar-tab:hover {
        color: #5b3e9e;
    }
    .pillar-tab.active {
        color: #1a1040;
        border-bottom-color: #d4a843;
    }
</style>

{{-- Page Header --}}
<section style="background: linear-gradient(135deg, #1a1040, #2d1b69); padding: 3rem 1rem; text-align: center;">
    <h1 class="font-heading text-3xl font-bold text-white mb-2">"What We Cover" Section — Design Options</h1>
    <p class="font-body text-white/50 text-sm">Compare all 5 options. Same content, different presentations.</p>
</section>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION A: Hovering Icon Cards --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option A — Hovering Icon Cards (Polished Current)</div>

<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Disney, Your Way</h2>
            <p class="text-navy/50 text-base mt-3 max-w-xl mx-auto font-body leading-relaxed">Real advice from a family that does Disney every week — with a focus on making the parks work for everyone.</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {{-- Accessibility --}}
            <div class="pillar-a pillar-a-pink text-center p-7 rounded-2xl bg-white border border-navy/5">
                <div class="pillar-a-icon w-16 h-16 mx-auto mb-5 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #fce4e8, #f8d0d6);">
                    <svg class="w-8 h-8" style="color: #d4627a;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                </div>
                <h3 class="font-heading text-lg font-bold text-navy mb-2">Accessibility</h3>
                <p class="text-navy/50 text-sm font-body leading-relaxed mb-4">DAS tips, sensory guides, and honest advice for neurodivergent families.</p>
                <span class="text-sm font-semibold font-body inline-flex items-center gap-1" style="color: #d4627a;">Explore <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
            </div>

            {{-- Park Strategy --}}
            <div class="pillar-a pillar-a-purple text-center p-7 rounded-2xl bg-white border border-navy/5">
                <div class="pillar-a-icon w-16 h-16 mx-auto mb-5 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede4f7, #ddd0f0);">
                    <svg class="w-8 h-8 text-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                </div>
                <h3 class="font-heading text-lg font-bold text-navy mb-2">Park Strategy</h3>
                <p class="text-navy/50 text-sm font-body leading-relaxed mb-4">Ride tips, rope drop plans, and weekly-tested strategies from locals.</p>
                <span class="text-sm font-semibold font-body inline-flex items-center gap-1 text-purple">Explore <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
            </div>

            {{-- Food & Reviews --}}
            <div class="pillar-a pillar-a-gold text-center p-7 rounded-2xl bg-white border border-navy/5">
                <div class="pillar-a-icon w-16 h-16 mx-auto mb-5 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #fdf3dc, #f8e8c4);">
                    <svg class="w-8 h-8 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                </div>
                <h3 class="font-heading text-lg font-bold text-navy mb-2">Food & Reviews</h3>
                <p class="text-navy/50 text-sm font-body leading-relaxed mb-4">Honest reviews of restaurants, snacks, and resorts — diabetic-friendly included.</p>
                <span class="text-sm font-semibold font-body inline-flex items-center gap-1 text-gold">Explore <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
            </div>

            {{-- Family Stories --}}
            <div class="pillar-a pillar-a-teal text-center p-7 rounded-2xl bg-white border border-navy/5">
                <div class="pillar-a-icon w-16 h-16 mx-auto mb-5 rounded-2xl flex items-center justify-center" style="background: linear-gradient(135deg, #ddf0f5, #c8e6ef);">
                    <svg class="w-8 h-8" style="color: #4a90a4;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                </div>
                <h3 class="font-heading text-lg font-bold text-navy mb-2">Family Stories</h3>
                <p class="text-navy/50 text-sm font-body leading-relaxed mb-4">Real moments from our weekly trips — the magical, messy, and in between.</p>
                <span class="text-sm font-semibold font-body inline-flex items-center gap-1" style="color: #4a90a4;">Explore <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></span>
            </div>
        </div>
    </div>
</section>

<div class="option-divider"></div>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION B: Overlapping Photo Cards --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option B — Photo Cards with Gradient Overlay</div>

<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Disney, Your Way</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="pillar-b">
                <img src="https://images.unsplash.com/photo-1597466599360-3b9775841aec?w=400&h=500&fit=crop" alt="">
                <div class="pillar-b-overlay"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 z-10">
                    <div class="w-10 h-10 rounded-xl mb-3 flex items-center justify-center" style="background: rgba(212,168,67,0.2); border: 1px solid rgba(212,168,67,0.3);">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-white mb-1">Accessibility</h3>
                    <p class="text-white/50 text-xs font-body leading-relaxed">DAS tips, sensory guides, and honest advice for neurodivergent families.</p>
                </div>
            </div>
            <div class="pillar-b">
                <img src="https://images.unsplash.com/photo-1568515387631-8b650bbcdb90?w=400&h=500&fit=crop" alt="">
                <div class="pillar-b-overlay"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 z-10">
                    <div class="w-10 h-10 rounded-xl mb-3 flex items-center justify-center" style="background: rgba(212,168,67,0.2); border: 1px solid rgba(212,168,67,0.3);">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-white mb-1">Park Strategy</h3>
                    <p class="text-white/50 text-xs font-body leading-relaxed">Rope drop plans and weekly-tested strategies from locals.</p>
                </div>
            </div>
            <div class="pillar-b">
                <img src="https://images.unsplash.com/photo-1551782450-a2132b4ba21d?w=400&h=500&fit=crop" alt="">
                <div class="pillar-b-overlay"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 z-10">
                    <div class="w-10 h-10 rounded-xl mb-3 flex items-center justify-center" style="background: rgba(212,168,67,0.2); border: 1px solid rgba(212,168,67,0.3);">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-white mb-1">Food & Reviews</h3>
                    <p class="text-white/50 text-xs font-body leading-relaxed">Honest reviews of restaurants, snacks, and resorts.</p>
                </div>
            </div>
            <div class="pillar-b">
                <img src="https://images.unsplash.com/photo-1511895426328-dc8714191300?w=400&h=500&fit=crop" alt="">
                <div class="pillar-b-overlay"></div>
                <div class="absolute bottom-0 left-0 right-0 p-6 z-10">
                    <div class="w-10 h-10 rounded-xl mb-3 flex items-center justify-center" style="background: rgba(212,168,67,0.2); border: 1px solid rgba(212,168,67,0.3);">
                        <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                    </div>
                    <h3 class="font-heading text-xl font-bold text-white mb-1">Family Stories</h3>
                    <p class="text-white/50 text-xs font-body leading-relaxed">Real moments — the magical, messy, and in between.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="option-divider"></div>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION C: Horizontal Feature Strips --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option C — Horizontal Feature Strips</div>

<section class="py-16 md:py-24 bg-cream">
    <div class="max-w-3xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-12">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Disney, Your Way</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
            <div class="pillar-c pillar-c-pink flex items-start gap-5 p-6 rounded-xl bg-cream-dark/50">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fce4e8, #f8d0d6);">
                    <svg class="w-6 h-6" style="color: #d4627a;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading text-lg font-bold text-navy mb-1">Accessibility</h3>
                    <p class="text-navy/50 text-sm font-body leading-relaxed">DAS tips, sensory guides, and honest advice for neurodivergent families navigating the parks.</p>
                </div>
            </div>

            <div class="pillar-c pillar-c-purple flex items-start gap-5 p-6 rounded-xl bg-cream-dark/50">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede4f7, #ddd0f0);">
                    <svg class="w-6 h-6 text-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading text-lg font-bold text-navy mb-1">Park Strategy</h3>
                    <p class="text-navy/50 text-sm font-body leading-relaxed">Ride tips, rope drop plans, and weekly-tested strategies from locals who know every shortcut.</p>
                </div>
            </div>

            <div class="pillar-c pillar-c-gold flex items-start gap-5 p-6 rounded-xl bg-cream-dark/50">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fdf3dc, #f8e8c4);">
                    <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading text-lg font-bold text-navy mb-1">Food & Reviews</h3>
                    <p class="text-navy/50 text-sm font-body leading-relaxed">Honest reviews of restaurants, snacks, and resorts — including diabetic-friendly options.</p>
                </div>
            </div>

            <div class="pillar-c pillar-c-teal flex items-start gap-5 p-6 rounded-xl bg-cream-dark/50">
                <div class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ddf0f5, #c8e6ef);">
                    <svg class="w-6 h-6" style="color: #4a90a4;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading text-lg font-bold text-navy mb-1">Family Stories</h3>
                    <p class="text-navy/50 text-sm font-body leading-relaxed">Real moments from our weekly trips — the magical, the messy, and everything in between.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="option-divider"></div>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION D: Big Numbers / Minimal --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option D — Bold Minimal (Magazine Style)</div>

<section class="py-16 md:py-24" style="background: linear-gradient(135deg, #1a1040, #2d1b69);">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-14">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-white mt-2">Disney, Your Way</h2>
        </div>
        <div class="grid sm:grid-cols-2 gap-x-12 gap-y-10">
            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center border" style="border-color: rgba(212,99,122,0.3); background: rgba(212,99,122,0.1);">
                    <span style="font-size: 1.5rem;">🧩</span>
                </div>
                <div>
                    <h3 class="font-heading text-xl font-bold text-white mb-1">Accessibility</h3>
                    <p class="font-body text-sm leading-relaxed" style="color: rgba(254,249,239,0.45);">DAS tips, sensory guides, and real advice for neurodivergent families navigating the parks.</p>
                </div>
            </div>

            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center border" style="border-color: rgba(91,62,158,0.4); background: rgba(91,62,158,0.15);">
                    <span style="font-size: 1.5rem;">🗺️</span>
                </div>
                <div>
                    <h3 class="font-heading text-xl font-bold text-white mb-1">Park Strategy</h3>
                    <p class="font-body text-sm leading-relaxed" style="color: rgba(254,249,239,0.45);">Ride tips, rope drop plans, and weekly-tested strategies from locals who know every shortcut.</p>
                </div>
            </div>

            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center border" style="border-color: rgba(212,168,67,0.3); background: rgba(212,168,67,0.1);">
                    <span style="font-size: 1.5rem;">⭐</span>
                </div>
                <div>
                    <h3 class="font-heading text-xl font-bold text-white mb-1">Food & Reviews</h3>
                    <p class="font-body text-sm leading-relaxed" style="color: rgba(254,249,239,0.45);">Honest reviews of restaurants, snacks, and resorts — including diabetic-friendly options.</p>
                </div>
            </div>

            <div class="flex items-start gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl flex items-center justify-center border" style="border-color: rgba(74,144,164,0.3); background: rgba(74,144,164,0.1);">
                    <span style="font-size: 1.5rem;">💜</span>
                </div>
                <div>
                    <h3 class="font-heading text-xl font-bold text-white mb-1">Family Stories</h3>
                    <p class="font-body text-sm leading-relaxed" style="color: rgba(254,249,239,0.45);">Real moments from our weekly trips — the magical, the messy, and everything in between.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="option-divider"></div>

{{-- ═══════════════════════════════════════════ --}}
{{-- OPTION E: Interactive Tabbed Preview --}}
{{-- ═══════════════════════════════════════════ --}}
<div class="option-label">Option E — Tabbed Content Preview</div>

<section class="py-16 md:py-24 bg-cream" x-data="{ active: 'accessibility' }">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10">
            <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">What We Cover</span>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Disney, Your Way</h2>
        </div>

        {{-- Tab Pills --}}
        <div class="flex flex-wrap justify-center gap-2 mb-10">
            <button @click="active = 'accessibility'" :class="active === 'accessibility' ? 'bg-navy text-white' : 'bg-white text-navy/60 hover:text-navy border border-navy/10'" class="font-body text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                Accessibility
            </button>
            <button @click="active = 'strategy'" :class="active === 'strategy' ? 'bg-navy text-white' : 'bg-white text-navy/60 hover:text-navy border border-navy/10'" class="font-body text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                Park Strategy
            </button>
            <button @click="active = 'food'" :class="active === 'food' ? 'bg-navy text-white' : 'bg-white text-navy/60 hover:text-navy border border-navy/10'" class="font-body text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                Food & Reviews
            </button>
            <button @click="active = 'stories'" :class="active === 'stories' ? 'bg-navy text-white' : 'bg-white text-navy/60 hover:text-navy border border-navy/10'" class="font-body text-sm font-semibold px-5 py-2.5 rounded-full transition-all duration-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                Family Stories
            </button>
        </div>

        {{-- Tab Panels --}}
        <div class="bg-white rounded-2xl border border-navy/5 shadow-sm overflow-hidden">
            {{-- Accessibility Panel --}}
            <div x-show="active === 'accessibility'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-8 md:p-10">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fce4e8, #f8d0d6);">
                                <svg class="w-5 h-5" style="color: #d4627a;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>
                            </div>
                            <h3 class="font-heading text-2xl font-bold text-navy">Accessibility</h3>
                        </div>
                        <p class="text-navy/55 font-body leading-relaxed mb-6">DAS tips, sensory guides, and honest advice for neurodivergent families navigating Disney parks. We share what works for our daughter Viola and what we've learned from years of weekly visits.</p>
                        <a href="/blog?category=accessibility" class="inline-flex items-center gap-1.5 text-purple hover:text-navy font-semibold text-sm font-body transition-colors">
                            Browse accessibility posts
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="md:w-64 flex-shrink-0 space-y-3">
                        <div class="text-xs font-semibold text-navy/40 uppercase tracking-wider font-body mb-2">Popular in this topic</div>
                        <div class="p-3 rounded-lg bg-cream/80 hover:bg-cream transition-colors">
                            <p class="text-sm font-semibold text-navy font-body">The Complete DAS Guide for 2026</p>
                            <p class="text-xs text-navy/40 font-body mt-0.5">Coming soon</p>
                        </div>
                        <div class="p-3 rounded-lg bg-cream/80 hover:bg-cream transition-colors">
                            <p class="text-sm font-semibold text-navy font-body">Sensory-Friendly Spots at Each Park</p>
                            <p class="text-xs text-navy/40 font-body mt-0.5">Coming soon</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Strategy Panel --}}
            <div x-show="active === 'strategy'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-8 md:p-10" style="display: none;">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ede4f7, #ddd0f0);">
                                <svg class="w-5 h-5 text-purple" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z"/></svg>
                            </div>
                            <h3 class="font-heading text-2xl font-bold text-navy">Park Strategy</h3>
                        </div>
                        <p class="text-navy/55 font-body leading-relaxed mb-6">Ride tips, rope drop plans, and weekly-tested strategies from locals who know every shortcut. We're at Disney 52+ weeks a year — these aren't theory, they're field-tested.</p>
                        <a href="/blog?category=park-tips" class="inline-flex items-center gap-1.5 text-purple hover:text-navy font-semibold text-sm font-body transition-colors">
                            Browse strategy posts
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="md:w-64 flex-shrink-0 space-y-3">
                        <div class="text-xs font-semibold text-navy/40 uppercase tracking-wider font-body mb-2">Popular in this topic</div>
                        <div class="p-3 rounded-lg bg-cream/80 hover:bg-cream transition-colors">
                            <p class="text-sm font-semibold text-navy font-body">Rope Drop: Is It Still Worth It?</p>
                            <p class="text-xs text-navy/40 font-body mt-0.5">Coming soon</p>
                        </div>
                        <div class="p-3 rounded-lg bg-cream/80 hover:bg-cream transition-colors">
                            <p class="text-sm font-semibold text-navy font-body">Our Favorite Low-Wait Rides</p>
                            <p class="text-xs text-navy/40 font-body mt-0.5">Coming soon</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Food Panel --}}
            <div x-show="active === 'food'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-8 md:p-10" style="display: none;">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #fdf3dc, #f8e8c4);">
                                <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg>
                            </div>
                            <h3 class="font-heading text-2xl font-bold text-navy">Food & Reviews</h3>
                        </div>
                        <p class="text-navy/55 font-body leading-relaxed mb-6">Honest reviews of Disney restaurants, snacks, and resorts — including diabetic-friendly options. We eat our way through the parks so you know what's actually worth your money.</p>
                        <a href="/blog?category=food-reviews" class="inline-flex items-center gap-1.5 text-purple hover:text-navy font-semibold text-sm font-body transition-colors">
                            Browse food posts
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="md:w-64 flex-shrink-0 space-y-3">
                        <div class="text-xs font-semibold text-navy/40 uppercase tracking-wider font-body mb-2">Popular in this topic</div>
                        <div class="p-3 rounded-lg bg-cream/80 hover:bg-cream transition-colors">
                            <p class="text-sm font-semibold text-navy font-body">Best Snacks Under $10 at Each Park</p>
                            <p class="text-xs text-navy/40 font-body mt-0.5">Coming soon</p>
                        </div>
                        <div class="p-3 rounded-lg bg-cream/80 hover:bg-cream transition-colors">
                            <p class="text-sm font-semibold text-navy font-body">Diabetic-Friendly Disney Dining</p>
                            <p class="text-xs text-navy/40 font-body mt-0.5">Coming soon</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stories Panel --}}
            <div x-show="active === 'stories'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="p-8 md:p-10" style="display: none;">
                <div class="flex flex-col md:flex-row gap-8">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: linear-gradient(135deg, #ddf0f5, #c8e6ef);">
                                <svg class="w-5 h-5" style="color: #4a90a4;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/></svg>
                            </div>
                            <h3 class="font-heading text-2xl font-bold text-navy">Family Stories</h3>
                        </div>
                        <p class="text-navy/55 font-body leading-relaxed mb-6">Real moments from our weekly Disney trips — the magical, the messy, and everything in between. No filter, just our family figuring it out one park day at a time.</p>
                        <a href="/stories" class="inline-flex items-center gap-1.5 text-purple hover:text-navy font-semibold text-sm font-body transition-colors">
                            Read our stories
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                    <div class="md:w-64 flex-shrink-0 space-y-3">
                        <div class="text-xs font-semibold text-navy/40 uppercase tracking-wider font-body mb-2">Popular in this topic</div>
                        <div class="p-3 rounded-lg bg-cream/80 hover:bg-cream transition-colors">
                            <p class="text-sm font-semibold text-navy font-body">Viola's First Time on Pirates</p>
                            <p class="text-xs text-navy/40 font-body mt-0.5">Coming soon</p>
                        </div>
                        <div class="p-3 rounded-lg bg-cream/80 hover:bg-cream transition-colors">
                            <p class="text-sm font-semibold text-navy font-body">Why We Go Every Week</p>
                            <p class="text-xs text-navy/40 font-body mt-0.5">Coming soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div style="height: 80px; background: #fef9ef;"></div>
@endsection
