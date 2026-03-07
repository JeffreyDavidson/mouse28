@extends('layouts.app')

@section('title', 'Community Stories — Mouse28')
@section('meta_description', 'Real stories from autism families sharing their Disney park experiences.')

@section('content')
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-24 relative overflow-hidden">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-full px-4 py-1.5 mb-6">
                <span class="w-2 h-2 bg-gold rounded-full animate-pulse"></span>
                <span class="text-gold text-sm font-semibold tracking-widest uppercase">Community Stories</span>
            </div>
            <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white mt-2">Our Disney Family</h1>
            <p class="text-white/60 mt-4 max-w-xl mx-auto text-lg">Real stories from real families navigating the magic of Disney with children on the autism spectrum. You're not alone on this journey.</p>

            {{-- Community Stats --}}
            @php $totalStories = ($featured->count() ?? 0) + ($stories->total() ?? 0); @endphp
            @if($totalStories > 0)
                <div class="inline-flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-full px-6 py-3 mt-8">
                    <span class="text-2xl">💛</span>
                    <span class="text-white font-heading text-lg font-semibold">{{ $totalStories }} {{ Str::plural('family', $totalStories) }} {{ $totalStories == 1 ? 'has' : 'have' }} shared {{ $totalStories == 1 ? 'a story' : 'their stories' }}</span>
                </div>
            @endif

            <div class="mt-8">
                <a href="/stories/share" class="inline-flex items-center gap-2 bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-3.5 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5 text-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Share Your Story
                </a>
            </div>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
        {{-- Featured Stories --}}
        @if($featured->isNotEmpty())
            <section class="mb-16">
                <h2 class="font-heading text-2xl font-bold text-navy mb-8 flex items-center gap-2">
                    <span class="text-gold">⭐</span> Featured Stories
                </h2>
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($featured as $story)
                        <div class="bg-white rounded-2xl shadow-sm border border-cream-dark p-8 relative group hover:shadow-lg transition-all hover:-translate-y-1 duration-300 flex flex-col">
                            {{-- Large decorative quotation mark --}}
                            <div class="absolute top-3 right-5 font-heading text-8xl text-purple/[0.06] leading-none select-none pointer-events-none" aria-hidden="true">"</div>

                            <div class="relative z-10 flex-1">
                                <h3 class="font-heading text-xl font-bold text-navy mb-3">{{ $story->title }}</h3>
                                <p class="text-navy/70 text-sm leading-relaxed mb-6 line-clamp-4">{{ Str::limit($story->story, 250) }}</p>
                            </div>

                            <div class="border-t border-cream-dark pt-4 mt-auto">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-purple to-gold rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-white font-bold text-sm">{{ strtoupper(substr($story->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-navy text-sm">{{ $story->name }}</p>
                                        <div class="flex items-center gap-2 text-xs text-navy/40">
                                            @if($story->location)
                                                <span>📍 {{ $story->location }}</span>
                                            @endif
                                            @if($story->child_name)
                                                <span class="text-purple">
                                                    {{ $story->child_name }}@if($story->child_age), {{ $story->child_age }}@endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($story->favorite_park)
                                    <span class="inline-block mt-3 text-xs bg-purple/10 text-purple px-3 py-1 rounded-full font-medium">🏰 {{ $story->favorite_park }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- All Stories --}}
        @if($stories->isNotEmpty())
            <section>
                <h2 class="font-heading text-2xl font-bold text-navy mb-8">All Stories</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($stories as $story)
                        <div class="bg-white rounded-2xl shadow-sm border border-cream-dark p-6 hover:shadow-md hover:border-purple/20 transition-all duration-300 group flex flex-col" x-data="{ expanded: false }">
                            <h3 class="font-heading text-lg font-bold text-navy mb-2 group-hover:text-purple transition-colors">{{ $story->title }}</h3>

                            <div class="text-navy/70 text-sm leading-relaxed mb-4 flex-1">
                                <p x-show="!expanded">{{ Str::limit($story->story, 150) }}</p>
                                <p x-show="expanded" x-cloak>{{ $story->story }}</p>
                                @if(strlen($story->story) > 150)
                                    <button @click="expanded = !expanded" class="text-purple text-xs font-semibold mt-1 hover:text-purple-dark transition-colors">
                                        <span x-text="expanded ? '← Read less' : 'Read more →'"></span>
                                    </button>
                                @endif
                            </div>

                            <div class="border-t border-cream-dark pt-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple/20 to-gold/20 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-navy font-bold text-xs">{{ strtoupper(substr($story->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-navy text-sm">{{ $story->name }}</p>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            @if($story->location)
                                                <span class="text-navy/40 text-xs">📍 {{ $story->location }}</span>
                                            @endif
                                            @if($story->child_name)
                                                <span class="text-purple text-xs">
                                                    {{ $story->child_name }}@if($story->child_age), age {{ $story->child_age }}@endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @if($story->favorite_park)
                                    <span class="inline-block mt-2 text-xs text-navy/30 font-medium">🏰 {{ $story->favorite_park }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $stories->links() }}
                </div>
            </section>
        @else
            {{-- Empty State --}}
            <div class="text-center py-20 bg-white rounded-3xl border border-cream-dark relative overflow-hidden">
                <div class="absolute inset-0 opacity-5">
                    <div class="absolute top-1/4 left-1/4 font-heading text-[200px] text-purple leading-none">"</div>
                </div>
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple to-gold rounded-full flex items-center justify-center mx-auto mb-6">
                        <span class="text-3xl">🏰</span>
                    </div>
                    <h2 class="font-heading text-3xl font-bold text-navy mb-3">Be the First to Share</h2>
                    <p class="text-navy/60 max-w-md mx-auto mb-8 text-lg">We'd love to hear about your family's Disney adventures. Every story helps another family feel less alone.</p>
                    <a href="/stories/share" class="inline-flex items-center gap-2 bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-3.5 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5 text-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Share Your Story
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Bottom CTA Banner --}}
    @if($stories->isNotEmpty())
        <section class="bg-gradient-to-br from-navy to-navy-light py-16 no-print">
            <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center">
                <h2 class="font-heading text-3xl font-bold text-white mb-4">Your Story Matters</h2>
                <p class="text-white/70 text-lg mb-8">Every family's Disney experience is unique. Share yours and help another family feel less alone on their journey.</p>
                <a href="/stories/share" class="inline-flex items-center gap-2 bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-3.5 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5 text-lg">
                    Share Your Story 💛
                </a>
            </div>
        </section>
    @endif
@endsection
