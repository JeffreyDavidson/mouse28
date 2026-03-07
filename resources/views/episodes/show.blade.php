@extends('layouts.app')

@section('title', $episode->title . ' — Mouse28')
@section('meta_description', Str::limit($episode->description, 160))

@section('content')
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20 relative overflow-hidden">
        {{-- Waveform background --}}
        <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
            <svg class="absolute bottom-0 left-0 w-full h-32" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor" class="text-white">
                <g stroke="white" stroke-width="2">
                    @for($i = 0; $i < 60; $i++)
                        <line x1="{{ $i * 20 + 10 }}" y1="{{ 60 - rand(5, 50) }}" x2="{{ $i * 20 + 10 }}" y2="{{ 60 + rand(5, 50) }}" stroke-linecap="round"/>
                    @endfor
                </g>
            </svg>
        </div>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 relative z-10">
            <a href="/episodes" class="text-white/50 hover:text-gold text-sm transition-colors mb-6 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                All Episodes
            </a>
            <div class="flex items-center gap-3 mb-4 mt-4">
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
            <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white">{{ $episode->title }}</h1>
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
                        <div class="bg-gradient-to-r from-navy to-navy-light rounded-2xl p-6 md:p-8 shadow-xl mb-10 relative overflow-hidden">
                            {{-- Decorative waveform behind player --}}
                            <div class="absolute inset-0 opacity-[0.07] pointer-events-none">
                                <svg class="absolute bottom-0 left-0 w-full h-32" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor" class="text-white">
                                    <g stroke="white" stroke-width="2">
                                        @for($i = 0; $i < 60; $i++)
                                            <line x1="{{ $i * 20 + 10 }}" y1="{{ 60 - rand(5, 50) }}" x2="{{ $i * 20 + 10 }}" y2="{{ 60 + rand(5, 50) }}" stroke-linecap="round"/>
                                        @endfor
                                    </g>
                                </svg>
                            </div>

                            <div class="relative z-10 flex flex-col sm:flex-row items-center gap-6">
                                {{-- Episode artwork/number --}}
                                <div class="flex-shrink-0 w-24 h-24 bg-gradient-to-br from-purple to-gold rounded-2xl flex items-center justify-center shadow-lg">
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
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-10 h-10 bg-purple/10 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <h2 class="font-heading text-2xl font-bold text-navy">Show Notes</h2>
                            </div>
                            <div class="prose prose-lg prose-navy max-w-none text-navy/80 leading-relaxed prose-headings:font-heading prose-headings:text-navy prose-a:text-purple prose-a:no-underline hover:prose-a:underline prose-li:marker:text-purple prose-strong:text-navy">
                                {!! $episode->show_notes !!}
                            </div>
                        </div>
                    @endif

                    {{-- Transcript --}}
                    <div class="bg-white rounded-2xl p-8 shadow-sm border border-navy/5 mb-8">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-gold/10 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                            </div>
                            <h2 class="font-heading text-2xl font-bold text-navy">Transcript</h2>
                        </div>
                        <p class="text-navy/40 italic">Transcript coming soon. We're working on making all episodes accessible with full text transcripts.</p>
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[34%]">
                    <div class="lg:sticky lg:top-[90px] space-y-6">
                        {{-- Episode Info --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-4">
                                <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Episode Info</h3>
                            </div>
                            <dl class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-navy/50">Episode</dt>
                                    <dd class="font-semibold text-navy">{{ $episode->episode_number }}</dd>
                                </div>
                                @if($episode->season_number)
                                    <div class="flex justify-between">
                                        <dt class="text-navy/50">Season</dt>
                                        <dd class="font-semibold text-navy">{{ $episode->season_number }}</dd>
                                    </div>
                                @endif
                                @if($episode->duration_seconds)
                                    <div class="flex justify-between">
                                        <dt class="text-navy/50">Duration</dt>
                                        <dd class="font-semibold text-navy">{{ $episode->formatted_duration }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <dt class="text-navy/50">Published</dt>
                                    <dd class="font-semibold text-navy">{{ $episode->published_at->format('M j, Y') }}</dd>
                                </div>
                            </dl>
                        </div>

                        {{-- Listen On --}}
                        @if($episode->apple_url || $episode->spotify_url || $episode->youtube_url)
                            <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                                <div class="flex items-center gap-3 mb-4">
                                    <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                    <h3 class="font-heading text-lg font-bold text-navy">Listen On</h3>
                                </div>
                                <div class="space-y-3">
                                    @if($episode->apple_url)
                                        <a href="{{ $episode->apple_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 bg-black text-white font-medium px-5 py-3 rounded-xl shadow-md hover:shadow-lg transition-all text-sm hover:-translate-y-0.5 w-full justify-center">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.1 22C7.79 22.05 6.8 20.68 5.96 19.47C4.25 16.56 2.93 11.3 4.7 7.72C5.57 5.94 7.36 4.86 9.28 4.84C10.56 4.81 11.78 5.7 12.56 5.7C13.34 5.7 14.85 4.62 16.41 4.8C17.07 4.83 18.96 5.06 20.16 6.87C20.05 6.95 17.58 8.37 17.61 11.34C17.65 14.9 20.68 16.04 20.71 16.06C20.69 16.13 20.18 17.86 18.71 19.5Z"/></svg>
                                            Apple Podcasts
                                        </a>
                                    @endif
                                    @if($episode->spotify_url)
                                        <a href="{{ $episode->spotify_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 bg-[#1DB954] text-white font-medium px-5 py-3 rounded-xl shadow-md hover:shadow-lg transition-all text-sm hover:-translate-y-0.5 w-full justify-center">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2z"/></svg>
                                            Spotify
                                        </a>
                                    @endif
                                    @if($episode->youtube_url)
                                        <a href="{{ $episode->youtube_url }}" target="_blank" rel="noopener" class="flex items-center gap-3 bg-[#FF0000] text-white font-medium px-5 py-3 rounded-xl shadow-md hover:shadow-lg transition-all text-sm hover:-translate-y-0.5 w-full justify-center">
                                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                            YouTube
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- Share --}}
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <div class="flex items-center gap-3 mb-4">
                                <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Share</h3>
                            </div>
                            <div class="space-y-3">
                                <a href="https://twitter.com/intent/tweet?text={{ urlencode($episode->title . ' — Mouse28 Podcast') }}&url={{ urlencode(url('/episodes/' . $episode->slug)) }}" target="_blank" rel="noopener"
                                   class="flex items-center gap-3 bg-navy/5 hover:bg-navy text-navy/60 hover:text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-all w-full">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    Post on X
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/episodes/' . $episode->slug)) }}" target="_blank" rel="noopener"
                                   class="flex items-center gap-3 bg-navy/5 hover:bg-[#1877F2] text-navy/60 hover:text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-all w-full">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    Share on Facebook
                                </a>
                                <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>{const l=this.querySelector('.copy-label');l.textContent='Copied!';setTimeout(()=>l.textContent='Copy Link',1500)})"
                                        class="flex items-center gap-3 bg-navy/5 hover:bg-purple text-navy/60 hover:text-white px-4 py-2.5 rounded-xl text-sm font-medium transition-all w-full text-left">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                    <span class="copy-label">Copy Link</span>
                                </button>
                            </div>
                        </div>

                        {{-- Related Posts --}}
                        @if($relatedPosts->count())
                            <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                                <div class="flex items-center gap-3 mb-4">
                                    <div style="height: 3px; width: 40px; background: linear-gradient(90deg, #d4a843, #f0c75e); border-radius: 2px;"></div>
                                    <h3 class="font-heading text-lg font-bold text-navy">Related Posts</h3>
                                </div>
                                <div class="space-y-4">
                                    @foreach($relatedPosts as $post)
                                        <a href="/blog/{{ $post->slug }}" class="group flex items-start gap-3">
                                            @if($post->cover_image)
                                                <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-16 h-16 rounded-xl object-cover flex-shrink-0">
                                            @else
                                                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center flex-shrink-0">
                                                    <span class="text-lg">✨</span>
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                @if($post->category)
                                                    <span class="text-[10px] uppercase tracking-wider font-bold text-purple">{{ $post->category->name }}</span>
                                                @endif
                                                <h4 class="font-heading text-sm font-semibold text-navy group-hover:text-purple transition-colors line-clamp-2">{{ $post->title }}</h4>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Browse Episodes CTA --}}
                        <div class="bg-gradient-to-br from-navy to-navy-light rounded-2xl p-6 text-center">
                            <span class="text-3xl mb-3 block">🎧</span>
                            <h3 class="font-heading text-lg font-bold text-white mb-2">More Episodes</h3>
                            <p class="text-white/60 text-sm mb-4">Browse all episodes from Mouse28</p>
                            <a href="/episodes" class="inline-block bg-gradient-to-r from-gold to-gold-light text-navy font-bold px-6 py-2.5 rounded-xl text-sm hover:shadow-lg transition-all hover:-translate-y-0.5">
                                All Episodes
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
