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
    <section class="bg-cream border-b border-navy/5">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="pt-6 pb-2">
                <form action="/blog" method="GET" class="relative max-w-xl mx-auto">
                    @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
                    <svg class="absolute left-5 top-1/2 -translate-y-1/2 w-5 h-5 text-navy/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search posts..."
                        class="w-full pl-13 pr-5 py-3.5 rounded-full border border-navy/10 bg-white text-navy text-sm placeholder:text-navy/35 focus:outline-none focus:ring-2 focus:ring-purple/30 focus:border-purple transition-all shadow-sm" style="padding-left: 3rem;">
                </form>
            </div>
            <div class="flex items-center gap-2 py-4 overflow-x-auto scrollbar-hide">
                <a href="/blog"
                   class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 {{ !$category ? 'bg-gold text-white shadow-md' : 'bg-white text-navy/60 hover:text-navy hover:shadow-sm border border-navy/10' }}">
                    All
                </a>
                @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                    @php
                        $dotColors = [
                            'park-tips' => 'bg-gold',
                            'accessibility' => 'bg-purple',
                            'food-dining' => 'bg-orange-400',
                            'family-stories' => 'bg-pink-400',
                            'planning' => 'bg-blue-400',
                            'reviews' => 'bg-green-400',
                        ];
                        $dot = $dotColors[$slug] ?? 'bg-purple';
                    @endphp
                    <a href="/blog?category={{ $slug }}"
                       class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-semibold transition-all duration-200 inline-flex items-center gap-2 {{ $category === $slug ? 'bg-gold text-white shadow-md' : 'bg-white text-navy/60 hover:text-navy hover:shadow-sm border border-navy/10' }}">
                        <span class="category-dot {{ $category === $slug ? 'bg-white/70' : $dot }}"></span>
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

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
                <div class="text-center py-20 bg-white rounded-2xl border border-navy/5">
                    <div class="text-6xl mb-6 flex items-center justify-center gap-2">
                        <span class="inline-block -rotate-12">🏰</span>
                        <span class="inline-block rotate-6 text-5xl">📝</span>
                        <span class="inline-block -rotate-6">✨</span>
                    </div>
                    <h2 class="font-heading text-2xl font-bold text-navy mb-2">
                        @if($category)
                            No {{ \App\Models\Post::CATEGORIES[$category] ?? 'matching' }} posts yet
                        @else
                            Posts Coming Soon!
                        @endif
                    </h2>
                    <p class="text-navy/50 mb-6 max-w-sm mx-auto">We're busy writing up our best Disney tips and magical stories. Check back soon!</p>
                    @if($category)
                        <a href="/blog" class="inline-flex items-center gap-2 bg-gold hover:bg-gold-light text-white font-semibold text-sm px-6 py-2.5 rounded-full transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            View all posts
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
