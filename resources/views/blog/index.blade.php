@extends('layouts.app')

@section('title', ($category ? \App\Models\Post::CATEGORIES[$category] ?? ucwords(str_replace('-', ' ', $category)) : 'Blog') . ' — Mouse28')

@section('content')
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">Blog</span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-white mt-2">Tips, Stories &amp; Disney Life</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto">Park accessibility guides, sensory-friendly tips, and stories from our family's Disney adventures.</p>
        </div>
    </section>

    {{-- Category Filters --}}
    <section class="bg-cream border-b border-navy/5">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center gap-2 py-5 overflow-x-auto scrollbar-hide">
                <a href="/blog"
                   class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-semibold transition-all {{ !$category ? 'bg-gold text-white shadow-md' : 'bg-white text-navy/60 hover:text-navy hover:shadow-sm border border-navy/10' }}">
                    All
                </a>
                @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                    <a href="/blog?category={{ $slug }}"
                       class="flex-shrink-0 px-5 py-2 rounded-full text-sm font-semibold transition-all {{ $category === $slug ? 'bg-gold text-white shadow-md' : 'bg-white text-navy/60 hover:text-navy hover:shadow-sm border border-navy/10' }}">
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
                        <a href="/blog/{{ $post->slug }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1.5 border border-navy/5">
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
                                <div class="flex items-center justify-between text-xs text-navy/40 pt-3 border-t border-navy/5">
                                    <span>{{ $post->published_at->format('M j, Y') }}</span>
                                    <span>{{ $post->reading_time }} min read</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $posts->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-2xl border border-navy/5">
                    <span class="text-5xl block mb-4">📝</span>
                    <h2 class="font-heading text-2xl font-bold text-navy mb-2">
                        @if($category)
                            No {{ \App\Models\Post::CATEGORIES[$category] ?? 'matching' }} posts yet
                        @else
                            Posts Coming Soon!
                        @endif
                    </h2>
                    <p class="text-navy/50 mb-6">We're writing up our best Disney tips and stories. Check back soon.</p>
                    @if($category)
                        <a href="/blog" class="inline-flex items-center gap-2 text-gold font-semibold hover:text-gold/80 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            View all posts
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </section>
@endsection
