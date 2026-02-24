@extends('layouts.app')

@section('title', $post->title . ' — Mouse28')
@section('meta_description', Str::limit($post->excerpt, 160))

@section('content')
    {{-- Header --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <a href="/blog" class="text-white/50 hover:text-gold text-sm transition-colors mb-6 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                All Posts
            </a>
            <div class="mt-4">
                <div class="flex items-center gap-3 mb-4">
                    @if($post->category)
                        <span class="bg-gold/20 text-gold text-sm font-semibold px-4 py-1 rounded-full">{{ str_replace('-', ' ', $post->category) }}</span>
                    @endif
                    <span class="text-white/40 text-sm">{{ $post->published_at->format('F j, Y') }}</span>
                </div>
                <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white">{{ $post->title }}</h1>
                @if($post->excerpt)
                    <p class="text-white/60 text-lg mt-4 leading-relaxed">{{ $post->excerpt }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="py-12 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            {{-- Cover Image --}}
            @if($post->cover_image)
                <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-64 md:h-96 object-cover rounded-2xl mb-10 shadow-sm">
            @endif

            {{-- Article Body --}}
            <article class="bg-white rounded-2xl p-8 md:p-12 shadow-sm border border-navy/5">
                <div class="prose prose-lg prose-navy max-w-none text-navy/80 leading-relaxed">
                    {!! $post->body !!}
                </div>
            </article>

            {{-- Related Episode --}}
            @if($post->episode)
                <div class="mt-10 bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                    <span class="text-gold text-xs font-semibold uppercase tracking-wider">Related Episode</span>
                    <a href="/episodes/{{ $post->episode->slug }}" class="group block mt-3">
                        <div class="flex items-center gap-4">
                            <span class="flex-shrink-0 inline-flex items-center justify-center w-12 h-12 bg-disney-blue/10 text-disney-blue font-heading font-bold rounded-xl">{{ $post->episode->episode_number }}</span>
                            <div>
                                <h3 class="font-heading text-lg font-semibold text-navy group-hover:text-disney-blue transition-colors">{{ $post->episode->title }}</h3>
                                <p class="text-navy/50 text-sm mt-1">Listen to the full episode →</p>
                            </div>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </section>
@endsection
