@extends('layouts.app')

@section('title', ($category ? \App\Models\Post::CATEGORIES[$category] ?? ucwords(str_replace('-', ' ', $category)) : 'Blog') . ' — Mouse28')

@section('content')
    <style>
        /* Pagination */
        .pagination nav > div:first-child { display: none; }
        .pagination span, .pagination a {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 2.25rem; height: 2.25rem; padding: 0 0.75rem;
            border-radius: 9999px; font-size: 0.875rem; font-weight: 600;
            transition: all 0.2s ease;
        }
        .pagination span[aria-current="page"] span {
            background: #d4a843; color: white; box-shadow: 0 2px 8px rgba(212,168,67,0.3);
        }
        .pagination a { color: rgba(26,16,64,0.5); border: 1px solid rgba(26,16,64,0.1); background: white; }
        .pagination a:hover { color: #1a1040; border-color: #d4a843; }
        .pagination span[aria-disabled="true"] span { color: rgba(26,16,64,0.2); background: transparent; border: 1px solid rgba(26,16,64,0.05); }

        /* Animated border */
        @property --border-angle {
            syntax: '<angle>';
            initial-value: 0deg;
            inherits: false;
        }
        @keyframes borderRotate {
            0% { --border-angle: 0deg; }
            100% { --border-angle: 360deg; }
        }
        .featured-wrapper {
            position: relative;
            z-index: 1;
            overflow: visible;
        }
        .featured-wrapper::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: 1.625rem;
            background: conic-gradient(from var(--border-angle), #d4a843, #7b5eb5, #4a90a4, #d4a843);
            animation: borderRotate 6s linear infinite;
            z-index: 0;
            opacity: 0.5;
        }
        .featured-card-border {
            position: relative;
            z-index: 1;
            background: linear-gradient(135deg, #1a1040, #2d1b69);
            border-radius: 1.5rem;
            color: white;
            overflow: hidden;
        }

        /* Ribbon: exact nxworld CodePen pattern */
        .ribbon {
            width: 150px;
            height: 150px;
            overflow: hidden;
            position: absolute;
            z-index: 30;
            pointer-events: none;
        }
        .ribbon::before,
        .ribbon::after {
            position: absolute;
            z-index: -1;
            content: '';
            display: block;
            border: 5px solid #7a5e1e;
        }
        .ribbon span {
            position: absolute;
            display: block;
            width: 225px;
            padding: 15px 0;
            background-color: #d4a843;
            box-shadow: 0 5px 10px rgba(0,0,0,.1);
            color: #1a1040;
            font: 700 18px/1 'Poppins', sans-serif;
            text-shadow: 0 1px 1px rgba(0,0,0,.2);
            text-transform: uppercase;
            text-align: center;
        }
        .ribbon-top-left {
            top: -10px;
            left: -10px;
        }
        .ribbon-top-left::before,
        .ribbon-top-left::after {
            border-top-color: transparent;
            border-left-color: transparent;
        }
        .ribbon-top-left::before {
            top: 0;
            right: 0;
        }
        .ribbon-top-left::after {
            bottom: 0;
            left: 0;
        }
        .ribbon-top-left span {
            right: -25px;
            top: 30px;
            transform: rotate(-45deg);
        }

        /* Grid post cards */
        .post-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .post-card .accent-bar {
            transition: transform 0.3s ease;
            transform: scaleX(0);
            transform-origin: left;
        }
        .post-card:hover .accent-bar { transform: scaleX(1); }
        .post-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(26,16,64,0.08), 0 8px 16px rgba(26,16,64,0.04);
        }

        /* Search bar glow */
        .search-glow:focus {
            box-shadow: 0 0 0 3px rgba(212,168,67,0.15), 0 4px 12px rgba(212,168,67,0.1);
        }

        /* Sparkle animation */
        @keyframes sparkle-float {
            0%, 100% { opacity: 0.15; transform: translateY(0) rotate(0deg); }
            50% { opacity: 0.4; transform: translateY(-4px) rotate(15deg); }
        }
    </style>

    @php
        $categoryColors = [
            'disney-tips' => '#d4a843',
            'park-accessibility' => '#7b5eb5',
            'episode-recap' => '#22c55e',
            'family-life' => '#3b82f6',
            'autism-awareness' => '#ec4899',
            'disney-news' => '#f97316',
            'food-reviews' => '#f59e0b',
            'resort-reviews' => '#14b8a6',
            'disney-plus' => '#6366f1',
            'merchandise' => '#f43f5e',
            'general' => '#4a90a4',
        ];
        $categoryGradients = [
            'disney-tips' => ['from' => '#d4a843', 'to' => '#f0c75e', 'icon' => '🏰'],
            'park-accessibility' => ['from' => '#7b5eb5', 'to' => '#a78bfa', 'icon' => '💜'],
            'episode-recap' => ['from' => '#059669', 'to' => '#34d399', 'icon' => '🎙️'],
            'family-life' => ['from' => '#2563eb', 'to' => '#60a5fa', 'icon' => '👨‍👩‍👧'],
            'autism-awareness' => ['from' => '#db2777', 'to' => '#f472b6', 'icon' => '🧩'],
            'disney-news' => ['from' => '#ea580c', 'to' => '#fb923c', 'icon' => '📰'],
            'food-reviews' => ['from' => '#d97706', 'to' => '#fbbf24', 'icon' => '🍽️'],
            'resort-reviews' => ['from' => '#0d9488', 'to' => '#5eead4', 'icon' => '🏨'],
            'disney-plus' => ['from' => '#4f46e5', 'to' => '#818cf8', 'icon' => '📺'],
            'merchandise' => ['from' => '#e11d48', 'to' => '#fb7185', 'icon' => '🛍️'],
            'general' => ['from' => '#4a90a4', 'to' => '#7bc4d4', 'icon' => '✨'],
        ];
        $defaultGrad = ['from' => '#5b3e9e', 'to' => '#7c5cbf', 'icon' => '✨'];
    @endphp

    {{-- Hero + Search + Filters (unified) --}}
    <section class="relative overflow-hidden" style="background: linear-gradient(180deg, #1a1040 0%, #2d1b69 100%); padding-bottom: 0;">
        <div class="absolute top-[15%] right-[10%] text-gold/20 text-sm" style="animation: sparkle-float 4s ease-in-out infinite;">&#10022;</div>
        <div class="absolute top-[35%] left-[7%] text-gold/10 text-xs" style="animation: sparkle-float 5s ease-in-out 1.5s infinite;">&#10022;</div>

        <div class="max-w-4xl mx-auto px-4 text-center relative pt-14">
            <span class="text-gold text-xs font-semibold tracking-widest uppercase">Stories & Tips</span>
            <h1 class="font-heading text-3xl md:text-4xl font-bold text-white mt-2">Blog <span class="inline-block text-gold/40 text-lg">✦</span></h1>
            <p class="text-white/40 mt-3">Disney tips, park guides, and stories from our family to yours.</p>

            @if($hasAnyPosts)
                {{-- Post stats --}}
                <div class="flex items-center justify-center gap-6 mt-6">
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-white font-heading">{{ $posts->total() }}</span>
                        <span class="text-white/30 text-xs uppercase tracking-wider">{{ Str::plural('Post', $posts->total()) }}</span>
                    </div>
                    <div class="w-px h-8 bg-white/10"></div>
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-gold font-heading">{{ count($usedCategories) }}</span>
                        <span class="text-white/30 text-xs uppercase tracking-wider">{{ Str::plural('Category', count($usedCategories)) }}</span>
                    </div>
                </div>

                {{-- Search --}}
                <form action="/blog" method="GET" class="relative max-w-xl mx-auto mt-8">
                    @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
                    <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4" style="color: rgba(254,249,239,0.3);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts..."
                        class="search-glow"
                        style="width: 100%; padding: 0.85rem 1.25rem 0.85rem 2.75rem; border-radius: 1rem; border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.05); color: #fef9ef; font-size: 0.875rem; font-family: 'Poppins', sans-serif; outline: none; transition: all 0.3s;"
                        onfocus="this.style.borderColor='rgba(212,168,67,0.4)';this.style.background='rgba(254,249,239,0.08)'"
                        onblur="this.style.borderColor='rgba(254,249,239,0.1)';this.style.background='rgba(254,249,239,0.05)'"
                    >
                    @if(request('q'))
                        <a href="/blog{{ $category ? '?category='.$category : '' }}" class="absolute right-4 top-1/2 -translate-y-1/2 text-white/30 hover:text-gold transition-colors" title="Clear search">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </a>
                    @endif
                </form>

                {{-- Category filters --}}
                <div class="flex flex-wrap items-center justify-center gap-2 mt-5" x-data="{ active: '{{ $category ?? 'all' }}' }">
                    <a href="/blog" @click="active = 'all'" :class="active === 'all' ? 'bg-white/15 text-white border-white/20' : 'bg-white/5 text-white/50 border-white/10 hover:text-white hover:border-white/20'" class="text-xs font-semibold uppercase tracking-wider px-4 py-2 rounded-full border transition-all duration-200">
                        All
                    </a>
                    @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                        @continue(!in_array($slug, $usedCategories))
                        @php $color = $categoryColors[$slug] ?? '#7b5eb5'; @endphp
                        <a href="/blog?category={{ $slug }}" @click="active = '{{ $slug }}'" :class="active === '{{ $slug }}' ? 'text-white' : 'bg-white/5 hover:border-current'" class="text-xs font-semibold uppercase tracking-wider px-4 py-2 rounded-full border border-white/10 transition-all duration-200" :style="active === '{{ $slug }}' ? 'background: {{ $color }}; border-color: {{ $color }}; color: white;' : 'color: {{ $color }};'">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                {{-- Sort + active search indicator --}}
                <div class="flex items-center justify-center gap-4 mt-5">
                    @if(request('q'))
                        <span class="text-white/30 text-xs">
                            {{ $posts->total() }} {{ Str::plural('result', $posts->total()) }} for "<span class="text-gold">{{ request('q') }}</span>"
                        </span>
                        <span class="text-white/10">|</span>
                    @endif
                    <div style="display: inline-flex; border-radius: 0.5rem; overflow: hidden; border: 1px solid rgba(254,249,239,0.1);">
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
                           style="padding: 0.4rem 1rem; font-size: 0.75rem; font-weight: 600; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s;
                           {{ ($sort ?? 'newest') === 'newest' ? 'background: rgba(212,168,67,0.15); color: #d4a843;' : 'background: transparent; color: rgba(254,249,239,0.4);' }}">
                            Newest
                        </a>
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}"
                           style="padding: 0.4rem 1rem; font-size: 0.75rem; font-weight: 600; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; border-left: 1px solid rgba(254,249,239,0.1);
                           {{ ($sort ?? 'newest') === 'oldest' ? 'background: rgba(212,168,67,0.15); color: #d4a843;' : 'background: transparent; color: rgba(254,249,239,0.4);' }}">
                            Oldest
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- Smooth transition to cream --}}
        <div class="mt-10" style="height: 48px; background: linear-gradient(180deg, transparent 0%, #fef9ef 100%);"></div>
    </section>

    {{-- Posts Section --}}
    <section class="py-16 bg-cream relative">
        <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(#1a1040 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative">
            @if($posts->count())
                @php $featured = $posts->first(); $rest = $posts->skip(1); @endphp

                {{-- Featured Post: Navy gradient + animated border + ribbon --}}
                @if($posts->currentPage() === 1 && !request('q'))
                    @php $fColor = $categoryColors[$featured->category] ?? '#5b3e9e'; @endphp
                    <div class="mb-8 featured-wrapper rounded-3xl" style="overflow: visible;">
                        {{-- Corner ribbon --}}
                        <div class="ribbon ribbon-top-left"><span>Featured</span></div>

                        <a href="/blog/{{ $featured->slug }}" class="featured-card-border group block transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                            <div class="grid md:grid-cols-5 min-h-[280px] relative">
                                {{-- Excerpt side --}}
                                <div class="md:col-span-2 p-8 md:p-10 pl-12 md:pl-16 pt-24 pb-8 flex flex-col justify-center relative">
                                    @if($featured->excerpt)
                                        <p class="text-white/70 text-sm md:text-base leading-relaxed relative z-10">
                                            {{ $featured->excerpt }}
                                        </p>
                                    @endif
                                </div>
                                {{-- Content side --}}
                                <div class="md:col-span-3 p-8 md:p-10 flex flex-col justify-center">
                                    <div class="flex items-center gap-3 mb-4">
                                        @if($featured->category)
                                            <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background: {{ $fColor }}30; color: {{ $fColor }};">{{ $featured->category_label }}</span>
                                        @endif
                                        <span class="text-white/30 text-xs">{{ $featured->reading_time }} min read</span>
                                    </div>
                                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-white group-hover:text-gold transition-colors leading-snug">{{ $featured->title }}</h2>
                                    <div class="flex items-center justify-between mt-6 pt-6 border-t border-white/10">
                                        <div class="flex items-center gap-4">
                                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gold/25 to-purple/15 flex items-center justify-center text-gold text-[10px] font-bold font-heading border border-gold/20">
                                                {{ collect(explode(' ', $featured->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                                            </div>
                                            <div>
                                                <p class="text-white text-sm font-semibold">{{ $featured->author_name }}</p>
                                                <p class="text-white/40 text-xs">{{ $featured->published_at->format('F j, Y') }}</p>
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
                @else
                    @php $rest = $posts; @endphp
                @endif

                {{-- Post Grid (2-column) --}}
                @if($rest->count())
                    <div class="grid sm:grid-cols-2 gap-6">
                        @foreach($rest as $post)
                            @php $pColor = $categoryColors[$post->category] ?? '#5b3e9e'; @endphp
                            <a href="/blog/{{ $post->slug }}" class="post-card group bg-white rounded-2xl p-7 shadow-sm border border-navy/5 relative">
                                {{-- Top accent bar on hover --}}
                                <div class="accent-bar absolute top-0 left-0 right-0 h-1" style="background: {{ $pColor }};"></div>
                                <div class="flex items-center gap-3 mb-3">
                                    @if($post->category)
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background: {{ $pColor }}15; color: {{ $pColor }};">{{ $post->category_label }}</span>
                                    @endif
                                    <span class="text-navy/25 text-xs">{{ $post->reading_time }} min read</span>
                                </div>
                                <h3 class="font-heading text-xl font-bold text-navy group-hover:text-purple transition-colors leading-snug line-clamp-2">{{ $post->title }}</h3>
                                @if($post->excerpt)
                                    <p class="text-navy/45 text-sm leading-relaxed mt-2 line-clamp-3">{{ $post->excerpt }}</p>
                                @endif
                                <div class="flex items-center justify-between mt-6 pt-4 border-t border-navy/5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-navy/60 text-xs font-medium">{{ $post->author_name }}</span>
                                        <span class="text-navy/20">·</span>
                                        <span class="text-navy/30 text-xs">{{ $post->published_at->format('M j, Y') }}</span>
                                    </div>
                                    <span class="text-xs font-semibold group-hover:gap-2 transition-all inline-flex items-center gap-1" style="color: {{ $pColor }};">
                                        Read
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Pagination / Load More --}}
                @if($posts->hasPages())
                    <div class="text-center mt-12">
                        @if($posts->hasMorePages())
                            <a href="{{ $posts->withQueryString()->nextPageUrl() }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-gold to-gold-light text-navy font-semibold text-sm px-8 py-3 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5">
                                Load More Stories
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                        @endif
                    </div>
                    <div class="mt-6 pagination flex justify-center">
                        {{ $posts->withQueryString()->links() }}
                    </div>
                @endif
            @else
                {{-- Empty State --}}
                <div class="relative overflow-hidden rounded-3xl" style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 50%, #3a2370 100%); padding: 5rem 3rem;">
                    <div style="position: absolute; top: -30%; right: -5%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(212, 168, 67, 0.06) 0%, transparent 60%); pointer-events: none;"></div>
                    <div style="position: absolute; bottom: -20%; left: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(91, 62, 158, 0.2) 0%, transparent 60%); pointer-events: none;"></div>

                    <div class="max-w-3xl mx-auto relative z-10">
                        <div class="grid md:grid-cols-2 gap-12 items-center">
                            <div class="text-center md:text-left">
                                <div style="display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(212, 168, 67, 0.3); border-radius: 9999px; padding: 0.35rem 1rem; margin-bottom: 1.5rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #d4a843;"></span>
                                    <span style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: #d4a843; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 600;">
                                        @if(request('q')) No Results @elseif($category) No Posts Yet @else Coming Soon @endif
                                    </span>
                                </div>
                                <h2 class="font-heading font-bold text-cream" style="font-size: clamp(1.75rem, 3.5vw, 2.5rem); line-height: 1.15; margin-bottom: 1rem;">
                                    @if(request('q'))
                                        No posts match "{{ request('q') }}"
                                    @elseif($category)
                                        Nothing in {{ \App\Models\Post::CATEGORIES[$category] ?? 'this category' }} yet
                                    @else
                                        We're putting pen to paper
                                    @endif
                                </h2>
                                <p style="color: rgba(254, 249, 239, 0.5); font-size: 0.95rem; line-height: 1.8; margin-bottom: 2rem;">
                                    @if(request('q'))
                                        Try a different search term or browse all posts instead.
                                    @elseif($category)
                                        We haven't published in this category yet, but it's on the list. Check out everything else in the meantime.
                                    @else
                                        Park tips, accessibility insights, food reviews, and real family stories from Disney. Our first posts are in the works.
                                    @endif
                                </p>
                                @if(request('q') || $category)
                                    <a href="/blog" class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-lg transition-all hover:-translate-y-0.5" style="background: rgba(212, 168, 67, 0.15); color: #d4a843; border: 1px solid rgba(212, 168, 67, 0.25);">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                        View all posts
                                    </a>
                                @else
                                    <a href="/contact" class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-lg transition-all hover:-translate-y-0.5" style="background: rgba(254, 249, 239, 0.08); color: rgba(254, 249, 239, 0.7); border: 1px solid rgba(254, 249, 239, 0.1);">
                                        Get in touch
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                            </div>

                            <div class="hidden md:flex flex-col gap-4">
                                @for($i = 0; $i < 3; $i++)
                                <div style="background: rgba(26, 16, 64, {{ 0.6 - $i * 0.15 }}); border: 1px solid rgba(212, 168, 67, {{ 0.12 - $i * 0.03 }}); border-radius: 1rem; padding: 1.5rem; {{ $i === 2 ? 'opacity: 0.6;' : '' }}">
                                    <div style="display: flex; gap: 1rem;">
                                        <div style="width: 72px; height: 72px; border-radius: 0.75rem; background: linear-gradient(135deg, rgba(212, 168, 67, {{ 0.2 - $i * 0.05 }}), rgba(91, 62, 158, {{ 0.3 - $i * 0.05 }})); flex-shrink: 0;"></div>
                                        <div style="flex: 1;">
                                            <div style="height: 8px; width: {{ 40 - $i * 5 }}%; background: rgba(212, 168, 67, {{ 0.25 - $i * 0.07 }}); border-radius: 99px; margin-bottom: 8px;"></div>
                                            <div style="height: 11px; width: {{ 95 - $i * 10 }}%; background: rgba(254, 249, 239, {{ 0.15 - $i * 0.04 }}); border-radius: 99px; margin-bottom: 5px;"></div>
                                            <div style="height: 11px; width: {{ 70 - $i * 10 }}%; background: rgba(254, 249, 239, {{ 0.1 - $i * 0.03 }}); border-radius: 99px;"></div>
                                        </div>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- Newsletter CTA --}}
    @if($posts->count())
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 100%);">
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold/20 to-transparent"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gold/5 rounded-full blur-3xl"></div>
        <div class="max-w-2xl mx-auto px-4 sm:px-6 py-16 text-center relative">
            <span class="text-gold text-xs font-bold uppercase tracking-widest">Stay Updated</span>
            <h2 class="font-heading text-2xl md:text-3xl font-bold text-white mt-3 mb-3">Never Miss a Post</h2>
            <p class="text-white/40 mb-8 leading-relaxed">Get Disney tips, park guides, and family stories delivered to your inbox. No spam, just magic.</p>
            <form action="/newsletter" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                @csrf
                <input type="email" name="email" placeholder="your@email.com" required
                    class="flex-1 px-5 py-3 rounded-xl text-sm outline-none transition-all"
                    style="background: rgba(254,249,239,0.08); border: 1px solid rgba(254,249,239,0.12); color: #fef9ef;"
                    onfocus="this.style.borderColor='rgba(212,168,67,0.4)'"
                    onblur="this.style.borderColor='rgba(254,249,239,0.12)'"
                >
                <button type="submit" class="px-6 py-3 rounded-xl font-bold text-sm text-navy transition-all hover:-translate-y-0.5 shadow-lg shadow-gold/20"
                    style="background: linear-gradient(135deg, #d4a843, #f0c75e);">
                    Subscribe
                </button>
            </form>
            <p class="text-white/20 text-[10px] mt-4">No spam. Unsubscribe anytime.</p>
        </div>
    </section>
    @endif
@endsection
