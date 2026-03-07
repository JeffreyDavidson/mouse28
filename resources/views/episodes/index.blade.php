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
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col lg:flex-row gap-10">
                <div class="lg:w-[66%]">
            @if($episodes->count())
                {{-- Group by season if available --}}
                @php
                    $grouped = $episodes->getCollection()->groupBy(fn($ep) => $ep->season_number ?? 0);
                    $allEpisodes = $episodes->getCollection();
                    $totalSeconds = $allEpisodes->sum('duration_seconds');
                    $totalHours = round($totalSeconds / 3600, 1);
                    $latestEpisode = $allEpisodes->first();
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
                </div>{{-- end lg:w-[66%] --}}

                <aside class="lg:w-[34%]">
                    <div class="lg:sticky lg:top-[90px] space-y-6">
                        {{-- Podcast Stats --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center justify-around">
                                <div class="text-center">
                                    <span class="block text-2xl font-bold text-navy font-heading">{{ $episodes->total() }}</span>
                                    <span class="text-navy/35 text-xs uppercase tracking-wider">{{ Str::plural('Episode', $episodes->total()) }}</span>
                                </div>
                                <div class="w-px h-10 bg-navy/8"></div>
                                <div class="text-center">
                                    <span class="block text-2xl font-bold text-purple font-heading">{{ $totalHours }}h</span>
                                    <span class="text-navy/35 text-xs uppercase tracking-wider">Listening</span>
                                </div>
                            </div>
                        </div>

                        {{-- Listen On --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-4">
                                <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Listen On</h3>
                            </div>
                            <div class="space-y-3">
                                <a href="#" target="_blank" rel="noopener" class="flex items-center gap-3 w-full bg-navy/5 hover:bg-navy text-navy/60 hover:text-white font-semibold text-sm px-4 py-3 rounded-xl transition-all">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.1 22C7.79 22.05 6.8 20.68 5.96 19.47C4.25 16.56 2.93 11.3 4.7 7.72C5.57 5.94 7.36 4.86 9.28 4.84C10.56 4.81 11.78 5.7 12.56 5.7C13.34 5.7 14.85 4.62 16.41 4.8C17.07 4.83 18.96 5.06 20.16 6.87C20.05 6.95 17.58 8.37 17.61 11.34C17.65 14.9 20.68 16.04 20.71 16.06C20.69 16.13 20.18 17.86 18.71 19.5ZM13 3.5C13.73 2.67 14.94 2.04 15.94 2C16.07 3.17 15.6 4.35 14.9 5.19C14.21 6.04 13.07 6.7 11.95 6.61C11.8 5.46 12.36 4.26 13 3.5Z"/></svg>
                                    Apple Podcasts
                                </a>
                                <a href="#" target="_blank" rel="noopener" class="flex items-center gap-3 w-full bg-navy/5 hover:bg-navy text-navy/60 hover:text-white font-semibold text-sm px-4 py-3 rounded-xl transition-all">
                                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                    Spotify
                                </a>
                            </div>
                        </div>

                        {{-- Latest Episode --}}
                        @if($latestEpisode)
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-4">
                                <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Latest Episode</h3>
                            </div>
                            <a href="/episodes/{{ $latestEpisode->slug }}" class="group block">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple to-navy rounded-lg flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-xs font-bold">{{ $latestEpisode->episode_number }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="font-heading text-sm font-bold text-navy group-hover:text-purple transition-colors line-clamp-2">{{ $latestEpisode->title }}</h4>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            @if($latestEpisode->duration_seconds)
                                                <span class="text-navy/40 text-xs">{{ $latestEpisode->formatted_duration }}</span>
                                            @endif
                                            @if($latestEpisode->season_number)
                                                <span class="text-navy/20">•</span>
                                                <span class="text-navy/40 text-xs">S{{ $latestEpisode->season_number }}E{{ $latestEpisode->episode_number }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <span class="text-purple text-sm font-semibold inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                                    Listen →
                                </span>
                            </a>
                        </div>
                        @endif

                        {{-- Blog CTA --}}
                        <div class="rounded-2xl p-6 text-center" style="background: linear-gradient(135deg, #1a1040, #2d1b69);">
                            <div class="text-3xl mb-3">📝</div>
                            <h3 class="font-heading text-lg font-bold text-cream mb-2">Read the Blog</h3>
                            <p class="text-cream/50 text-sm mb-4">Disney tips, park guides, and family stories</p>
                            <a href="/blog" class="inline-block bg-gradient-to-r from-gold to-gold-light text-navy font-bold text-sm px-6 py-2.5 rounded-xl transition-all hover:-translate-y-0.5 shadow-md shadow-gold/15">
                                Visit Blog ✨
                            </a>
                        </div>

                        {{-- Newsletter --}}
                        <div class="rounded-2xl overflow-hidden shadow-sm border border-gold/15">
                            <div class="bg-gradient-to-r from-gold/15 via-gold/8 to-purple/8 px-6 pt-5 pb-4 text-center relative">
                                <div class="absolute top-3 right-4 text-gold/15 text-xs">✦</div>
                                <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-white/80 flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </div>
                                <h3 class="font-heading text-base font-bold text-navy">Stay in the Loop</h3>
                                <p class="text-navy/45 text-xs mt-1">Disney tips & new episodes</p>
                            </div>
                            <div class="bg-white px-6 py-5">
                                <form action="/newsletter" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="email" name="email" placeholder="your@email.com" required
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-navy/10 focus:border-gold focus:ring-2 focus:ring-gold/20 outline-none transition-all placeholder:text-navy/25 text-navy">
                                    <button type="submit" class="w-full bg-gradient-to-r from-gold to-gold-light text-navy font-bold text-sm py-2.5 rounded-xl transition-all hover:-translate-y-0.5 shadow-md shadow-gold/15">
                                        Subscribe ✨
                                    </button>
                                </form>
                                <p class="text-navy/25 text-[10px] text-center mt-3">No spam. Unsubscribe anytime.</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>{{-- end flex --}}
        </div>
    </section>
            @else
    <section class="py-16 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <style>
                    @keyframes waveformPulse {
                        0%, 100% { transform: scaleY(1); }
                        50% { transform: scaleY(0.6); }
                    }
                    .waveform-bar { animation: waveformPulse 1.5s ease-in-out infinite; transform-origin: bottom; }
                </style>
                <div class="relative overflow-hidden rounded-3xl" style="background: linear-gradient(135deg, #1a1040 0%, #2d1b69 50%, #3a2370 100%); padding: 5rem 3rem;">
                    {{-- Ambient glows --}}
                    <div style="position: absolute; top: -30%; right: -5%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(212, 168, 67, 0.06) 0%, transparent 60%); pointer-events: none;"></div>
                    <div style="position: absolute; bottom: -20%; left: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(91, 62, 158, 0.2) 0%, transparent 60%); pointer-events: none;"></div>

                    {{-- Background waveform decoration --}}
                    <div style="position: absolute; bottom: 0; left: 0; right: 0; display: flex; align-items: flex-end; gap: 3px; height: 80px; opacity: 0.04; padding: 0 2rem; pointer-events: none;">
                        @for($i = 0; $i < 60; $i++)
                            <div style="flex: 1; background: white; border-radius: 99px; height: {{ rand(15, 100) }}%;"></div>
                        @endfor
                    </div>

                    <div class="max-w-3xl mx-auto relative z-10">
                        <div class="grid md:grid-cols-2 gap-12 items-center">
                            {{-- Left: Content --}}
                            <div class="text-center md:text-left">
                                <div style="display: inline-flex; align-items: center; gap: 0.5rem; border: 1px solid rgba(212, 168, 67, 0.3); border-radius: 9999px; padding: 0.35rem 1rem; margin-bottom: 1.5rem;">
                                    <span style="width: 6px; height: 6px; border-radius: 50%; background: #d4a843;"></span>
                                    <span style="font-family: 'Poppins', sans-serif; font-size: 0.7rem; color: #d4a843; letter-spacing: 0.15em; text-transform: uppercase; font-weight: 600;">Coming Soon</span>
                                </div>
                                <h2 class="font-heading font-bold text-cream" style="font-size: clamp(1.75rem, 3.5vw, 2.5rem); line-height: 1.15; margin-bottom: 1rem;">We're warming up the mics</h2>
                                <p style="color: rgba(254, 249, 239, 0.5); font-size: 0.95rem; line-height: 1.8; margin-bottom: 2rem;">
                                    Our first episode is in the works. Disney parks, accessibility, family stories, and a lot of heart. Subscribe so you're there from the very start.
                                </p>
                                <div class="flex items-center justify-center md:justify-start gap-3">
                                    <a href="#" class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-lg transition-all hover:-translate-y-0.5" style="background: rgba(254, 249, 239, 0.08); color: rgba(254, 249, 239, 0.7); border: 1px solid rgba(254, 249, 239, 0.1);">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.1 22C7.79 22.05 6.8 20.68 5.96 19.47C4.25 16.56 2.93 11.3 4.7 7.72C5.57 5.94 7.36 4.86 9.28 4.84C10.56 4.81 11.78 5.7 12.56 5.7C13.34 5.7 14.85 4.62 16.41 4.8C17.07 4.83 18.96 5.06 20.16 6.87C20.05 6.95 17.58 8.37 17.61 11.34C17.65 14.9 20.68 16.04 20.71 16.06C20.69 16.13 20.18 17.86 18.71 19.5Z"/></svg>
                                        Apple Podcasts
                                    </a>
                                    <a href="#" class="inline-flex items-center gap-2 text-xs font-semibold px-4 py-2 rounded-lg transition-all hover:-translate-y-0.5" style="background: rgba(254, 249, 239, 0.08); color: rgba(254, 249, 239, 0.7); border: 1px solid rgba(254, 249, 239, 0.1);">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02z"/></svg>
                                        Spotify
                                    </a>
                                </div>
                            </div>

                            {{-- Right: Faux player --}}
                            <div class="hidden md:block">
                                <div style="background: rgba(26, 16, 64, 0.6); border: 1px solid rgba(212, 168, 67, 0.12); border-radius: 1.5rem; padding: 2rem; backdrop-filter: blur(10px); box-shadow: 0 25px 50px rgba(0,0,0,0.3);">
                                    {{-- Episode header --}}
                                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem;">
                                        <div style="width: 56px; height: 56px; border-radius: 0.75rem; background: linear-gradient(135deg, #5b3e9e, #3a2370); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(91, 62, 158, 0.4);">
                                            <svg style="width: 22px; height: 22px; color: #d4a843;" fill="currentColor" viewBox="0 0 24 24"><path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                        </div>
                                        <div style="flex: 1;">
                                            <div style="height: 9px; width: 45%; background: rgba(212, 168, 67, 0.3); border-radius: 99px; margin-bottom: 8px;"></div>
                                            <div style="height: 12px; width: 85%; background: rgba(254, 249, 239, 0.15); border-radius: 99px; margin-bottom: 5px;"></div>
                                            <div style="height: 12px; width: 60%; background: rgba(254, 249, 239, 0.1); border-radius: 99px;"></div>
                                        </div>
                                    </div>

                                    {{-- Waveform bars with animation --}}
                                    <div style="display: flex; align-items: end; gap: 2px; height: 48px; margin-bottom: 0.75rem;">
                                        @for($i = 0; $i < 32; $i++)
                                            @php $h = rand(20, 100); $played = $i < 12; @endphp
                                            <div class="waveform-bar" style="flex: 1; background: {{ $played ? 'rgba(212, 168, 67, 0.5)' : 'rgba(254, 249, 239, 0.1)' }}; border-radius: 99px; height: {{ $h }}%; animation-delay: {{ $i * 0.08 }}s;"></div>
                                        @endfor
                                    </div>

                                    {{-- Progress bar --}}
                                    <div style="height: 3px; background: rgba(254, 249, 239, 0.06); border-radius: 99px; margin-bottom: 0.5rem; position: relative;">
                                        <div style="position: absolute; left: 0; top: 0; width: 38%; height: 100%; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 99px;"></div>
                                        <div style="position: absolute; left: 38%; top: 50%; transform: translate(-50%, -50%); width: 9px; height: 9px; background: #f0c75e; border-radius: 50%; box-shadow: 0 0 8px rgba(212, 168, 67, 0.5);"></div>
                                    </div>
                                    <div style="display: flex; justify-content: space-between;">
                                        <span style="font-size: 0.65rem; color: rgba(254, 249, 239, 0.25); font-family: 'Poppins', sans-serif;">12:34</span>
                                        <span style="font-size: 0.65rem; color: rgba(254, 249, 239, 0.25); font-family: 'Poppins', sans-serif;">32:18</span>
                                    </div>

                                    {{-- Playback controls --}}
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 1.5rem; margin-top: 1rem;">
                                        <div style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                            <svg style="width: 16px; height: 16px; color: rgba(254, 249, 239, 0.25);" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg>
                                        </div>
                                        <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #d4a843, #b8922e); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(212, 168, 67, 0.3);">
                                            <svg style="width: 20px; height: 20px; color: #1a1040; margin-left: 2px;" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                        <div style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                            <svg style="width: 16px; height: 16px; color: rgba(254, 249, 239, 0.25);" fill="currentColor" viewBox="0 0 24 24"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
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
