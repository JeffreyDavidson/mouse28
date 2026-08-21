@extends('layouts.app')

@section('title', ($episode->meta_title ?: $episode->title) . ' — Mouse28')
@section('meta_description', $episode->meta_description ?: Str::limit($episode->description, 160))
@section('og_title', $episode->meta_title ?: $episode->title)
@section('og_description', $episode->meta_description ?: Str::limit($episode->description, 200))
@if($episode->og_image_url) @section('og_image', $episode->og_image_url) @endif

@section('content')
    <section class="relative overflow-hidden bg-linear-to-br from-navy to-navy-light py-16 md:py-24">
        {{-- Waveform background --}}
        <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
            <svg class="absolute bottom-0 left-0 h-32 w-full text-white" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor">
                <g stroke="white" stroke-width="2">
                    @for($i = 0; $i < 60; $i++)
                        <line x1="{{ $i * 20 + 10 }}" y1="{{ 60 - rand(5, 50) }}" x2="{{ $i * 20 + 10 }}" y2="{{ 60 + rand(5, 50) }}" stroke-linecap="round"/>
                    @endfor
                </g>
            </svg>
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10">
            <a href="/episodes" class="mb-6 inline-flex min-h-11 items-center gap-1 text-base text-white/50 transition-colors hover:text-gold sm:text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                All Episodes
            </a>

            <div class="inline-flex items-center gap-2 border border-gold/30 rounded-full px-4 py-1.5 mt-4 mb-5">
                <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                <span class="text-gold text-xs font-semibold tracking-[0.15em] uppercase font-body">Episode {{ $episode->episode_number }}</span>
            </div>

            <div class="flex items-center gap-3 mb-4">
                <span class="bg-gold/20 text-gold text-sm font-bold px-4 py-1 rounded-full backdrop-blur-sm">Episode {{ $episode->episode_number }}</span>
                @if($episode->season_number)
                    <span class="bg-white/10 text-white/70 text-sm px-3 py-1 rounded-full">Season {{ $episode->season_number }}</span>
                @endif
                @if($episode->duration_seconds)
                    <span class="text-white/40 text-sm flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $episode->formatted_duration }}
                    </span>
                @endif
                <span class="text-white/40 text-sm">{{ $episode->published_at->format('F j, Y') }}</span>
            </div>
            <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white">{{ $episode->title }}</h1>
            @if($episode->description)
                <p class="text-white/60 text-lg mt-4 leading-relaxed max-w-3xl">{{ $episode->description }}</p>
            @endif
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col lg:flex-row gap-10">
                {{-- Main Content --}}
                <div class="lg:w-[66%]">
                    {{-- Custom Audio Player Card --}}
                    @if($episode->audio_url)
                        <div class="relative mb-10 overflow-hidden rounded-2xl bg-linear-to-r from-navy to-navy-light p-6 shadow-xl md:p-8">
                            {{-- Decorative waveform behind player --}}
                            <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
                                <svg class="absolute bottom-0 left-0 h-32 w-full text-white" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor">
                                    <g stroke="white" stroke-width="2">
                                        @for($i = 0; $i < 60; $i++)
                                            <line x1="{{ $i * 20 + 10 }}" y1="{{ 60 - rand(5, 50) }}" x2="{{ $i * 20 + 10 }}" y2="{{ 60 + rand(5, 50) }}" stroke-linecap="round"/>
                                        @endfor
                                    </g>
                                </svg>
                            </div>

                            <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
                                {{-- Episode artwork/number --}}
                                <div class="flex size-24 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-purple to-gold shadow-lg">
                                    <div class="text-center">
                                        <span class="text-white/60 text-[10px] uppercase tracking-wider font-bold block">EP</span>
                                        <span class="text-white text-3xl font-heading font-bold">{{ $episode->episode_number }}</span>
                                    </div>
                                </div>

                                <div class="flex-1 w-full">
                                    <p class="text-white/50 text-xs uppercase tracking-wider mb-1">Now Playing</p>
                                    <p class="text-white font-heading font-semibold text-lg mb-3">{{ $episode->title }}</p>
                                    <audio controls class="w-full [&::-webkit-media-controls-panel]:bg-white/10 [&::-webkit-media-controls-panel]:rounded-xl" preload="metadata">
                                        <source src="{{ $episode->audio_url }}" type="audio/mpeg">
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Show Notes --}}
                    @if($episode->show_notes)
                        <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm border border-navy/5 mb-8">
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-10 h-10 bg-purple/10 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h2 class="font-heading text-2xl font-bold text-navy">Show Notes</h2>
                            </div>
                            <div class="episode-show-notes-content">
                                {!! $episode->show_notes !!}
                            </div>
                        </div>
                    @endif

                    {{-- Transcript --}}
                    <div class="bg-white rounded-2xl p-8 md:p-10 shadow-sm border border-navy/5 mb-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div class="w-10 h-10 bg-gold/10 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                            </div>
                            <h2 class="font-heading text-2xl font-bold text-navy">Transcript</h2>
                        </div>
                        @if($episode->transcript)
                            <div x-data="{ expanded: false }">
                                <div class="episode-transcript-content max-h-[600px]" :class="{ 'max-h-none': expanded }">
                                    {!! $episode->transcript !!}
                                </div>
                                <div class="relative" x-show="!expanded" x-cloak>
                                    <div class="pointer-events-none absolute right-0 bottom-full left-0 h-20 bg-linear-to-t from-white to-transparent"></div>
                                </div>
                                <button type="button" @click="expanded = !expanded" class="mt-4 w-full rounded-xl border border-navy/10 py-3 text-center text-sm font-semibold text-gold transition-all hover:border-gold hover:bg-gold/5">
                                    <span x-text="expanded ? 'Collapse Transcript' : 'Read Full Transcript'" class="inline-flex items-center gap-1.5">Read Full Transcript</span>
                                    <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 inline-block ml-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                            </div>
                        @else
                            <p class="text-navy/40 italic">Transcript coming soon. We're working on making all episodes accessible with full text transcripts.</p>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[34%]">
                    <div class="lg:sticky lg:top-[90px] space-y-6">
                        {{-- Episode Info --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Episode Info</h3>
                            </div>
                            {{-- Episode number highlight --}}
                            <div class="flex items-center gap-4 mb-5 pb-5 border-b border-navy/8">
                                <div class="flex size-14 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-purple to-navy shadow-md">
                                    <div class="text-center leading-none">
                                        <span class="text-white/50 text-[8px] uppercase font-bold block">EP</span>
                                        <span class="text-white text-xl font-heading font-bold block -mt-0.5">{{ $episode->episode_number }}</span>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <span class="text-navy font-heading font-bold text-sm block truncate">{{ $episode->title }}</span>
                                    <span class="text-navy/40 text-xs">{{ $episode->published_at->format('F j, Y') }}</span>
                                </div>
                            </div>
                            <dl class="space-y-0 text-sm">
                                @if($episode->season_number)
                                    <div class="flex justify-between py-3 border-b border-navy/5">
                                        <dt class="text-navy/45 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                            Season
                                        </dt>
                                        <dd class="font-semibold text-navy">{{ $episode->season_number }}</dd>
                                    </div>
                                @endif
                                @if($episode->duration_seconds)
                                    <div class="flex justify-between py-3 border-b border-navy/5">
                                        <dt class="text-navy/45 flex items-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            Duration
                                        </dt>
                                        <dd class="font-semibold text-navy">{{ $episode->formatted_duration }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between py-3">
                                    <dt class="text-navy/45 flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Published
                                    </dt>
                                    <dd class="font-semibold text-navy">{{ $episode->published_at->format('M j, Y') }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Listen On (always show — links to general podcast feeds if no episode-specific URLs) --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Listen On</h3>
                            </div>
                            <div class="space-y-3">
                                <a @if($episode->apple_url) href="{{ $episode->apple_url }}" target="_blank" rel="noopener" @else aria-disabled="true" @endif class="flex w-full items-center gap-3 rounded-xl border border-navy/8 px-4 py-3.5 transition-all {{ $episode->apple_url ? 'group hover:border-navy/20 hover:bg-navy/[0.03]' : 'opacity-70' }}">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#fc3c44] to-[#d42d56] shadow-sm">
                                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.1 22C7.79 22.05 6.8 20.68 5.96 19.47C4.25 16.56 2.93 11.3 4.7 7.72C5.57 5.94 7.36 4.86 9.28 4.84C10.56 4.81 11.78 5.7 12.56 5.7C13.34 5.7 14.85 4.62 16.41 4.8C17.07 4.83 18.96 5.06 20.16 6.87C20.05 6.95 17.58 8.37 17.61 11.34C17.65 14.9 20.68 16.04 20.71 16.06C20.69 16.13 20.18 17.86 18.71 19.5ZM13 3.5C13.73 2.67 14.94 2.04 15.94 2C16.07 3.17 15.6 4.35 14.9 5.19C14.21 6.04 13.07 6.7 11.95 6.61C11.8 5.46 12.36 4.26 13 3.5Z"/></svg>
                                    </div>
                                    <div>
                                        <span class="block text-base font-semibold text-navy transition-colors group-hover:text-purple sm:text-sm">Apple Podcasts</span>
                                        <span class="text-sm text-navy/35 sm:text-xs">{{ $episode->apple_url ? 'Listen to this episode' : 'Coming soon' }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-navy/20 ml-auto group-hover:text-purple/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a @if($episode->spotify_url) href="{{ $episode->spotify_url }}" target="_blank" rel="noopener" @else aria-disabled="true" @endif class="flex w-full items-center gap-3 rounded-xl border border-navy/8 px-4 py-3.5 transition-all {{ $episode->spotify_url ? 'group hover:border-navy/20 hover:bg-navy/[0.03]' : 'opacity-70' }}">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#1DB954] to-[#169c46] shadow-sm">
                                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg>
                                    </div>
                                    <div>
                                        <span class="block text-base font-semibold text-navy transition-colors group-hover:text-purple sm:text-sm">Spotify</span>
                                        <span class="text-sm text-navy/35 sm:text-xs">{{ $episode->spotify_url ? 'Listen to this episode' : 'Coming soon' }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-navy/20 ml-auto group-hover:text-purple/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                @if($episode->youtube_url)
                                    <a href="{{ $episode->youtube_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 w-full px-4 py-3.5 rounded-xl border border-navy/8 hover:border-navy/20 hover:bg-navy/[0.03] transition-all group">
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#FF0000] to-[#cc0000] shadow-sm">
                                            <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                        </div>
                                        <div>
                                            <span class="text-navy font-semibold text-sm block group-hover:text-purple transition-colors">YouTube</span>
                                            <span class="text-navy/35 text-xs">Watch this episode</span>
                                        </div>
                                        <svg class="w-4 h-4 text-navy/20 ml-auto group-hover:text-purple/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- Share --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Share</h3>
                            </div>
                            <div class="space-y-3">
                                <a href="https://twitter.com/intent/tweet?text={{ urlencode($episode->title . ' — Mouse28 Podcast') }}&url={{ urlencode(url('/episodes/' . $episode->slug)) }}" target="_blank" rel="noopener"
                                   class="flex items-center gap-3 w-full px-4 py-3.5 rounded-xl border border-navy/8 hover:border-navy/20 hover:bg-navy/[0.03] transition-all group">
                                    <div class="w-9 h-9 bg-black rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    </div>
                                    <span class="text-navy font-semibold text-sm group-hover:text-purple transition-colors">Post on X</span>
                                    <svg class="w-4 h-4 text-navy/20 ml-auto group-hover:text-purple/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/episodes/' . $episode->slug)) }}" target="_blank" rel="noopener"
                                   class="flex items-center gap-3 w-full px-4 py-3.5 rounded-xl border border-navy/8 hover:border-navy/20 hover:bg-navy/[0.03] transition-all group">
                                    <div class="w-9 h-9 bg-[#1877F2] rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                                        <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    </div>
                                    <span class="text-navy font-semibold text-sm group-hover:text-purple transition-colors">Share on Facebook</span>
                                    <svg class="w-4 h-4 text-navy/20 ml-auto group-hover:text-purple/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                                <button type="button" data-copy-link class="group flex w-full items-center gap-3 rounded-xl border border-navy/8 px-4 py-3.5 text-left transition-all hover:border-navy/20 hover:bg-navy/[0.03]">
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-purple to-navy shadow-sm">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    </div>
                                    <span data-copy-label class="text-sm font-semibold text-navy transition-colors group-hover:text-purple">Copy Link</span>
                                    <span data-copy-feedback class="hidden text-sm font-semibold text-purple" aria-live="polite">Copied!</span>
                                    <svg class="w-4 h-4 text-navy/20 ml-auto group-hover:text-purple/50 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Related Posts --}}
                        @if($relatedPosts->count())
                            <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                    <h3 class="font-heading text-lg font-bold text-navy">Related Posts</h3>
                                </div>
                                <div class="space-y-4">
                                    @foreach($relatedPosts as $post)
                                        <a href="/blog/{{ $post->slug }}" class="group flex items-start gap-3">
                                            @if($post->cover_image_url)
                                                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                                            @else
                                                <div class="flex size-16 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-purple/10 to-gold/10">
                                                    <svg class="w-6 h-6 text-purple/40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-heading text-sm font-semibold text-navy group-hover:text-purple transition-colors line-clamp-2">{{ $post->title }}</h4>
                                                <span class="text-navy/35 text-xs mt-1 block">{{ $post->published_at->format('M j, Y') }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Browse Episodes CTA --}}
                        <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-linear-to-br from-navy via-navy-light to-navy p-7 text-center">
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-gold/5 rounded-full blur-3xl"></div>
                            <div class="relative">
                                <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-white/10 flex items-center justify-center border border-white/10">
                                    <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                </div>
                                <h3 class="font-heading text-lg font-bold text-white mb-2">More Episodes</h3>
                                <p class="mb-5 text-base/relaxed text-white/40 sm:text-sm/relaxed">Browse all episodes from Mouse28</p>
                                <a href="/episodes" class="inline-block rounded-full bg-linear-to-r from-gold to-gold-light px-7 py-3 text-sm font-bold text-navy shadow-lg shadow-gold/20 transition-all hover:-translate-y-0.5">
                                    All Episodes
                                </a>
                            </div>
                        </div>

                        {{-- Newsletter --}}
                        <x-newsletter-card subtitle="New episodes & Disney tips delivered to your inbox" />
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
