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

        /* Post cards */
        .post-card {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .post-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: var(--cat-color, #5b3e9e);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 2;
        }
        .post-card:hover::before { transform: scaleX(1); }
        .post-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(26,16,64,0.08), 0 8px 16px rgba(26,16,64,0.04);
        }
        .post-card .card-image {
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .post-card:hover .card-image { transform: scale(1.08); }

        /* Featured card */
        .featured-card {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .featured-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 50px rgba(26,16,64,0.12), 0 10px 20px rgba(26,16,64,0.06);
        }
        .featured-card .featured-image {
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .featured-card:hover .featured-image { transform: scale(1.05); }

        /* Category pills */
        .category-pill-filter {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600;
            font-family: 'Poppins', sans-serif; transition: all 0.25s ease; text-decoration: none;
            position: relative; overflow: hidden;
        }
        .category-pill-filter::after {
            content: '';
            position: absolute; bottom: 0; left: 50%; right: 50%;
            height: 2px; background: currentColor; opacity: 0;
            transition: all 0.3s ease;
        }
        .category-pill-filter:hover::after {
            left: 20%; right: 20%; opacity: 0.3;
        }
        .category-pill-filter.active::after {
            left: 20%; right: 20%; opacity: 0.5;
        }

        /* Reading time badge */
        .reading-badge {
            backdrop-filter: blur(8px);
            transition: all 0.3s ease;
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

        /* Post count badge */
        .post-count-badge {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 1.5rem; height: 1.5rem; padding: 0 0.4rem;
            border-radius: 9999px; font-size: 0.65rem; font-weight: 700;
        }

        /* View toggle */
        .view-btn {
            padding: 0.5rem; border-radius: 0.5rem;
            transition: all 0.2s; color: rgba(254,249,239,0.3);
            border: 1px solid transparent;
        }
        .view-btn:hover { color: rgba(254,249,239,0.6); }
        .view-btn.active {
            color: #d4a843; background: rgba(212,168,67,0.1);
            border-color: rgba(212,168,67,0.2);
        }
    </style>

    {{-- Hero --}}
    <section class="relative overflow-hidden" style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 60%, #1a1040 100%);">
        {{-- Decorative elements --}}
        <div class="absolute top-0 right-0 w-96 h-96 bg-gold/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple/10 rounded-full blur-3xl translate-y-1/2 -translate-x-1/4"></div>
        <div class="absolute top-[15%] right-[10%] text-gold/20 text-sm" style="animation: sparkle-float 4s ease-in-out infinite;">&#10022;</div>
        <div class="absolute top-[35%] left-[7%] text-gold/10 text-xs" style="animation: sparkle-float 5s ease-in-out 1.5s infinite;">&#10022;</div>

        <div class="relative max-w-6xl mx-auto px-4 sm:px-6 py-16 md:py-20 text-center">
            <div class="inline-flex items-center gap-2 border border-gold/20 rounded-full px-4 py-1.5 mb-6">
                <span class="w-1.5 h-1.5 rounded-full bg-gold"></span>
                <span class="text-gold text-xs font-semibold tracking-widest uppercase" style="font-family: 'Poppins', sans-serif;">Blog</span>
            </div>
            <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight">
                Tips, Stories &amp; <span class="text-gold">Disney Life</span>
            </h1>
            <p class="text-white/50 mt-5 max-w-xl mx-auto text-lg leading-relaxed">Park accessibility guides, sensory-friendly tips, and stories from our family's Disney adventures.</p>

            {{-- Post stats --}}
            @if($hasAnyPosts)
                <div class="flex items-center justify-center gap-6 mt-8">
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
            @endif
        </div>
    </section>

    {{-- Search + Filters Bar --}}
    @if($hasAnyPosts)
    <section style="background: linear-gradient(180deg, #1a1040 0%, #2d1b69 100%); position: relative;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-2 pb-7">
            {{-- Search --}}
            <form action="/blog" method="GET" class="relative max-w-xl mx-auto mb-6">
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
                    'general' => '#64748b',
                ];
            @endphp
            <div class="flex items-center justify-center gap-2 flex-wrap">
                <a href="/blog" class="category-pill-filter {{ !$category ? 'active' : '' }}"
                   style="{{ !$category
                       ? 'background: rgba(212,168,67,0.15); color: #d4a843; border: 1px solid rgba(212,168,67,0.3);'
                       : 'background: rgba(254,249,239,0.04); color: rgba(254,249,239,0.5); border: 1px solid rgba(254,249,239,0.08);' }}">
                    All Posts
                    <span class="post-count-badge" style="{{ !$category ? 'background: rgba(212,168,67,0.2); color: #d4a843;' : 'background: rgba(254,249,239,0.08); color: rgba(254,249,239,0.4);' }}">{{ $posts->total() }}</span>
                </a>

                @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                    @continue(!in_array($slug, $usedCategories))
                    @php $color = $categoryColors[$slug] ?? '#7b5eb5'; $isActive = $category === $slug; @endphp
                    <a href="/blog?category={{ $slug }}" class="category-pill-filter {{ $isActive ? 'active' : '' }}"
                       style="{{ $isActive
                           ? "background: {$color}20; color: {$color}; border: 1px solid {$color}40;"
                           : "background: rgba(254,249,239,0.04); color: rgba(254,249,239,0.5); border: 1px solid rgba(254,249,239,0.08);" }}">
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
        </div>
        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(212,168,67,0.2), transparent);"></div>
    </section>
    @endif

    {{-- Posts Section --}}
    <section class="py-16 bg-cream relative">
        {{-- Subtle pattern overlay --}}
        <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(#1a1040 1px, transparent 1px); background-size: 24px 24px;"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative">
            @if($posts->count())
                @php $featured = $posts->first(); $rest = $posts->skip(1); @endphp

                @php
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
                        'general' => ['from' => '#475569', 'to' => '#94a3b8', 'icon' => '✨'],
                    ];
                    $defaultGrad = ['from' => '#5b3e9e', 'to' => '#7c5cbf', 'icon' => '✨'];
                @endphp

                {{-- Featured Post (first post, large card) --}}
                @if($posts->currentPage() === 1 && !request('q'))
                    @php $fGrad = $categoryGradients[$featured->category] ?? $defaultGrad; @endphp
                    <a href="/blog/{{ $featured->slug }}" class="featured-card group block bg-white rounded-3xl overflow-hidden shadow-lg shadow-navy/5 border border-navy/5 mb-12">
                        <div class="grid md:grid-cols-2">
                            {{-- Visual side --}}
                            <div class="relative overflow-hidden" style="min-height: 320px; background: linear-gradient(135deg, {{ $fGrad['from'] }}18, {{ $fGrad['to'] }}10);">
                                @if($featured->cover_image)
                                    <img src="{{ $featured->cover_image }}" alt="{{ $featured->title }}" class="featured-image absolute inset-0 w-full h-full object-cover">
                                @else
                                    {{-- Category-themed decorative panel --}}
                                    <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $fGrad['from'] }}15, {{ $fGrad['to'] }}08);">
                                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center">
                                            <span class="text-7xl block mb-4 opacity-80 group-hover:scale-110 transition-transform duration-500">{{ $fGrad['icon'] }}</span>
                                            <span class="text-xs font-bold uppercase tracking-[0.25em] opacity-20" style="color: {{ $fGrad['from'] }};">{{ $featured->category_label }}</span>
                                        </div>
                                        {{-- Decorative circles --}}
                                        <div class="absolute -top-16 -right-16 w-48 h-48 rounded-full opacity-[0.06]" style="background: {{ $fGrad['from'] }};"></div>
                                        <div class="absolute -bottom-12 -left-12 w-36 h-36 rounded-full opacity-[0.04]" style="background: {{ $fGrad['to'] }};"></div>
                                        <div class="absolute top-8 left-8 w-2 h-2 rounded-full opacity-20" style="background: {{ $fGrad['from'] }};"></div>
                                        <div class="absolute bottom-12 right-10 w-1.5 h-1.5 rounded-full opacity-15" style="background: {{ $fGrad['to'] }};"></div>
                                    </div>
                                @endif
                                {{-- Featured badge --}}
                                <div class="absolute top-5 left-5 z-10">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider text-gold bg-navy/80 backdrop-blur-md border border-gold/20">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                        Latest Post
                                    </span>
                                </div>
                            </div>
                            {{-- Content side --}}
                            <div class="p-8 md:p-10 lg:p-12 flex flex-col justify-center">
                                @if($featured->category)
                                    <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full mb-4 w-fit {{ $featured->category_color }}">
                                        {{ $featured->category_label }}
                                    </span>
                                @endif
                                <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy group-hover:text-purple transition-colors leading-snug">
                                    {{ $featured->title }}
                                </h2>
                                @if($featured->excerpt)
                                    <p class="text-navy/55 mt-4 leading-relaxed line-clamp-3">{{ $featured->excerpt }}</p>
                                @endif
                                <div class="flex items-center gap-4 mt-6 pt-6 border-t border-navy/5">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gold/25 to-purple/15 flex items-center justify-center text-gold text-xs font-bold font-heading border border-gold/15">
                                        {{ collect(explode(' ', $featured->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-navy text-sm font-semibold">{{ $featured->author_name }}</p>
                                        <p class="text-navy/35 text-xs">{{ $featured->published_at->format('F j, Y') }} · {{ $featured->reading_time }} min read</p>
                                    </div>
                                    <span class="hidden sm:inline-flex items-center gap-1.5 text-gold text-sm font-semibold group-hover:gap-2.5 transition-all">
                                        Read
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @else
                    @php $rest = $posts; @endphp
                @endif

                {{-- Post Grid --}}
                @if($rest->count())
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                        @foreach($rest as $post)
                            @php
                                $catColor = $categoryColors[$post->category] ?? '#5b3e9e';
                            @endphp
                            @php $pGrad = $categoryGradients[$post->category] ?? $defaultGrad; @endphp
                            <a href="/blog/{{ $post->slug }}" class="post-card group bg-white rounded-2xl shadow-sm border border-navy/5" style="--cat-color: {{ $catColor }};">
                                {{-- Visual header --}}
                                <div class="relative overflow-hidden rounded-t-2xl" style="height: 180px;">
                                    @if($post->cover_image)
                                        <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="card-image absolute inset-0 w-full h-full object-cover">
                                    @else
                                        <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $pGrad['from'] }}12, {{ $pGrad['to'] }}08);">
                                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                                                <span class="text-5xl block group-hover:scale-110 transition-transform duration-500 opacity-70">{{ $pGrad['icon'] }}</span>
                                            </div>
                                            <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full opacity-[0.05]" style="background: {{ $pGrad['from'] }};"></div>
                                            <div class="absolute -bottom-8 -left-8 w-24 h-24 rounded-full opacity-[0.04]" style="background: {{ $pGrad['to'] }};"></div>
                                        </div>
                                    @endif
                                    {{-- Reading time overlay --}}
                                    <div class="absolute top-3 right-3">
                                        <span class="reading-badge text-[10px] font-bold text-white bg-navy/60 px-2.5 py-1 rounded-full">
                                            {{ $post->reading_time }} min
                                        </span>
                                    </div>
                                </div>

                                {{-- Content --}}
                                <div class="p-6">
                                    @if($post->category)
                                        <span class="inline-block text-[10px] font-bold px-2.5 py-1 rounded-full mb-3 uppercase tracking-wider {{ $post->category_color }}">
                                            {{ $post->category_label }}
                                        </span>
                                    @endif
                                    <h2 class="font-heading text-lg font-bold text-navy group-hover:text-purple transition-colors leading-snug line-clamp-2">
                                        {{ $post->title }}
                                    </h2>
                                    @if($post->excerpt)
                                        <p class="text-navy/50 text-sm leading-relaxed line-clamp-2 mt-2">{{ Str::limit($post->excerpt, 120) }}</p>
                                    @endif
                                    <div class="flex items-center gap-3 mt-5 pt-4 border-t border-navy/5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-gold/20 to-purple/10 flex items-center justify-center text-gold text-[10px] font-bold font-heading flex-shrink-0 border border-gold/10">
                                            {{ collect(explode(' ', $post->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-navy text-xs font-semibold truncate">{{ $post->author_name }}</p>
                                            <p class="text-navy/30 text-[11px]">{{ $post->published_at->format('M j, Y') }}</p>
                                        </div>
                                        <svg class="w-4 h-4 text-navy/20 group-hover:text-gold group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                {{-- Pagination --}}
                @if($posts->hasPages())
                    <div class="mt-14 pagination flex justify-center">
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

    {{-- Newsletter CTA (bottom of page) --}}
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
