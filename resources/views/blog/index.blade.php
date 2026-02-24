@extends('layouts.app')

@section('title', 'Blog — Mouse28')

@section('content')
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">Blog</span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-white mt-2">Tips, Stories &amp; Disney Life</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto">Park accessibility guides, sensory-friendly tips, and stories from our family's Disney adventures.</p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            @if($posts->count())
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($posts as $post)
                        <a href="/blog/{{ $post->slug }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 border border-navy/5">
                            @if($post->cover_image)
                                <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-disney-blue/10 to-gold/10 flex items-center justify-center">
                                    <span class="text-3xl">✨</span>
                                </div>
                            @endif
                            <div class="p-6">
                                <div class="flex items-center gap-3 mb-2">
                                    @if($post->category)
                                        <span class="text-gold text-xs font-semibold uppercase tracking-wider">{{ str_replace('-', ' ', $post->category) }}</span>
                                    @endif
                                    <span class="text-navy/30 text-xs">{{ $post->published_at->format('M j, Y') }}</span>
                                </div>
                                <h2 class="font-heading text-lg font-semibold text-navy group-hover:text-disney-blue transition-colors mb-2">{{ $post->title }}</h2>
                                <p class="text-navy/60 text-sm leading-relaxed line-clamp-3">{{ Str::limit($post->excerpt, 140) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @else
                <div class="text-center py-16 bg-white rounded-2xl border border-navy/5">
                    <span class="text-5xl block mb-4">📝</span>
                    <h2 class="font-heading text-2xl font-bold text-navy mb-2">Posts Coming Soon!</h2>
                    <p class="text-navy/50">We're writing up our best Disney tips and stories. Check back soon.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
