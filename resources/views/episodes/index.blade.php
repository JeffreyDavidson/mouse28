@extends('layouts.app')

@section('title', 'Episodes — Mouse28')

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
    </style>
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-24 relative overflow-hidden">
        {{-- Waveform SVG decoration --}}
        <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
            <svg class="absolute bottom-0 left-0 w-full h-32" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor" class="text-white">
                <g stroke="white" stroke-width="2">
                    @for($i = 0; $i < 60; $i++)
                        <line x1="{{ $i * 20 + 10 }}" y1="{{ 60 - rand(5, 50) }}" x2="{{ $i * 20 + 10 }}" y2="{{ 60 + rand(5, 50) }}" stroke-linecap="round"/>
                    @endfor
                </g>
            </svg>
        </div>
        <div class="absolute top-8 right-8 opacity-10">
            <svg width="120" height="120" viewBox="0 0 120 120" fill="none"><circle cx="60" cy="60" r="50" stroke="white" stroke-width="2"/><polygon points="50,35 50,85 90,60" fill="white"/></svg>
        </div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 mb-6">
                <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                <span class="text-gold text-sm font-semibold tracking-widest uppercase">Podcast</span>
            </div>
            <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white mt-2">Listen Along</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto text-lg">Every week we bring you stories, tips, and magic from inside Disney parks, from our family to yours.</p>
            {{-- Platform subscribe badges --}}
            <div class="flex items-center justify-center gap-3 mt-8">
                <a href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white/70 hover:text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-all hover:-translate-y-0.5 backdrop-blur-sm border border-white/10">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.1 22C7.79 22.05 6.8 20.68 5.96 19.47C4.25 16.56 2.93 11.3 4.7 7.72C5.57 5.94 7.36 4.86 9.28 4.84C10.56 4.81 11.78 5.7 12.56 5.7C13.34 5.7 14.85 4.62 16.41 4.8C17.07 4.83 18.96 5.06 20.16 6.87C20.05 6.95 17.58 8.37 17.61 11.34C17.65 14.9 20.68 16.04 20.71 16.06C20.69 16.13 20.18 17.86 18.71 19.5ZM13 3.5C13.73 2.67 14.94 2.04 15.94 2C16.07 3.17 15.6 4.35 14.9 5.19C14.21 6.04 13.07 6.7 11.95 6.61C11.8 5.46 12.36 4.26 13 3.5Z"/></svg>
                    Apple Podcasts
                </a>
                <a href="#" target="_blank" rel="noopener" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white/70 hover:text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-all hover:-translate-y-0.5 backdrop-blur-sm border border-white/10">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                    Spotify
                </a>
            </div>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            @if($episodes->count())
                {{-- Group by season if available --}}
                @php
                    $grouped = $episodes->getCollection()->groupBy(fn($ep) => $ep->season_number ?? 0);
                @endphp

                @foreach($grouped as $season => $seasonEpisodes)
                    @if($season > 0)
                        <div class="{{ !$loop->first ? 'mt-14' : '' }} mb-8">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-3 bg-white rounded-full px-5 py-2 shadow-sm border border-navy/5">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple to-navy rounded-full flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">S{{ $season }}</span>
                                    </div>
                                    <span class="font-heading text-lg font-bold text-navy">Season {{ $season }}</span>
                                    <span class="text-navy/30 text-sm">{{ $seasonEpisodes->count() }} {{ Str::plural('episode', $seasonEpisodes->count()) }}</span>
                                </div>
                                <div class="flex-1 h-px bg-navy/10"></div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-5 {{ $season == 0 && !$loop->first ? 'mt-14' : '' }}">
                        @foreach($seasonEpisodes as $episode)
                            <a href="/episodes/{{ $episode->slug }}" class="group block bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-navy/5 hover:border-purple/20 overflow-hidden">
                                <div class="flex flex-col sm:flex-row">
                                    {{-- Episode number with play overlay --}}
                                    <div class="relative flex-shrink-0 sm:w-28 h-20 sm:h-auto bg-gradient-to-br from-purple to-navy flex items-center justify-center">
                                        <span class="font-heading text-3xl font-bold text-white/20">{{ $episode->episode_number }}</span>
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 bg-purple/80">
                                            <svg class="w-10 h-10 text-white drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                        {{-- Mini waveform decoration --}}
                                        <div class="absolute bottom-1.5 left-1/2 -translate-x-1/2 flex items-end gap-[2px] h-3 opacity-20 group-hover:opacity-40 transition-opacity">
                                            <div class="w-[2px] bg-white rounded-full h-1"></div>
                                            <div class="w-[2px] bg-white rounded-full h-2.5"></div>
                                            <div class="w-[2px] bg-white rounded-full h-1.5"></div>
                                            <div class="w-[2px] bg-white rounded-full h-3"></div>
                                            <div class="w-[2px] bg-white rounded-full h-1.5"></div>
                                        </div>
                                    </div>

                                    <div class="flex-1 min-w-0 p-6">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-navy/40 text-xs font-medium">{{ $episode->published_at->format('M j, Y') }}</span>
                                            @if($episode->duration_seconds)
                                                <span class="text-navy/20">•</span>
                                                <span class="text-navy/40 text-xs flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $episode->formatted_duration }}
                                                </span>
                                            @endif
                                            @if($episode->season_number)
                                                <span class="text-navy/20">•</span>
                                                <span class="text-navy/40 text-xs">S{{ $episode->season_number }}E{{ $episode->episode_number }}</span>
                                            @endif
                                        </div>
                                        <h2 class="font-heading text-xl font-semibold text-navy group-hover:text-purple transition-colors mb-2">{{ $episode->title }}</h2>
                                        <p class="text-navy/60 text-sm leading-relaxed line-clamp-2">{{ Str::limit($episode->description, 200) }}</p>

                                        <div class="flex items-center gap-4 mt-4">
                                            @if($episode->apple_url)
                                                <span class="inline-flex items-center gap-1.5 text-xs text-navy/40 bg-navy/5 px-2.5 py-1 rounded-full">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.1 22C7.79 22.05 6.8 20.68 5.96 19.47C4.25 16.56 2.93 11.3 4.7 7.72C5.57 5.94 7.36 4.86 9.28 4.84C10.56 4.81 11.78 5.7 12.56 5.7C13.34 5.7 14.85 4.62 16.41 4.8C17.07 4.83 18.96 5.06 20.16 6.87C20.05 6.95 17.58 8.37 17.61 11.34C17.65 14.9 20.68 16.04 20.71 16.06C20.69 16.13 20.18 17.86 18.71 19.5Z"/></svg>
                                                    Apple
                                                </span>
                                            @endif
                                            @if($episode->spotify_url)
                                                <span class="inline-flex items-center gap-1.5 text-xs text-navy/40 bg-navy/5 px-2.5 py-1 rounded-full">
                                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2z"/></svg>
                                                    Spotify
                                                </span>
                                            @endif
                                            <span class="text-purple text-sm font-medium flex items-center gap-1 ml-auto group-hover:gap-2 transition-all">
                                                Listen now
                                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endforeach

                <div class="mt-12 pagination flex justify-center">
                    {{ $episodes->links() }}
                </div>
            @else
                <div class="text-center py-20 bg-white rounded-3xl border border-navy/5 relative overflow-hidden">
                    {{-- Decorative waveform --}}
                    <div class="absolute inset-x-0 bottom-0 flex items-end justify-center gap-1 h-16 opacity-10 px-8">
                        @for($i = 0; $i < 40; $i++)
                            <div class="w-1 bg-purple rounded-full" style="height: {{ rand(8, 60) }}%"></div>
                        @endfor
                    </div>
                    <div class="relative z-10">
                        <div class="w-20 h-20 bg-gradient-to-br from-purple to-navy rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                        </div>
                        <h2 class="font-heading text-3xl font-bold text-navy mb-3">Tuning Up! 🎵</h2>
                        <p class="text-navy/50 max-w-md mx-auto text-lg">Our first episode is almost ready. Subscribe so you don't miss the premiere!</p>
                        <div class="flex items-center justify-center gap-3 mt-8">
                            <a href="#" class="inline-flex items-center gap-2 bg-navy text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-purple transition-colors">
                                🎧 Subscribe on Apple
                            </a>
                            <a href="#" class="inline-flex items-center gap-2 bg-[#1DB954] text-white text-sm font-semibold px-5 py-2.5 rounded-full hover:bg-[#1ed760] transition-colors">
                                💚 Follow on Spotify
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection
