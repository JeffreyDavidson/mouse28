@extends('layouts.app')

@section('title', ($category ? \App\Models\Post::CATEGORIES[$category] ?? ucwords(str_replace('-', ' ', $category)) : 'Blog') . ' — Mouse28')

@section('content')
    <style>
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
        .category-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    </style>

    {{-- Hero --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">Blog</span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-white mt-2">Tips, Stories &amp; Disney Life</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto">Park accessibility guides, sensory-friendly tips, and stories from our family's Disney adventures.</p>
        </div>
    </section>

    {{-- Search + Category Filters --}}
    @if($hasAnyPosts)
    <section style="background: linear-gradient(180deg, #1a1040 0%, #2d1b69 100%); padding: 0 0 1px 0; position: relative;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6" style="padding-top: 2rem; padding-bottom: 1.75rem;">
            {{-- Search --}}
            <form action="/blog" method="GET" class="relative max-w-xl mx-auto" style="margin-bottom: 1.5rem;">
                @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
                <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-4 h-4" style="color: rgba(254,249,239,0.3);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts..."
                    style="width: 100%; padding: 0.75rem 1.25rem 0.75rem 2.75rem; border-radius: 0.75rem; border: 1px solid rgba(254,249,239,0.1); background: rgba(254,249,239,0.05); color: #fef9ef; font-size: 0.875rem; font-family: 'Poppins', sans-serif; outline: none; transition: all 0.2s;"
                    onfocus="this.style.borderColor='rgba(212,168,67,0.4)';this.style.background='rgba(254,249,239,0.08)'"
                    onblur="this.style.borderColor='rgba(254,249,239,0.1)';this.style.background='rgba(254,249,239,0.05)'"
                >
            </form>

            {{-- Categories --}}
            @php
                $categoryIcons = [
                    'park-tips' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>',
                    'accessibility' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
                    'food-dining' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm0-6C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>',
                    'family-stories' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
                    'planning' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>',
                    'reviews' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
                ];
                $categoryColors = [
                    'park-tips' => '#d4a843',
                    'accessibility' => '#7b5eb5',
                    'food-dining' => '#f97316',
                    'family-stories' => '#ec4899',
                    'planning' => '#3b82f6',
                    'reviews' => '#22c55e',
                ];
            @endphp
            <div class="flex items-center justify-center gap-2 flex-wrap">
                <a href="/blog" style="
                    display: inline-flex; align-items: center; gap: 0.4rem;
                    padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600;
                    font-family: 'Poppins', sans-serif; transition: all 0.2s; text-decoration: none;
                    {{ !$category
                        ? 'background: rgba(212,168,67,0.15); color: #d4a843; border: 1px solid rgba(212,168,67,0.3);'
                        : 'background: rgba(254,249,239,0.04); color: rgba(254,249,239,0.5); border: 1px solid rgba(254,249,239,0.08);'
                    }}
                ">All Posts</a>

                @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                    @php $color = $categoryColors[$slug] ?? '#7b5eb5'; $icon = $categoryIcons[$slug] ?? ''; $isActive = $category === $slug; @endphp
                    <a href="/blog?category={{ $slug }}" style="
                        display: inline-flex; align-items: center; gap: 0.4rem;
                        padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600;
                        font-family: 'Poppins', sans-serif; transition: all 0.2s; text-decoration: none;
                        {{ $isActive
                            ? "background: " . $color . "20; color: " . $color . "; border: 1px solid " . $color . "40;"
                            : "background: rgba(254,249,239,0.04); color: rgba(254,249,239,0.5); border: 1px solid rgba(254,249,239,0.08);"
                        }}
                    ">
                        @if($icon)
                            <svg style="width: 14px; height: 14px; {{ $isActive ? 'color: ' . $color : 'color: rgba(254,249,239,0.35)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $icon !!}</svg>
                        @endif
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Sort Toggle --}}
            <div class="flex justify-center mt-4">
                <div style="display: inline-flex; border-radius: 0.5rem; overflow: hidden; border: 1px solid rgba(254,249,239,0.1);">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'newest']) }}"
                       style="padding: 0.4rem 1rem; font-size: 0.75rem; font-weight: 600; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s;
                       {{ ($sort ?? 'newest') === 'newest' ? 'background: rgba(212,168,67,0.15); color: #d4a843;' : 'background: transparent; color: rgba(254,249,239,0.4);' }}">
                        Newest First
                    </a>
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'oldest']) }}"
                       style="padding: 0.4rem 1rem; font-size: 0.75rem; font-weight: 600; font-family: 'Poppins', sans-serif; text-decoration: none; transition: all 0.2s; border-left: 1px solid rgba(254,249,239,0.1);
                       {{ ($sort ?? 'newest') === 'oldest' ? 'background: rgba(212,168,67,0.15); color: #d4a843;' : 'background: transparent; color: rgba(254,249,239,0.4);' }}">
                        Oldest First
                    </a>
                </div>
            </div>
        </div>
        {{-- Bottom fade into cream --}}
        <div style="height: 1px; background: linear-gradient(90deg, transparent, rgba(212,168,67,0.2), transparent);"></div>
    </section>
    @endif

    {{-- Posts Grid --}}
    <section class="py-16 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            @if($posts->count())
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($posts as $post)
                        <a href="/blog/{{ $post->slug }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 border border-navy/5 relative border-t-3 border-t-transparent hover:border-t-3" style="border-top: 3px solid transparent;" onmouseenter="this.style.borderTopColor='var(--cat-color, #5b3e9e)'" onmouseleave="this.style.borderTopColor='transparent'"
                            @php
                                $colorMap = [
                                    'park-tips' => '#d4a843',
                                    'accessibility' => '#5b3e9e',
                                    'food-dining' => '#f97316',
                                    'family-stories' => '#ec4899',
                                    'planning' => '#3b82f6',
                                    'reviews' => '#22c55e',
                                ];
                            @endphp
                            style="--cat-color: {{ $colorMap[$post->category] ?? '#5b3e9e' }}; border-top: 3px solid transparent;"
                        >
                            @if($post->cover_image)
                                <div class="overflow-hidden">
                                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-52 object-cover group-hover:scale-105 transition-transform duration-500">
                                </div>
                            @else
                                <div class="w-full h-52 bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center">
                                    <span class="text-4xl">✨</span>
                                </div>
                            @endif
                            <div class="p-6">
                                @if($post->category)
                                    <span class="inline-block text-xs font-semibold px-3 py-1 rounded-full mb-3 {{ $post->category_color }}">
                                        {{ $post->category_label }}
                                    </span>
                                @endif
                                <h2 class="font-heading text-lg font-bold text-navy group-hover:text-purple transition-colors mb-2 line-clamp-2">{{ $post->title }}</h2>
                                <p class="text-navy/55 text-sm leading-relaxed line-clamp-2 mb-4">{{ Str::limit($post->excerpt, 120) }}</p>
                                <div class="flex items-center gap-3 pt-3 border-t border-navy/5">
                                    <div class="w-7 h-7 rounded-full bg-purple/10 flex items-center justify-center text-purple text-[10px] font-bold flex-shrink-0">
                                        {{ collect(explode(' ', $post->author_name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
                                    </div>
                                    <div class="flex-1 flex items-center justify-between text-xs text-navy/40">
                                        <span>{{ $post->author_name }} · {{ $post->published_at->format('M j, Y') }}</span>
                                        <span>{{ $post->reading_time }} min</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12 pagination flex justify-center">
                    {{ $posts->withQueryString()->links() }}
                </div>
            @else
                <div class="relative overflow-hidden rounded-3xl" style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 50%, #3a2370 100%); padding: 5rem 3rem;">
                    {{-- Ambient glows --}}
                    <div style="position: absolute; top: -30%; right: -5%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(212, 168, 67, 0.06) 0%, transparent 60%); pointer-events: none;"></div>
                    <div style="position: absolute; bottom: -20%; left: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(91, 62, 158, 0.2) 0%, transparent 60%); pointer-events: none;"></div>

                    <div class="max-w-3xl mx-auto relative z-10">
                        <div class="grid md:grid-cols-2 gap-12 items-center">
                            {{-- Left: Content --}}
                            <div class="text-center md:text-left">
                                <div style="display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(212, 168, 67, 0.3); border-radius: 9999px; padding: 0.35rem 1rem; margin-bottom: 1.5rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #d4a843;"></span>
                                    <span style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: #d4a843; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 600;">
                                        @if($category) No Posts Yet @else Coming Soon @endif
                                    </span>
                                </div>
                                <h2 class="font-heading font-bold text-cream" style="font-size: clamp(1.75rem, 3.5vw, 2.5rem); line-height: 1.15; margin-bottom: 1rem;">
                                    @if($category)
                                        Nothing in {{ \App\Models\Post::CATEGORIES[$category] ?? 'this category' }} yet
                                    @else
                                        We're putting pen to paper
                                    @endif
                                </h2>
                                <p style="color: rgba(254, 249, 239, 0.5); font-size: 0.95rem; line-height: 1.8; margin-bottom: 2rem;">
                                    @if($category)
                                        We haven't published in this category yet, but it's on the list. In the meantime, check out everything else we've been working on.
                                    @else
                                        Park tips, accessibility insights, food reviews, and real family stories from Disney. Our first posts are in the works.
                                    @endif
                                </p>
                                @if($category)
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

                            {{-- Right: Faux blog post cards --}}
                            <div class="hidden md:flex flex-col gap-4">
                                {{-- Card 1 --}}
                                <div style="background: rgba(26, 16, 64, 0.6); border: 1px solid rgba(212, 168, 67, 0.12); border-radius: 1rem; padding: 1.5rem; backdrop-filter: blur(10px); box-shadow: 0 15px 40px rgba(0,0,0,0.2);">
                                    <div style="display: flex; gap: 1rem;">
                                        <div style="width: 72px; height: 72px; border-radius: 0.75rem; background: linear-gradient(135deg, rgba(212, 168, 67, 0.2), rgba(91, 62, 158, 0.3)); flex-shrink: 0;"></div>
                                        <div style="flex: 1;">
                                            <div style="height: 8px; width: 40%; background: rgba(212, 168, 67, 0.25); border-radius: 99px; margin-bottom: 8px;"></div>
                                            <div style="height: 11px; width: 95%; background: rgba(254, 249, 239, 0.15); border-radius: 99px; margin-bottom: 5px;"></div>
                                            <div style="height: 11px; width: 70%; background: rgba(254, 249, 239, 0.1); border-radius: 99px; margin-bottom: 10px;"></div>
                                            <div style="height: 7px; width: 55%; background: rgba(254, 249, 239, 0.05); border-radius: 99px;"></div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Card 2 --}}
                                <div style="background: rgba(26, 16, 64, 0.4); border: 1px solid rgba(212, 168, 67, 0.08); border-radius: 1rem; padding: 1.5rem; backdrop-filter: blur(10px); box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                                    <div style="display: flex; gap: 1rem;">
                                        <div style="width: 72px; height: 72px; border-radius: 0.75rem; background: linear-gradient(135deg, rgba(91, 62, 158, 0.3), rgba(58, 35, 112, 0.4)); flex-shrink: 0;"></div>
                                        <div style="flex: 1;">
                                            <div style="height: 8px; width: 35%; background: rgba(91, 62, 158, 0.3); border-radius: 99px; margin-bottom: 8px;"></div>
                                            <div style="height: 11px; width: 85%; background: rgba(254, 249, 239, 0.12); border-radius: 99px; margin-bottom: 5px;"></div>
                                            <div style="height: 11px; width: 60%; background: rgba(254, 249, 239, 0.08); border-radius: 99px; margin-bottom: 10px;"></div>
                                            <div style="height: 7px; width: 45%; background: rgba(254, 249, 239, 0.04); border-radius: 99px;"></div>
                                        </div>
                                    </div>
                                </div>
                                {{-- Card 3 (faded) --}}
                                <div style="background: rgba(26, 16, 64, 0.25); border: 1px solid rgba(212, 168, 67, 0.05); border-radius: 1rem; padding: 1.5rem; opacity: 0.6;">
                                    <div style="display: flex; gap: 1rem;">
                                        <div style="width: 72px; height: 72px; border-radius: 0.75rem; background: rgba(91, 62, 158, 0.15); flex-shrink: 0;"></div>
                                        <div style="flex: 1;">
                                            <div style="height: 8px; width: 30%; background: rgba(254, 249, 239, 0.08); border-radius: 99px; margin-bottom: 8px;"></div>
                                            <div style="height: 11px; width: 80%; background: rgba(254, 249, 239, 0.06); border-radius: 99px; margin-bottom: 5px;"></div>
                                            <div style="height: 11px; width: 50%; background: rgba(254, 249, 239, 0.04); border-radius: 99px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
