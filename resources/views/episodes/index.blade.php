@extends('layouts.app')

@section('title', 'Episodes — Mouse28')

@section('content')
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center">
            <span class="text-gold text-sm font-semibold tracking-widest uppercase">All Episodes</span>
            <h1 class="font-heading text-4xl md:text-5xl font-bold text-white mt-2">Listen Along</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto">Every week we bring you stories, tips, and magic from inside Disney parks — from our family to yours.</p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            @if($episodes->count())
                <div class="space-y-6">
                    @foreach($episodes as $episode)
                        <a href="/episodes/{{ $episode->slug }}" class="group block bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all hover:-translate-y-0.5 border border-navy/5">
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                                <div class="flex-shrink-0">
                                    <span class="inline-flex items-center justify-center w-14 h-14 bg-purple/10 text-purple font-heading font-bold text-lg rounded-xl">{{ $episode->episode_number }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="text-navy/40 text-xs">{{ $episode->published_at->format('M j, Y') }}</span>
                                        @if($episode->duration_seconds)
                                            <span class="text-navy/30 text-xs">•</span>
                                            <span class="text-navy/40 text-xs">{{ $episode->formatted_duration }}</span>
                                        @endif
                                    </div>
                                    <h2 class="font-heading text-xl font-semibold text-navy group-hover:text-purple transition-colors mb-2">{{ $episode->title }}</h2>
                                    <p class="text-navy/60 text-sm leading-relaxed line-clamp-2">{{ Str::limit($episode->description, 200) }}</p>
                                    <div class="flex items-center gap-4 mt-4">
                                        @if($episode->apple_url)
                                            <span class="text-xs text-navy/40 hover:text-purple transition-colors">🎧 Apple</span>
                                        @endif
                                        @if($episode->spotify_url)
                                            <span class="text-xs text-navy/40 hover:text-purple transition-colors">💚 Spotify</span>
                                        @endif
                                        <span class="text-purple text-sm font-medium flex items-center gap-1 ml-auto">
                                            Show notes
                                            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $episodes->links() }}
                </div>
            @else
                <div class="text-center py-16 bg-white rounded-2xl border border-navy/5">
                    <span class="text-5xl block mb-4">🎙️</span>
                    <h2 class="font-heading text-2xl font-bold text-navy mb-2">Coming Soon!</h2>
                    <p class="text-navy/50">Our first episode is in the works. Subscribe to be the first to know.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
