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

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-24 relative overflow-hidden">
        {{-- Stacked paper/cards decoration --}}
        <div class="absolute top-8 right-8 opacity-10 pointer-events-none hidden md:block">
            <svg width="140" height="140" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                {{-- Back card (most rotated) --}}
                <rect x="30" y="10" width="85" height="110" rx="8" stroke="white" stroke-width="1.5" transform="rotate(8 72 65)" fill="white" fill-opacity="0.02"/>
                {{-- Middle card --}}
                <rect x="25" y="12" width="85" height="110" rx="8" stroke="white" stroke-width="1.5" transform="rotate(-4 67 67)" fill="white" fill-opacity="0.03"/>
                {{-- Front card --}}
                <rect x="20" y="15" width="85" height="110" rx="8" stroke="white" stroke-width="2" fill="white" fill-opacity="0.04"/>
                {{-- Lines on front card suggesting text --}}
                <line x1="32" y1="40" x2="78" y2="40" stroke="white" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                <line x1="32" y1="52" x2="92" y2="52" stroke="white" stroke-width="1" stroke-linecap="round" opacity="0.25"/>
                <line x1="32" y1="62" x2="88" y2="62" stroke="white" stroke-width="1" stroke-linecap="round" opacity="0.25"/>
                <line x1="32" y1="72" x2="72" y2="72" stroke="white" stroke-width="1" stroke-linecap="round" opacity="0.2"/>
            </svg>
        </div>
        {{-- Scattered cards along bottom --}}
        <div class="absolute bottom-0 left-0 w-full opacity-[0.06] pointer-events-none">
            <svg class="w-full h-28" viewBox="0 0 1200 112" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
                {{-- Scattered small card outlines --}}
                <rect x="80" y="40" width="55" height="70" rx="5" stroke="white" stroke-width="1.5" transform="rotate(-12 107 75)"/>
                <rect x="250" y="50" width="55" height="70" rx="5" stroke="white" stroke-width="1.5" transform="rotate(6 277 85)"/>
                <rect x="460" y="35" width="55" height="70" rx="5" stroke="white" stroke-width="1.5" transform="rotate(-8 487 70)"/>
                <rect x="680" y="45" width="55" height="70" rx="5" stroke="white" stroke-width="1.5" transform="rotate(10 707 80)"/>
                <rect x="880" y="38" width="55" height="70" rx="5" stroke="white" stroke-width="1.5" transform="rotate(-5 907 73)"/>
                <rect x="1060" y="50" width="55" height="70" rx="5" stroke="white" stroke-width="1.5" transform="rotate(7 1087 85)"/>
            </svg>
        </div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 mb-6">
                <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                <span class="text-gold text-sm font-semibold tracking-widest uppercase">Stories & Tips</span>
            </div>
            <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white mt-2">Blog <span class="inline-block text-gold/40 text-lg">✦</span></h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto text-lg">Disney tips, park guides, and stories from our family to yours.</p>
        </div>
    </section>

    {{-- Posts Section with Sidebar --}}
    <section class="py-12 bg-cream relative">
        <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(#1a1040 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative">
            <div class="flex flex-col lg:flex-row gap-10">
                {{-- Main Content --}}
                <div class="lg:w-[66%]">
            @if($posts->count())
                @php $featured = $posts->first(); $rest = $posts->skip(1); @endphp

                {{-- Featured Post: Single-column, navy gradient + animated border + ribbon --}}
                @if($posts->currentPage() === 1 && !request('q'))
                    @php $fColor = $categoryColors[$featured->category] ?? '#5b3e9e'; @endphp
                    <div class="mb-8 featured-wrapper rounded-3xl" style="overflow: visible;">
                        {{-- Corner ribbon --}}
                        <div class="ribbon ribbon-top-left"><span>Featured</span></div>

                        <a href="/blog/{{ $featured->slug }}" class="featured-card-border group block transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                            <div class="p-8 md:p-10 lg:p-12 pt-20 md:pt-16 relative">
                                {{-- Category + read time --}}
                                <div class="flex items-center gap-3 mb-5 md:pl-20">
                                    @if($featured->category)
                                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background: {{ $fColor }}30; color: {{ $fColor }};">{{ $featured->category_label }}</span>
                                    @endif
                                    <span class="text-white/30 text-xs">{{ $featured->reading_time }} min read</span>
                                </div>

                                {{-- Title --}}
                                <h2 class="font-heading text-2xl md:text-3xl font-bold text-white group-hover:text-gold transition-colors leading-snug md:pl-20">{{ $featured->title }}</h2>

                                {{-- Excerpt --}}
                                @if($featured->excerpt)
                                    <p class="text-white/55 text-sm md:text-base leading-relaxed mt-4 md:pl-20 max-w-2xl">{{ $featured->excerpt }}</p>
                                @endif

                                {{-- Author + CTA --}}
                                <div class="flex items-center justify-between mt-8 pt-6 border-t border-white/10 md:ml-20">
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
                        </a>
                    </div>
                @else
                    @php $rest = $posts; @endphp
                @endif

                {{-- Post Grid (single column since sidebar takes space) --}}
                @if($rest->count())
                    <div class="space-y-6">
                        @foreach($rest as $post)
                            @php $pColor = $categoryColors[$post->category] ?? '#5b3e9e'; @endphp
                            <a href="/blog/{{ $post->slug }}" class="post-card group bg-white rounded-2xl p-7 shadow-sm border border-navy/5 relative block">
                                {{-- Top accent bar on hover --}}
                                <div class="accent-bar absolute top-0 left-0 right-0 h-1 rounded-t-2xl" style="background: {{ $pColor }};"></div>
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
                <div class="relative overflow-hidden rounded-3xl" style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 50%, #3a2370 100%); padding: 4rem 2rem;">
                    <div class="relative z-10 text-center">
                        <div style="display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(212, 168, 67, 0.3); border-radius: 9999px; padding: 0.35rem 1rem; margin-bottom: 1.5rem;">
                            <span style="width: 6px; height: 6px; border-radius: 50%; background: #d4a843;"></span>
                            <span style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: #d4a843; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 600;">
                                @if(request('q')) No Results @elseif($category) No Posts Yet @else Coming Soon @endif
                            </span>
                        </div>
                        <h2 class="font-heading text-2xl font-bold text-cream mb-3">
                            @if(request('q'))
                                No posts match "{{ request('q') }}"
                            @elseif($category)
                                Nothing in {{ \App\Models\Post::CATEGORIES[$category] ?? 'this category' }} yet
                            @else
                                We're putting pen to paper
                            @endif
                        </h2>
                        <p style="color: rgba(254, 249, 239, 0.5); font-size: 0.9rem; line-height: 1.8; margin-bottom: 1.5rem;">
                            @if(request('q'))
                                Try a different search term or browse all posts.
                            @elseif($category)
                                Check out everything else in the meantime.
                            @else
                                Park tips, accessibility insights, and real family stories. Our first posts are in the works.
                            @endif
                        </p>
                        @if(request('q') || $category)
                            <a href="/blog" class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-lg transition-all hover:-translate-y-0.5" style="background: rgba(212, 168, 67, 0.15); color: #d4a843; border: 1px solid rgba(212, 168, 67, 0.25);">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                View all posts
                            </a>
                        @endif
                    </div>
                </div>
            @endif
                </div>{{-- end main content --}}

                {{-- Sidebar --}}
                <aside class="lg:w-[34%]">
                    <div class="lg:sticky lg:top-[90px] space-y-6">
                        {{-- Search --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-4">
                                <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Search</h3>
                            </div>
                            <form action="/blog" method="GET" class="relative">
                                @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
                                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-navy/25" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts..."
                                    class="w-full pl-11 pr-4 py-3 rounded-xl border border-navy/10 text-sm text-navy placeholder:text-navy/25 outline-none transition-all focus:border-gold focus:ring-2 focus:ring-gold/20"
                                >
                                @if(request('q'))
                                    <a href="/blog{{ $category ? '?category='.$category : '' }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-navy/30 hover:text-gold transition-colors" title="Clear search">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </a>
                                @endif
                            </form>
                            @if(request('q'))
                                <p class="text-navy/40 text-xs mt-2">{{ $posts->total() }} {{ Str::plural('result', $posts->total()) }} for "<span class="text-gold font-semibold">{{ request('q') }}</span>"</p>
                            @endif
                        </div>

                        {{-- Categories --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-4">
                                <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Categories</h3>
                            </div>
                            <div class="space-y-1">
                                <a href="/blog" class="flex items-center justify-between py-2.5 px-3 rounded-xl transition-all group {{ !$category ? 'bg-gold/10' : 'hover:bg-cream' }}">
                                    <span class="text-sm font-medium {{ !$category ? 'text-gold font-semibold' : 'text-navy/65 group-hover:text-navy' }} transition-colors">All Posts</span>
                                    <span class="text-xs bg-gold/8 px-3 py-0.5 rounded-full font-bold {{ !$category ? 'text-gold' : 'text-gold/70' }}">{{ $posts->total() }}</span>
                                </a>
                                @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                                    @continue(!in_array($slug, $usedCategories))
                                    @php $color = $categoryColors[$slug] ?? '#7b5eb5'; @endphp
                                    <a href="/blog?category={{ $slug }}" class="flex items-center justify-between py-2.5 px-3 rounded-xl transition-all group {{ $category === $slug ? 'bg-opacity-10' : 'hover:bg-cream' }}" @if($category === $slug) style="background: {{ $color }}12;" @endif>
                                        <span class="text-sm font-medium transition-colors {{ $category === $slug ? 'font-semibold' : 'text-navy/65 group-hover:text-navy' }}" @if($category === $slug) style="color: {{ $color }};" @endif>{{ $label }}</span>
                                        <span class="text-xs px-3 py-0.5 rounded-full font-bold" style="background: {{ $color }}12; color: {{ $color }};">{{ $categoryCounts[$slug] ?? 0 }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Sort --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-4">
                                <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Sort By</h3>
                            </div>
                            <div class="flex rounded-xl overflow-hidden border border-navy/10">
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
                                   class="flex-1 text-center py-2.5 text-xs font-semibold transition-all {{ ($sort ?? 'newest') === 'newest' ? 'bg-gold/15 text-gold' : 'text-navy/40 hover:text-navy/60' }}">
                                    Newest
                                </a>
                                <a href="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}"
                                   class="flex-1 text-center py-2.5 text-xs font-semibold transition-all border-l border-navy/10 {{ ($sort ?? 'newest') === 'oldest' ? 'bg-gold/15 text-gold' : 'text-navy/40 hover:text-navy/60' }}">
                                    Oldest
                                </a>
                            </div>
                        </div>

                        {{-- Post Stats --}}
                        @if($hasAnyPosts)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center justify-around">
                                <div class="text-center">
                                    <span class="block text-2xl font-bold text-navy font-heading">{{ $posts->total() }}</span>
                                    <span class="text-navy/35 text-xs uppercase tracking-wider">{{ Str::plural('Post', $posts->total()) }}</span>
                                </div>
                                <div class="w-px h-10 bg-navy/8"></div>
                                <div class="text-center">
                                    <span class="block text-2xl font-bold text-gold font-heading">{{ count($usedCategories) }}</span>
                                    <span class="text-navy/35 text-xs uppercase tracking-wider">{{ Str::plural('Category', count($usedCategories)) }}</span>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Newsletter --}}
                        <div class="rounded-2xl overflow-hidden shadow-sm border border-gold/15">
                            <div class="bg-gradient-to-r from-gold/15 via-gold/8 to-purple/8 px-6 pt-5 pb-4 text-center relative">
                                <div class="absolute top-3 right-4 text-gold/15 text-xs">✦</div>
                                <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-white/80 flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </div>
                                <h3 class="font-heading text-base font-bold text-navy">Stay in the Loop</h3>
                                <p class="text-navy/45 text-xs mt-1">Disney tips & new posts</p>
                            </div>
                            <div class="bg-white px-6 py-5">
                                <form action="/newsletter" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="email" name="email" placeholder="your@email.com" required
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-navy/10 focus:border-gold focus:ring-2 focus:ring-gold/20 outline-none transition-all placeholder:text-navy/25 text-navy">
                                    <button type="submit" class="w-full bg-gradient-to-r from-gold to-gold-light text-navy font-bold text-sm py-2.5 rounded-xl transition-all hover:-translate-y-0.5 shadow-md shadow-gold/15">
                                        Subscribe ✨
                                    </button>
                                </form>
                                <p class="text-navy/25 text-[10px] text-center mt-3">No spam. Unsubscribe anytime.</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>{{-- end flex --}}
        </div>
    </section>
@endsection
