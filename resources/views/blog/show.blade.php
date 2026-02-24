@extends('layouts.app')

@section('title', $post->title . ' — Mouse28')
@section('meta_description', Str::limit($post->excerpt, 160))

@section('content')
    {{-- Header --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <a href="/blog" class="text-white/50 hover:text-gold text-sm transition-colors mb-6 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                All Posts
            </a>
            <div class="mt-4 max-w-3xl">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    @if($post->category)
                        <span class="text-sm font-semibold px-4 py-1 rounded-full {{ $post->category_color }}">{{ $post->category_label }}</span>
                    @endif
                    <span class="text-white/40 text-sm">{{ $post->published_at->format('F j, Y') }}</span>
                    <span class="text-white/30">·</span>
                    <span class="text-white/40 text-sm">{{ $post->reading_time }} min read</span>
                </div>
                <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white">{{ $post->title }}</h1>
                @if($post->excerpt)
                    <p class="text-white/60 text-lg mt-4 leading-relaxed">{{ $post->excerpt }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="py-12 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col lg:flex-row gap-10">
                {{-- Main Content --}}
                <div class="lg:w-[68%]">
                    @if($post->cover_image)
                        <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-64 md:h-96 object-cover rounded-2xl mb-10 shadow-sm">
                    @endif

                    <article class="bg-white rounded-2xl p-8 md:p-12 shadow-sm border border-navy/5">
                        <div class="prose prose-lg prose-navy max-w-none text-navy/80 leading-relaxed">
                            {!! $post->body !!}
                        </div>
                    </article>

                    {{-- Related Episode --}}
                    @if($post->episode)
                        <div class="mt-8 bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <span class="text-gold text-xs font-semibold uppercase tracking-wider">Related Episode</span>
                            <a href="/episodes/{{ $post->episode->slug }}" class="group block mt-3">
                                <div class="flex items-center gap-4">
                                    <span class="flex-shrink-0 inline-flex items-center justify-center w-12 h-12 bg-purple/10 text-purple font-heading font-bold rounded-xl">{{ $post->episode->episode_number }}</span>
                                    <div>
                                        <h3 class="font-heading text-lg font-semibold text-navy group-hover:text-purple transition-colors">{{ $post->episode->title }}</h3>
                                        <p class="text-navy/50 text-sm mt-1">Listen to the full episode →</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[32%] space-y-8">
                    {{-- Recent Posts --}}
                    @if($recentPosts->count())
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <h3 class="font-heading text-lg font-bold text-navy mb-4">Recent Posts</h3>
                            <div class="space-y-4">
                                @foreach($recentPosts as $recent)
                                    <a href="/blog/{{ $recent->slug }}" class="group flex gap-3 items-start">
                                        @if($recent->cover_image)
                                            <img src="{{ $recent->cover_image }}" alt="" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                                        @else
                                            <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center flex-shrink-0">
                                                <span class="text-sm">✨</span>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-semibold text-navy group-hover:text-purple transition-colors line-clamp-2 leading-snug">{{ $recent->title }}</h4>
                                            <span class="text-xs text-navy/40 mt-1 block">{{ $recent->published_at->format('M j, Y') }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Categories --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                        <h3 class="font-heading text-lg font-bold text-navy mb-4">Categories</h3>
                        <div class="space-y-2">
                            @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                                @php $count = $categoryCounts[$slug] ?? 0; @endphp
                                @if($count > 0)
                                    <a href="/blog?category={{ $slug }}" class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-cream transition-colors group">
                                        <span class="text-sm text-navy/70 group-hover:text-navy transition-colors">{{ $label }}</span>
                                        <span class="text-xs text-navy/30 bg-navy/5 px-2 py-0.5 rounded-full">{{ $count }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Podcast CTA --}}
                    <div class="bg-gradient-to-br from-navy to-navy-light rounded-2xl p-6 text-center">
                        <span class="text-3xl block mb-3">🎧</span>
                        <h3 class="font-heading text-lg font-bold text-white mb-2">Listen to the Podcast</h3>
                        <p class="text-white/50 text-sm mb-4">Catch our latest episodes about Disney, accessibility, and family life.</p>
                        <a href="/episodes" class="inline-block bg-gold hover:bg-gold/90 text-white font-semibold text-sm px-6 py-2.5 rounded-full transition-colors">
                            Browse Episodes
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
