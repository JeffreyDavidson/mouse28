@extends('layouts.app')

@section('title', $episode->title . ' — Mouse28')
@section('meta_description', Str::limit($episode->description, 160))

@section('content')
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <a href="/episodes" class="text-white/50 hover:text-gold text-sm transition-colors mb-6 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                All Episodes
            </a>
            <div class="flex items-center gap-3 mb-4 mt-4">
                <span class="bg-gold/20 text-gold text-sm font-bold px-4 py-1 rounded-full">Episode {{ $episode->episode_number }}</span>
                @if($episode->duration_seconds)
                    <span class="text-white/40 text-sm">{{ $episode->formatted_duration }}</span>
                @endif
                <span class="text-white/40 text-sm">{{ $episode->published_at->format('F j, Y') }}</span>
            </div>
            <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white">{{ $episode->title }}</h1>
            @if($episode->description)
                <p class="text-white/60 text-lg mt-4 leading-relaxed">{{ $episode->description }}</p>
            @endif
        </div>
    </section>

    <section class="py-12 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            {{-- Audio Player --}}
            @if($episode->audio_url)
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5 mb-8">
                    <audio controls class="w-full" preload="metadata">
                        <source src="{{ $episode->audio_url }}" type="audio/mpeg">
                        Your browser does not support the audio element.
                    </audio>
                </div>
            @endif

            {{-- Platform Links --}}
            <div class="flex flex-wrap gap-3 mb-10">
                @if($episode->apple_url)
                    <a href="{{ $episode->apple_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 bg-white text-navy font-medium px-5 py-2.5 rounded-full shadow-sm hover:shadow-md transition-all text-sm border border-navy/5">🎧 Apple Podcasts</a>
                @endif
                @if($episode->spotify_url)
                    <a href="{{ $episode->spotify_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 bg-[#1DB954] text-white font-medium px-5 py-2.5 rounded-full shadow-sm hover:shadow-md transition-all text-sm">💚 Spotify</a>
                @endif
                @if($episode->youtube_url)
                    <a href="{{ $episode->youtube_url }}" target="_blank" rel="noopener" class="flex items-center gap-2 bg-red-600 text-white font-medium px-5 py-2.5 rounded-full shadow-sm hover:shadow-md transition-all text-sm">▶️ YouTube</a>
                @endif
            </div>

            {{-- Show Notes --}}
            @if($episode->show_notes)
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-navy/5">
                    <h2 class="font-heading text-2xl font-bold text-navy mb-6">Show Notes</h2>
                    <div class="prose prose-navy max-w-none text-navy/80 leading-relaxed">
                        {!! nl2br(e($episode->show_notes)) !!}
                    </div>
                </div>
            @endif

            {{-- Related Blog Posts --}}
            @if($relatedPosts->count())
                <div class="mt-12">
                    <h3 class="font-heading text-xl font-bold text-navy mb-6">Related Posts</h3>
                    <div class="grid sm:grid-cols-2 gap-6">
                        @foreach($relatedPosts as $post)
                            <a href="/blog/{{ $post->slug }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all border border-navy/5">
                                @if($post->cover_image)
                                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-40 object-cover">
                                @else
                                    <div class="w-full h-40 bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center">
                                        <span class="text-2xl">✨</span>
                                    </div>
                                @endif
                                <div class="p-5">
                                    <h4 class="font-heading text-lg font-semibold text-navy group-hover:text-purple transition-colors">{{ $post->title }}</h4>
                                    <p class="text-navy/60 text-sm mt-2 line-clamp-2">{{ Str::limit($post->excerpt, 100) }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
