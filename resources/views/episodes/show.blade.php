<x-layouts.app
    :title="($episode->meta_title ?: $episode->title).' — Mouse28'"
    :description="$episode->meta_description ?: Str::limit($episode->description, 160)"
    :og-title="$episode->meta_title ?: $episode->title"
    :og-description="$episode->meta_description ?: Str::limit($episode->description, 200)"
    :og-image="$episode->og_image_url ?: $episode->cover_image_url"
    :robots="($isPreview ?? false) ? 'noindex,nofollow' : 'index,follow'"
>
    @unless ($isPreview ?? false)
        @push('head')
            <x-structured-data :data="\App\Support\StructuredData::forEpisode($episode, $podcast)" />
        @endpush
    @endunless

    @if ($isPreview ?? false)
        <div role="status" class="bg-gold text-navy px-4 py-3 text-center text-sm font-semibold">
            Preview mode — this page is only visible to administrators.
        </div>
    @endif
    <section class="from-navy to-navy-light relative overflow-hidden bg-linear-to-br py-16 md:py-24">
        {{-- Waveform background --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]">
            <svg class="absolute bottom-0 left-0 h-32 w-full text-white" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor">
                <g stroke="white" stroke-width="2">
                    @for ($i = 0; $i < 60; $i++)
                        <line
                            x1="{{ $i * 20 + 10 }}"
                            y1="{{ 60 - rand(5, 50) }}"
                            x2="{{ $i * 20 + 10 }}"
                            y2="{{ 60 + rand(5, 50) }}"
                            stroke-linecap="round"
                        />
                    @endfor
                </g>
            </svg>
        </div>
        <div class="relative z-10 mx-auto max-w-4xl px-4 sm:px-6">
            <a
                href="{{ route('episodes.index') }}"
                class="hover:text-gold mb-6 inline-flex min-h-11 items-center gap-1 text-base text-white/50 transition-colors sm:text-sm"
            >
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                All Episodes
            </a>

            <div class="border-gold/30 mt-4 mb-5 inline-flex items-center gap-2 rounded-full border px-4 py-1.5">
                <span class="bg-gold size-2 animate-pulse rounded-full"></span>
                <span class="font-body text-gold text-xs font-semibold tracking-[0.15em] uppercase">Episode {{ $episode->episode_number }}</span>
            </div>

            <div class="mb-4 flex items-center gap-3">
                <span class="bg-gold/20 text-gold rounded-full px-4 py-1 text-sm font-bold backdrop-blur-sm">Episode {{ $episode->episode_number }}</span>
                @if ($episode->season_number)
                    <span class="rounded-full bg-white/10 px-3 py-1 text-sm text-white/70">Season {{ $episode->season_number }}</span>
                @endif
                @if ($episode->duration_seconds)
                    <span class="flex items-center gap-1 text-sm text-white/40">
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $episode->formatted_duration }}
                    </span>
                @endif
                <span class="text-sm text-white/40">{{ $episode->published_at?->format('F j, Y') ?? 'Not scheduled' }}</span>
            </div>
            <h1 class="font-heading max-w-4xl text-4xl font-bold break-words text-white md:text-5xl lg:text-6xl">
                {{ $episode->title }}
            </h1>
            @if ($episode->description)
                <p class="mt-4 max-w-3xl text-lg/relaxed text-white/60">{{ $episode->description }}</p>
            @endif
        </div>
    </section>

    <section class="bg-cream py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex flex-col gap-10 lg:flex-row">
                {{-- Main Content --}}
                <div class="lg:w-[66%]">
                    {{-- Custom Audio Player Card --}}
                    @if ($episode->audio_source_url)
                        <div class="from-navy to-navy-light relative mb-10 overflow-hidden rounded-2xl bg-linear-to-r p-6 shadow-xl md:p-8">
                            {{-- Decorative waveform behind player --}}
                            <div class="pointer-events-none absolute inset-0 opacity-[0.07]">
                                <svg class="absolute bottom-0 left-0 h-32 w-full text-white" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor">
                                    <g stroke="white" stroke-width="2">
                                        @for ($i = 0; $i < 60; $i++)
                                            <line
                                                x1="{{ $i * 20 + 10 }}"
                                                y1="{{ 60 - rand(5, 50) }}"
                                                x2="{{ $i * 20 + 10 }}"
                                                y2="{{ 60 + rand(5, 50) }}"
                                                stroke-linecap="round"
                                            />
                                        @endfor
                                    </g>
                                </svg>
                            </div>

                            <div class="relative z-10 flex flex-col items-center gap-6 sm:flex-row">
                                {{-- Episode artwork/number --}}
                                <div class="size-24 shrink-0 overflow-hidden rounded-2xl shadow-lg">
                                    <img
                                        src="{{ $episode->cover_image_url ?: '/images/podcast/mouse28-cover.jpg' }}"
                                        alt="{{ $episode->title }} podcast artwork"
                                        width="3000"
                                        height="3000"
                                        loading="lazy"
                                        decoding="async"
                                        class="size-full object-cover"
                                    />
                                </div>

                                <div class="w-full flex-1">
                                    <p class="mb-1 text-xs tracking-wider text-white/50 uppercase">Now Playing</p>
                                    <p class="font-heading mb-3 text-lg font-semibold text-white">
                                        {{ $episode->title }}
                                    </p>
                                    <audio
                                        controls
                                        class="[&::-webkit-media-controls-panel]:rounded-xl [&::-webkit-media-controls-panel]:bg-white/10 w-full"
                                        preload="metadata"
                                    >
                                        <source src="{{ $episode->audio_source_url }}" type="audio/mpeg" />
                                        Your browser does not support the audio element.
                                    </audio>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Show Notes --}}
                    @if ($episode->show_notes)
                        <div class="border-navy/5 mb-8 rounded-2xl border bg-white p-8 shadow-sm md:p-10">
                            <div class="mb-8 flex items-center gap-3">
                                <div class="bg-purple/10 flex size-10 items-center justify-center rounded-xl">
                                    <svg class="text-purple size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                </div>
                                <h2 class="font-heading text-navy text-2xl font-bold">Show Notes</h2>
                            </div>
                            <div class="episode-show-notes-content">{!! $episode->show_notes !!}</div>
                        </div>
                    @endif

                    {{-- Transcript --}}
                    <div class="border-navy/5 mb-8 rounded-2xl border bg-white p-8 shadow-sm md:p-10">
                        <div class="mb-8 flex items-center gap-3">
                            <div class="bg-gold/10 flex size-10 items-center justify-center rounded-xl">
                                <svg class="text-gold size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" /></svg>
                            </div>
                            <h2 class="font-heading text-navy text-2xl font-bold">Transcript</h2>
                        </div>
                        @if ($episode->transcript)
                            <div x-data="{ expanded: false }">
                                <div
                                    class="episode-transcript-content max-h-[600px]"
                                    :class="{ 'max-h-none': expanded }"
                                >
                                    {!! $episode->transcript !!}
                                </div>
                                <div class="relative" x-show="! expanded" x-cloak>
                                    <div class="pointer-events-none absolute inset-x-0 bottom-full h-20 bg-linear-to-t from-white to-transparent"></div>
                                </div>
                                <button
                                    type="button"
                                    @click="expanded = ! expanded"
                                    class="border-navy/10 text-gold hover:border-gold hover:bg-gold/5 mt-4 w-full rounded-xl border py-3 text-center text-sm font-semibold transition-colors"
                                >
                                    <span
                                        x-text="expanded ? 'Collapse Transcript' : 'Read Full Transcript'"
                                        class="inline-flex items-center gap-1.5"
                                    >Read Full Transcript</span>
                                    <svg :class="expanded ? 'rotate-180' : ''" class="ml-1 inline-block size-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                </button>
                            </div>
                        @else
                            <p class="text-navy/40 italic">A transcript is not available for this episode.</p>
                        @endif
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[34%]">
                    <div class="space-y-6 lg:sticky lg:top-[90px]">
                        {{-- Episode Info --}}
                        <div class="border-navy/5 rounded-2xl border bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="from-gold to-gold-light h-[3px] w-10 rounded-sm bg-linear-to-r"></div>
                                <h3 class="font-heading text-navy text-lg font-bold">Episode Info</h3>
                            </div>
                            {{-- Episode number highlight --}}
                            <div class="border-navy/8 mb-5 flex items-center gap-4 border-b pb-5">
                                <div class="from-purple to-navy flex size-14 shrink-0 items-center justify-center rounded-xl bg-linear-to-br shadow-md">
                                    <div class="text-center leading-none">
                                        <span class="block text-[8px] font-bold text-white/50 uppercase">EP</span>
                                        <span class="font-heading -mt-0.5 block text-xl font-bold text-white">{{ $episode->episode_number }}</span>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <span class="font-heading text-navy block truncate text-sm font-bold">{{ $episode->title }}</span>
                                    <span class="text-navy/40 text-xs">{{ $episode->published_at?->format('F j, Y') ?? 'Not scheduled' }}</span>
                                </div>
                            </div>
                            <dl class="space-y-0 text-sm">
                                @if ($episode->season_number)
                                    <div class="border-navy/5 flex justify-between border-b py-3">
                                        <dt class="text-navy/45 flex items-center gap-2">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                                            Season
                                        </dt>
                                        <dd class="text-navy font-semibold">{{ $episode->season_number }}</dd>
                                    </div>
                                @endif
                                @if ($episode->duration_seconds)
                                    <div class="border-navy/5 flex justify-between border-b py-3">
                                        <dt class="text-navy/45 flex items-center gap-2">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            Duration
                                        </dt>
                                        <dd class="text-navy font-semibold">{{ $episode->formatted_duration }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between py-3">
                                    <dt class="text-navy/45 flex items-center gap-2">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        Published
                                    </dt>
                                    <dd class="text-navy font-semibold">
                                        {{ $episode->published_at?->format('M j, Y') ?? 'Not scheduled' }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        @php
                            $appleUrl = $episode->apple_url ?: $podcast->apple_url;
                            $spotifyUrl = $episode->spotify_url ?: $podcast->spotify_url;
                            $youtubeUrl = $episode->youtube_url ?: $podcast->youtube_url;
                        @endphp

                        {{-- Listen On --}}
                        <div class="border-navy/5 rounded-2xl border bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="from-gold to-gold-light h-[3px] w-10 rounded-sm bg-linear-to-r"></div>
                                <h3 class="font-heading text-navy text-lg font-bold">Listen On</h3>
                            </div>
                            <div class="space-y-3">
                                @if ($appleUrl)
                                    <a
                                        href="{{ $appleUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="group border-navy/8 hover:border-navy/20 hover:bg-navy/[0.03] flex w-full items-center gap-3 rounded-xl border px-4 py-3.5 transition-colors"
                                    >
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#fc3c44] to-[#d42d56] shadow-sm">
                                            <svg class="size-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.1 22C7.79 22.05 6.8 20.68 5.96 19.47C4.25 16.56 2.93 11.3 4.7 7.72C5.57 5.94 7.36 4.86 9.28 4.84C10.56 4.81 11.78 5.7 12.56 5.7C13.34 5.7 14.85 4.62 16.41 4.8C17.07 4.83 18.96 5.06 20.16 6.87C20.05 6.95 17.58 8.37 17.61 11.34C17.65 14.9 20.68 16.04 20.71 16.06C20.69 16.13 20.18 17.86 18.71 19.5ZM13 3.5C13.73 2.67 14.94 2.04 15.94 2C16.07 3.17 15.6 4.35 14.9 5.19C14.21 6.04 13.07 6.7 11.95 6.61C11.8 5.46 12.36 4.26 13 3.5Z" /></svg>
                                        </div>
                                        <div>
                                            <span class="text-navy group-hover:text-purple block text-base font-semibold transition-colors sm:text-sm">Apple Podcasts</span>
                                            <span class="text-navy/35 text-sm sm:text-xs">{{ $episode->apple_url ? 'Listen to this episode' : 'Visit the show' }}</span>
                                        </div>
                                        <svg class="text-navy/20 group-hover:text-purple/50 ml-auto size-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                @endif
                                @if ($spotifyUrl)
                                    <a
                                        href="{{ $spotifyUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="group border-navy/8 hover:border-navy/20 hover:bg-navy/[0.03] flex w-full items-center gap-3 rounded-xl border px-4 py-3.5 transition-colors"
                                    >
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#1DB954] to-[#169c46] shadow-sm">
                                            <svg class="size-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z" /></svg>
                                        </div>
                                        <div>
                                            <span class="text-navy group-hover:text-purple block text-base font-semibold transition-colors sm:text-sm">Spotify</span>
                                            <span class="text-navy/35 text-sm sm:text-xs">{{ $episode->spotify_url ? 'Listen to this episode' : 'Visit the show' }}</span>
                                        </div>
                                        <svg class="text-navy/20 group-hover:text-purple/50 ml-auto size-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                @endif
                                @if ($youtubeUrl)
                                    <a
                                        href="{{ $youtubeUrl }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="group border-navy/8 hover:border-navy/20 hover:bg-navy/3 flex w-full items-center gap-3 rounded-xl border px-4 py-3.5 transition-colors"
                                    >
                                        <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-[#FF0000] to-[#cc0000] shadow-sm">
                                            <svg class="size-5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z" /></svg>
                                        </div>
                                        <div>
                                            <span class="text-navy group-hover:text-purple block text-sm font-semibold transition-colors">YouTube</span>
                                            <span class="text-navy/35 text-xs">{{ $episode->youtube_url ? 'Watch this episode' : 'Visit the channel' }}</span>
                                        </div>
                                        <svg class="text-navy/20 group-hover:text-purple/50 ml-auto size-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                @endif
                                <a
                                    href="{{ route('rss.podcast') }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group border-navy/8 hover:border-navy/20 hover:bg-navy/3 flex w-full items-center gap-3 rounded-xl border px-4 py-3.5 transition-colors"
                                >
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br from-orange-500 to-orange-600 shadow-sm">
                                        <svg class="size-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a14 14 0 0 1 14 14M5 11a8 8 0 0 1 8 8M5 19h.01" /></svg>
                                    </div>
                                    <div>
                                        <span class="text-navy group-hover:text-purple block text-sm font-semibold transition-colors">RSS Feed</span>
                                        <span class="text-navy/35 text-xs">Subscribe in another podcast app</span>
                                    </div>
                                    <svg class="text-navy/20 group-hover:text-purple/50 ml-auto size-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        </div>

                        {{-- Share --}}
                        <div class="border-navy/5 rounded-2xl border bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="from-gold to-gold-light h-[3px] w-10 rounded-sm bg-linear-to-r"></div>
                                <h3 class="font-heading text-navy text-lg font-bold">Share</h3>
                            </div>
                            <div class="space-y-3">
                                <a
                                    href="https://twitter.com/intent/tweet?text={{ urlencode($episode->title . ' — Mouse28 Podcast') }}&url={{ urlencode(route('episodes.show', $episode)) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="group border-navy/8 hover:border-navy/20 hover:bg-navy/3 flex w-full items-center gap-3 rounded-xl border px-4 py-3.5 transition-colors"
                                >
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-black shadow-sm">
                                        <svg class="size-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" /></svg>
                                    </div>
                                    <span class="text-navy group-hover:text-purple text-sm font-semibold transition-colors">Post on X</span>
                                    <svg class="text-navy/20 group-hover:text-purple/50 ml-auto size-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                                <a
                                    href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('episodes.show', $episode)) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="group border-navy/8 hover:border-navy/20 hover:bg-navy/3 flex w-full items-center gap-3 rounded-xl border px-4 py-3.5 transition-colors"
                                >
                                    <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-[#1877F2] shadow-sm">
                                        <svg class="size-4 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" /></svg>
                                    </div>
                                    <span class="text-navy group-hover:text-purple text-sm font-semibold transition-colors">Share on Facebook</span>
                                    <svg class="text-navy/20 group-hover:text-purple/50 ml-auto size-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                                <button
                                    type="button"
                                    data-copy-link
                                    class="group border-navy/8 hover:border-navy/20 hover:bg-navy/3 flex w-full items-center gap-3 rounded-xl border px-4 py-3.5 text-left transition-colors"
                                >
                                    <div class="from-purple to-navy flex size-9 shrink-0 items-center justify-center rounded-lg bg-linear-to-br shadow-sm">
                                        <svg class="size-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                    </div>
                                    <span
                                        data-copy-label
                                        class="text-navy group-hover:text-purple text-sm font-semibold transition-colors"
                                    >Copy Link</span>
                                    <span
                                        data-copy-feedback
                                        class="text-purple hidden text-sm font-semibold"
                                        aria-live="polite"
                                    >Copied!</span>
                                    <svg class="text-navy/20 group-hover:text-purple/50 ml-auto size-4 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </button>
                            </div>
                        </div>

                        {{-- Related Posts --}}
                        @if ($relatedPosts->count())
                            <div class="border-navy/5 rounded-2xl border bg-white p-6 shadow-sm">
                                <div class="mb-5 flex items-center gap-3">
                                    <div class="from-gold to-gold-light h-[3px] w-10 rounded-sm bg-linear-to-r"></div>
                                    <h3 class="font-heading text-navy text-lg font-bold">Related Posts</h3>
                                </div>
                                <div class="space-y-4">
                                    @foreach ($relatedPosts as $post)
                                        <a href="{{ route('blog.show', $post) }}" class="group flex items-start gap-3">
                                            @if ($post->cover_image_url)
                                                <img
                                                    src="{{ $post->cover_image_url }}"
                                                    alt="{{ $post->title }}"
                                                    loading="lazy"
                                                    decoding="async"
                                                    class="size-16 shrink-0 rounded-xl object-cover"
                                                />
                                            @else
                                                <div class="from-purple/10 to-gold/10 flex size-16 shrink-0 items-center justify-center rounded-xl bg-linear-to-br">
                                                    <svg class="text-purple/40 size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <h4 class="font-heading text-navy group-hover:text-purple line-clamp-2 text-sm font-semibold transition-colors">
                                                    {{ $post->title }}
                                                </h4>
                                                <span class="text-navy/35 mt-1 block text-xs">{{ $post->published_at->format('M j, Y') }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Browse Episodes CTA --}}
                        <div class="from-navy via-navy-light to-navy relative overflow-hidden rounded-2xl border border-white/5 bg-linear-to-br p-7 text-center">
                            <div class="bg-gold/5 absolute top-1/2 left-1/2 size-32 -translate-1/2 rounded-full blur-3xl"></div>
                            <div class="relative">
                                <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-xl border border-white/10 bg-white/10">
                                    <svg class="text-gold size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                                </div>
                                <h3 class="font-heading mb-2 text-lg font-bold text-white">More Episodes</h3>
                                <p class="mb-5 text-base/relaxed text-white/40 sm:text-sm/relaxed">
                                    Browse all episodes from Mouse28
                                </p>
                                <a
                                    href="{{ route('episodes.index') }}"
                                    class="from-gold to-gold-light text-navy shadow-gold/20 inline-block rounded-full bg-linear-to-r px-7 py-3 text-sm font-bold shadow-lg transition-transform hover:-translate-y-0.5"
                                >
                                    All Episodes
                                </a>
                            </div>
                        </div>

                        {{-- Newsletter --}}
                        <x-newsletter-card subtitle="New episodes & Disney tips delivered to your inbox" />
                    </div>
                </aside>
            </div>

            @if ($previousEpisode || $nextEpisode)
                <nav aria-label="Episode navigation" class="mt-12 grid gap-4 sm:grid-cols-2">
                    @if ($previousEpisode)
                        <a
                            href="{{ route('episodes.show', $previousEpisode) }}"
                            rel="prev"
                            class="group border-navy/5 hover:border-purple/20 flex min-h-24 flex-col justify-center rounded-2xl border bg-white px-6 py-5 shadow-sm transition-[border-color,box-shadow] hover:shadow-lg"
                        >
                            <span class="text-gold text-xs font-bold tracking-widest uppercase">← Previous episode</span>
                            <span class="font-heading text-navy group-hover:text-purple mt-2 text-lg font-bold transition-colors">{{ $previousEpisode->title }}</span>
                        </a>
                    @endif
                    @if ($nextEpisode)
                        <a
                            href="{{ route('episodes.show', $nextEpisode) }}"
                            rel="next"
                            class="group border-navy/5 hover:border-purple/20 flex min-h-24 flex-col justify-center rounded-2xl border bg-white px-6 py-5 text-right shadow-sm transition-[border-color,box-shadow] hover:shadow-lg sm:col-start-2"
                        >
                            <span class="text-gold text-xs font-bold tracking-widest uppercase">Next episode →</span>
                            <span class="font-heading text-navy group-hover:text-purple mt-2 text-lg font-bold transition-colors">{{ $nextEpisode->title }}</span>
                        </a>
                    @endif
                </nav>
            @endif
        </div>
    </section>
</x-layouts.app>
