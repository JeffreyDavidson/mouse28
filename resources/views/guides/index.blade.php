@extends('layouts.app')

@section('title', 'Disney Accessibility Guide — Mouse28')
@section('meta_description', 'Comprehensive accessibility guides for Walt Disney World parks. Sensory tips, DAS guides, quiet spots, dining, rides, and planning resources.')

@section('content')
    {{-- Hero --}}
    <section class="bg-gradient-to-br from-navy via-navy-light to-purple py-16 md:py-24 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 text-6xl sparkle">✨</div>
            <div class="absolute top-20 right-20 text-4xl sparkle-delay">⭐</div>
            <div class="absolute bottom-10 left-1/3 text-5xl sparkle-delay-2">🏰</div>
        </div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 relative">
            <div class="text-center max-w-3xl mx-auto">
                <span class="text-gold text-sm font-semibold uppercase tracking-wider">Resource Hub</span>
                <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white mt-4">Disney Accessibility Guide</h1>
                <p class="text-white/60 text-lg md:text-xl mt-6 leading-relaxed">Helping families navigate Walt Disney World with confidence. Real tips from a family who visits every week with their autistic daughter.</p>
                <p class="text-white/40 text-sm mt-3">Browse by park below, or filter by category to find exactly what you need.</p>
            </div>
        </div>
    </section>

    {{-- Park Filter Cards --}}
    <section class="bg-white border-b border-navy/10 sticky top-16 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-5">
            {{-- Park Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:flex md:items-center gap-2 md:gap-3">
                <a href="/guides{{ $activeCategory ? '?category='.$activeCategory : '' }}"
                   class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm font-medium transition-all border-2 {{ !$activePark ? 'bg-navy text-white border-navy shadow-md' : 'bg-cream/50 text-navy/60 border-transparent hover:bg-cream hover:text-navy hover:border-navy/10' }}">
                    <span class="text-lg">🌟</span>
                    <span>All Parks</span>
                </a>
                @php
                    $parkEmojis = [
                        'magic-kingdom' => '🏰',
                        'epcot' => '🌐',
                        'hollywood-studios' => '🎬',
                        'animal-kingdom' => '🦁',
                        'disney-springs' => '🛍️',
                        'resorts' => '🏨',
                        'general' => '📋',
                    ];
                @endphp
                @foreach($parks as $slug => $label)
                    <a href="/guides?park={{ $slug }}{{ $activeCategory ? '&category='.$activeCategory : '' }}"
                       class="flex items-center gap-2.5 px-4 py-3 rounded-xl text-sm font-medium transition-all border-2 {{ $activePark === $slug ? 'bg-navy text-white border-navy shadow-md' : 'bg-cream/50 text-navy/60 border-transparent hover:bg-cream hover:text-navy hover:border-navy/10' }}">
                        <span class="text-lg">{{ $parkEmojis[$slug] ?? '🎡' }}</span>
                        <span class="hidden sm:inline">{{ $label }}</span>
                        <span class="sm:hidden text-xs">{{ Str::limit($label, 12) }}</span>
                    </a>
                @endforeach
            </div>

            {{-- Category Filters --}}
            <div class="flex items-center gap-2 overflow-x-auto pt-3 -mb-1 scrollbar-hide">
                <span class="text-navy/40 text-xs font-medium uppercase tracking-wider mr-1 flex-shrink-0">Category:</span>
                <a href="/guides{{ $activePark ? '?park='.$activePark : '' }}"
                   class="whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium transition-all {{ !$activeCategory ? 'bg-gold/20 text-gold-dark border border-gold/30' : 'text-navy/50 hover:bg-navy/5' }}">
                    All
                </a>
                @foreach($categories as $slug => $label)
                    <a href="/guides?{{ $activePark ? 'park='.$activePark.'&' : '' }}category={{ $slug }}"
                       class="whitespace-nowrap px-3 py-1.5 rounded-full text-xs font-medium transition-all {{ $activeCategory === $slug ? 'bg-gold/20 text-gold-dark border border-gold/30' : 'text-navy/50 hover:bg-navy/5' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Guides --}}
    <section class="py-12 md:py-16 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            @if($guides->isEmpty())
                <div class="text-center py-20 bg-white rounded-3xl border border-navy/5">
                    <div class="text-6xl mb-4">🔍</div>
                    <h2 class="font-heading text-2xl font-bold text-navy mb-2">No guides found</h2>
                    <p class="text-navy/50">Try adjusting your filters or <a href="/guides" class="text-purple hover:text-gold transition-colors underline">view all guides</a>.</p>
                </div>
            @else
                @foreach($guides as $park => $parkGuides)
                    <div class="{{ !$loop->first ? 'mt-14' : '' }}">
                        {{-- Park section header --}}
                        <div class="bg-white/70 backdrop-blur-sm rounded-2xl px-6 py-4 mb-6 border border-navy/5">
                            <div class="flex items-center gap-4">
                                <span class="text-3xl">{{ $parkEmojis[$park] ?? '🎡' }}</span>
                                <div>
                                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy">
                                        {{ \App\Models\Guide::PARKS[$park] ?? $park }}
                                    </h2>
                                    <span class="text-navy/40 text-sm">{{ $parkGuides->count() }} {{ Str::plural('guide', $parkGuides->count()) }} available</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($parkGuides as $guide)
                                <a href="/guides/{{ $guide->slug }}" class="group bg-white rounded-2xl p-6 shadow-sm border-2 border-navy/5 hover:border-purple/30 hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                                    {{-- Icon prominently displayed --}}
                                    @if($guide->icon)
                                        <div class="w-14 h-14 bg-gradient-to-br from-purple/10 to-gold/10 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-300">
                                            <span class="text-3xl">{{ $guide->icon }}</span>
                                        </div>
                                    @endif

                                    <h3 class="font-heading text-lg font-bold text-navy group-hover:text-purple transition-colors mb-2">{{ $guide->title }}</h3>

                                    @if($guide->excerpt)
                                        <p class="text-navy/50 text-sm leading-relaxed line-clamp-3 mb-4">{{ $guide->excerpt }}</p>
                                    @endif

                                    <div class="flex flex-wrap gap-2 mt-auto">
                                        <span class="text-xs font-semibold px-3 py-1 rounded-full bg-purple/10 text-purple">{{ $guide->park_label }}</span>
                                        <span class="text-xs font-medium px-3 py-1 rounded-full bg-gold/15 text-gold-dark">{{ $guide->category_label }}</span>
                                    </div>

                                    <div class="flex items-center gap-1 mt-4 text-purple text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity">
                                        Read guide
                                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </section>
@endsection
