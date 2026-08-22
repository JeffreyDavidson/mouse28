@extends('layouts.app')

@section('title', ($podcast->name ?: 'Mouse28').' Podcast')
@section('meta_description', $podcast->description ?: 'Disney park stories, accessibility conversations, and family experiences from the Mouse28 podcast.')
@section('og_title', ($podcast->name ?: 'Mouse28').' Podcast')
@section('og_description', $podcast->description ?: 'Disney park stories, accessibility conversations, and family experiences from the Mouse28 podcast.')
@section('og_image', $podcast->cover_image ? '/storage/'.ltrim($podcast->cover_image, '/') : '/images/podcast/mouse28-cover.jpg')
@section('canonical', $canonicalUrl)

@section('content')
    <section class="relative overflow-hidden bg-linear-to-br from-navy to-navy-light py-16 md:py-24">
        {{-- Waveform SVG decoration --}}
        <div class="pointer-events-none absolute inset-0 opacity-[0.07]">
            <svg class="absolute bottom-0 left-0 h-32 w-full text-white" viewBox="0 0 1200 120" preserveAspectRatio="none" fill="none" stroke="currentColor">
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
        <div class="relative z-10 mx-auto max-w-6xl px-4 text-center sm:px-6">
            <div class="mb-6 inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 backdrop-blur-sm">
                <span class="size-2 animate-pulse rounded-full bg-gold"></span>
                <span class="text-sm font-semibold tracking-widest text-gold uppercase">Podcast</span>
            </div>
            <h1 class="mt-2 font-heading text-4xl font-bold text-white md:text-5xl lg:text-6xl">Listen Along</h1>
            <p class="mx-auto mt-4 max-w-xl text-lg text-white/60">Every week we bring you stories, tips, and magic from inside Disney parks, from our family to yours.</p>
            {{-- Platform subscribe badges --}}
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                @foreach ($podcast->distributionLinks() as $link)
                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-12 items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-4 py-2.5 text-base font-semibold text-white/80 backdrop-blur-sm transition-colors hover:border-gold/40 hover:text-gold sm:text-sm">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7v7m0-7L10 14M5 7v12h12v-5"/></svg>
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="bg-cream py-16">
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            @if($episodes->count())
            <div class="flex flex-col gap-10 lg:flex-row">
                <div class="lg:w-[66%]">
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
                                <div class="flex items-center gap-3 rounded-full border border-navy/5 bg-white px-5 py-2 shadow-sm">
                                    <div class="flex size-8 items-center justify-center rounded-full bg-linear-to-br from-purple to-navy">
                                        <span class="text-xs font-bold text-white">S{{ $season }}</span>
                                    </div>
                                    <span class="font-heading text-lg font-bold text-navy">Season {{ $season }}</span>
                                    <span class="text-sm text-navy/30">{{ $seasonEpisodes->count() }} {{ Str::plural('episode', $seasonEpisodes->count()) }}</span>
                                </div>
                                <div class="h-px flex-1 bg-navy/10"></div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-5 {{ $season == 0 && !$loop->first ? 'mt-14' : '' }}">
                        @foreach($seasonEpisodes as $episode)
                            <a href="{{ route('episodes.show', $episode) }}" class="group block overflow-hidden rounded-2xl border border-navy/5 bg-white shadow-sm transition-[transform,border-color,box-shadow] duration-300 hover:-translate-y-1 hover:border-purple/20 hover:shadow-xl">
                                <div class="flex flex-col sm:flex-row">
                                    {{-- Episode badge with play button --}}
                                    <div class="relative flex h-24 shrink-0 items-center justify-center bg-linear-to-br from-purple to-navy sm:h-auto sm:w-32">
                                        {{-- EP badge --}}
                                        <div class="relative z-10 text-center transition-[transform,opacity] duration-300 group-hover:scale-90 group-hover:opacity-0">
                                            <span class="block text-[10px] font-bold tracking-widest text-white/40 uppercase">EP</span>
                                            <span class="-mt-1 block font-heading text-3xl font-bold text-white">{{ $episode->episode_number }}</span>
                                        </div>
                                        {{-- Play button (shows on hover) --}}
                                        <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                                            <div class="flex size-14 items-center justify-center rounded-full bg-linear-to-br from-gold to-gold-light shadow-lg">
                                                <svg class="ml-0.5 size-6 text-navy" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                            </div>
                                        </div>
                                        {{-- Subtle corner accent --}}
                                        <div class="absolute top-2 left-2 size-5 rounded-tl-md border-t-2 border-l-2 border-white/10"></div>
                                        <div class="absolute right-2 bottom-2 size-5 rounded-br-md border-r-2 border-b-2 border-white/10"></div>
                                    </div>

                                    <div class="min-w-0 flex-1 p-5 sm:p-6">
                                        <div class="mb-2 flex flex-wrap items-center gap-3">
                                            <span class="text-xs font-medium text-navy/40">{{ $episode->published_at->format('M j, Y') }}</span>
                                            @if($episode->duration_seconds)
                                                <span class="text-navy/20">•</span>
                                                <span class="flex items-center gap-1 text-xs text-navy/40">
                                                    <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $episode->formatted_duration }}
                                                </span>
                                            @endif
                                            @if($episode->season_number)
                                                <span class="text-navy/20">•</span>
                                                <span class="text-xs text-navy/40">S{{ $episode->season_number }}E{{ $episode->episode_number }}</span>
                                            @endif
                                        </div>
                                        <h2 class="mb-2 font-heading text-xl font-semibold text-navy transition-colors group-hover:text-purple">{{ $episode->title }}</h2>
                                        <p class="line-clamp-2 text-base/relaxed text-navy/60 sm:text-sm/relaxed">{{ Str::limit($episode->description, 200) }}</p>

                                        <div class="mt-4 flex items-center gap-4">
                                            @if($episode->apple_url)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-navy/5 px-2.5 py-1 text-xs text-navy/40">
                                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5C17.88 20.74 17 21.95 15.66 21.97C14.32 22 13.89 21.18 12.37 21.18C10.84 21.18 10.37 21.95 9.1 22C7.79 22.05 6.8 20.68 5.96 19.47C4.25 16.56 2.93 11.3 4.7 7.72C5.57 5.94 7.36 4.86 9.28 4.84C10.56 4.81 11.78 5.7 12.56 5.7C13.34 5.7 14.85 4.62 16.41 4.8C17.07 4.83 18.96 5.06 20.16 6.87C20.05 6.95 17.58 8.37 17.61 11.34C17.65 14.9 20.68 16.04 20.71 16.06C20.69 16.13 20.18 17.86 18.71 19.5Z"/></svg>
                                                    Apple
                                                </span>
                                            @endif
                                            @if($episode->spotify_url)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-navy/5 px-2.5 py-1 text-xs text-navy/40">
                                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2z"/></svg>
                                                    Spotify
                                                </span>
                                            @endif
                                            <span class="ml-auto flex items-center gap-1 text-sm font-medium text-purple transition-[gap] group-hover:gap-2">
                                                Listen now
                                                <svg class="size-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endforeach

                <div class="episodes-pagination mt-12 flex justify-center">
                    {{ $episodes->links() }}
                </div>
                </div>{{-- end lg:w-[66%] --}}

                <aside class="lg:w-[34%]">
                    <div class="space-y-6 lg:sticky lg:top-[90px]">
                        {{-- Podcast Stats --}}
                        <div class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Show Stats</h3>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="rounded-xl bg-linear-to-br from-purple/8 to-navy/5 p-4 text-center">
                                    <span class="block font-heading text-3xl font-bold text-navy">{{ $episodes->total() }}</span>
                                    <span class="mt-1 block text-xs font-medium tracking-wider text-navy/45 uppercase">{{ Str::plural('Episode', $episodes->total()) }}</span>
                                </div>
                                <div class="rounded-xl bg-linear-to-br from-gold/8 to-gold/3 p-4 text-center">
                                    <span class="block font-heading text-3xl font-bold text-navy">{{ $totalHours }}<span class="text-lg text-navy/40">h</span></span>
                                    <span class="mt-1 block text-xs font-medium tracking-wider text-navy/45 uppercase">Listening</span>
                                </div>
                            </div>
                        </div>

                        {{-- Listen On --}}
                        <div class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Listen On</h3>
                            </div>
                            <div class="space-y-3">
                                @foreach ($podcast->distributionLinks() as $link)
                                    <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="group flex min-h-12 w-full items-center gap-3 rounded-xl border border-navy/8 px-4 py-3 transition-colors hover:border-purple/25 hover:bg-purple/5">
                                        <svg class="size-5 shrink-0 text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3h7v7m0-7L10 14M5 7v12h12v-5"/></svg>
                                        <span class="font-semibold text-navy transition-colors group-hover:text-purple">{{ $link['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        {{-- Latest Episode --}}
                        @if($latestEpisode)
                        <div class="rounded-2xl border border-navy/5 bg-white p-6 shadow-sm">
                            <div class="mb-5 flex items-center gap-3">
                                <div class="h-[3px] w-10 rounded-sm bg-linear-to-r from-gold to-gold-light"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Latest Episode</h3>
                            </div>
                            <a href="{{ route('episodes.show', $latestEpisode) }}" class="group block rounded-xl border border-purple/10 bg-linear-to-br from-purple/5 to-navy/5 p-4 transition-colors hover:border-purple/25">
                                <div class="flex items-start gap-3">
                                    <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-purple to-navy shadow-md">
                                        <div class="text-center leading-none">
                                            <span class="block text-[8px] font-bold text-white/50 uppercase">EP</span>
                                            <span class="-mt-0.5 block font-heading text-lg font-bold text-white">{{ $latestEpisode->episode_number }}</span>
                                        </div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h4 class="line-clamp-2 font-heading text-sm/snug font-bold text-navy transition-colors group-hover:text-purple">{{ $latestEpisode->title }}</h4>
                                        @if($latestEpisode->duration_seconds)
                                            <div class="mt-2 flex items-center gap-1.5">
                                                <svg class="size-3 text-navy/30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                <span class="text-xs text-navy/40">{{ $latestEpisode->formatted_duration }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-3 flex items-center justify-end border-t border-purple/10 pt-3">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-purple transition-[gap] group-hover:gap-2.5">
                                        Listen Now
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </span>
                                </div>
                            </a>
                        </div>
                        @endif

                        {{-- Blog CTA --}}
                        <div class="relative overflow-hidden rounded-2xl border border-white/5 bg-linear-to-br from-navy via-navy-light to-navy p-7 text-center">
                            <div class="absolute top-1/2 left-1/2 size-32 -translate-1/2 rounded-full bg-gold/5 blur-3xl"></div>
                            <div class="relative">
                                <div class="mx-auto mb-4 flex size-12 items-center justify-center rounded-xl border border-white/10 bg-white/10">
                                    <svg class="size-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                                </div>
                                <h3 class="mb-2 font-heading text-lg font-bold text-white">Read the Blog</h3>
                                <p class="mb-5 text-base/relaxed text-white/40 sm:text-sm/relaxed">Disney tips, park guides, and family stories</p>
                                <a href="{{ route('blog.index') }}" class="inline-block rounded-full bg-linear-to-r from-gold to-gold-light px-7 py-3 text-sm font-bold text-navy shadow-lg shadow-gold/20 transition-transform hover:-translate-y-0.5">
                                    Visit Blog
                                </a>
                            </div>
                        </div>

                        {{-- Newsletter --}}
                        <x-newsletter-card subtitle="New episodes & Disney tips delivered to your inbox" />
                    </div>
                </aside>
            </div>{{-- end flex --}}
            @else
                @php
                    $emptyWaveformHeights = ['h-[20%]', 'h-[35%]', 'h-[55%]', 'h-[75%]', 'h-full', 'h-[65%]', 'h-[45%]', 'h-[30%]'];
                    $playerWaveformHeights = ['h-[30%]', 'h-[55%]', 'h-[80%]', 'h-full', 'h-[70%]', 'h-[45%]', 'h-[65%]', 'h-[35%]'];
                @endphp
                <div class="relative overflow-hidden rounded-3xl bg-linear-to-br from-navy via-navy-light to-purple-dark px-6 py-16 sm:px-12 sm:py-20">
                    {{-- Ambient glows --}}
                    <div class="pointer-events-none absolute top-[-30%] right-[-5%] size-[600px] bg-[radial-gradient(circle,rgb(212_168_67/6%)_0%,transparent_60%)]"></div>
                    <div class="pointer-events-none absolute bottom-[-20%] left-[-10%] size-[400px] bg-[radial-gradient(circle,rgb(91_62_158/20%)_0%,transparent_60%)]"></div>

                    {{-- Background waveform decoration --}}
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 flex h-20 items-end gap-[3px] px-8 opacity-[0.04]">
                        @for($i = 0; $i < 60; $i++)
                            <div class="flex-1 rounded-full bg-white {{ $emptyWaveformHeights[$i % count($emptyWaveformHeights)] }}"></div>
                        @endfor
                    </div>

                    <div class="relative z-10 mx-auto max-w-3xl">
                        <div class="grid items-center gap-12 md:grid-cols-2">
                            {{-- Left: Content --}}
                            <div class="text-center md:text-left">
                                <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-gold/30 px-4 py-[0.35rem]">
                                    <span class="size-1.5 rounded-full bg-gold"></span>
                                    <span class="font-body text-[0.7rem] font-semibold tracking-[0.15em] text-gold uppercase">Coming Soon</span>
                                </div>
                                <h2 class="mb-4 font-heading text-[clamp(1.75rem,3.5vw,2.5rem)] leading-[1.15] font-bold text-cream">We're warming up the mics</h2>
                                <p class="mb-8 text-[0.95rem] leading-[1.8] text-cream/50">
                                    Our first episode is in the works. Disney parks, accessibility, family stories, and a lot of heart. Subscribe so you're there from the very start.
                                </p>
                                <div class="flex items-center justify-center gap-3 md:justify-start">
                                    @foreach ($podcast->distributionLinks() as $link)
                                        <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-cream/10 bg-cream/8 px-4 py-2 text-base font-semibold text-cream/70 transition-colors hover:border-gold/30 hover:text-gold sm:text-xs">{{ $link['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Right: Faux player --}}
                            <div class="hidden md:block">
                                <div class="rounded-3xl border border-gold/12 bg-navy/60 p-8 shadow-2xl shadow-black/30 backdrop-blur-[10px]">
                                    {{-- Episode header --}}
                                    <div class="mb-6 flex items-center gap-4">
                                        <div class="flex size-14 shrink-0 items-center justify-center rounded-xl bg-linear-to-br from-purple to-purple-dark shadow-lg shadow-purple/40">
                                            <svg class="size-[22px] text-gold" fill="currentColor" viewBox="0 0 24 24"><path d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/></svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="mb-2 h-[9px] w-[45%] rounded-full bg-gold/30"></div>
                                            <div class="mb-[5px] h-3 w-[85%] rounded-full bg-cream/15"></div>
                                            <div class="h-3 w-3/5 rounded-full bg-cream/10"></div>
                                        </div>
                                    </div>

                                    {{-- Waveform bars with animation --}}
                                    <div class="mb-3 flex h-12 items-end gap-0.5">
                                        @for($i = 0; $i < 32; $i++)
                                            @php $played = $i < 12; @endphp
                                            <div class="episodes-waveform-bar flex-1 rounded-full {{ $played ? 'bg-gold/50' : 'bg-cream/10' }} {{ $playerWaveformHeights[$i % count($playerWaveformHeights)] }}"></div>
                                        @endfor
                                    </div>

                                    {{-- Progress bar --}}
                                    <div class="relative mb-2 h-[3px] rounded-full bg-cream/6">
                                        <div class="absolute top-0 left-0 h-full w-[38%] rounded-full bg-linear-to-r from-gold to-gold-light"></div>
                                        <div class="absolute top-1/2 left-[38%] size-[9px] -translate-1/2 rounded-full bg-gold-light shadow-[0_0_8px_rgb(212_168_67/50%)]"></div>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-body text-[0.65rem] text-cream/25">12:34</span>
                                        <span class="font-body text-[0.65rem] text-cream/25">32:18</span>
                                    </div>

                                    {{-- Playback controls --}}
                                    <div class="mt-4 flex items-center justify-center gap-6">
                                        <div class="flex size-7 items-center justify-center">
                                            <svg class="size-4 text-cream/25" fill="currentColor" viewBox="0 0 24 24"><path d="M6 6h2v12H6zm3.5 6l8.5 6V6z"/></svg>
                                        </div>
                                        <div class="flex size-12 items-center justify-center rounded-full bg-linear-to-br from-gold to-gold-dark shadow-lg shadow-gold/30">
                                            <svg class="ml-0.5 size-5 text-navy" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                        </div>
                                        <div class="flex size-7 items-center justify-center">
                                            <svg class="size-4 text-cream/25" fill="currentColor" viewBox="0 0 24 24"><path d="M6 18l8.5-6L6 6v12zM16 6v12h2V6h-2z"/></svg>
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
