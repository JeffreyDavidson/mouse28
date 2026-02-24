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
            </div>
        </div>
    </section>

    {{-- Filters --}}
    <section class="bg-white border-b border-navy/10 sticky top-16 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            {{-- Park Tabs --}}
            <div class="flex items-center gap-1 overflow-x-auto py-4 scrollbar-hide">
                <a href="/guides{{ $activeCategory ? '?category='.$activeCategory : '' }}"
                   class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-all {{ !$activePark ? 'bg-navy text-white shadow-md' : 'text-navy/60 hover:bg-navy/5 hover:text-navy' }}">
                    All Parks
                </a>
                @foreach($parks as $slug => $label)
                    <a href="/guides?park={{ $slug }}{{ $activeCategory ? '&category='.$activeCategory : '' }}"
                       class="whitespace-nowrap px-4 py-2 rounded-full text-sm font-medium transition-all {{ $activePark === $slug ? 'bg-navy text-white shadow-md' : 'text-navy/60 hover:bg-navy/5 hover:text-navy' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- Category Filters --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-4 -mt-1">
                <span class="text-navy/40 text-xs font-medium uppercase tracking-wider mr-1">Category:</span>
                <a href="/guides{{ $activePark ? '?park='.$activePark : '' }}"
                   class="whitespace-nowrap px-3 py-1 rounded-full text-xs font-medium transition-all {{ !$activeCategory ? 'bg-gold/20 text-gold-dark border border-gold/30' : 'text-navy/50 hover:bg-navy/5' }}">
                    All
                </a>
                @foreach($categories as $slug => $label)
                    <a href="/guides?{{ $activePark ? 'park='.$activePark.'&' : '' }}category={{ $slug }}"
                       class="whitespace-nowrap px-3 py-1 rounded-full text-xs font-medium transition-all {{ $activeCategory === $slug ? 'bg-gold/20 text-gold-dark border border-gold/30' : 'text-navy/50 hover:bg-navy/5' }}">
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
                <div class="text-center py-20">
                    <div class="text-6xl mb-4">🔍</div>
                    <h2 class="font-heading text-2xl font-bold text-navy mb-2">No guides found</h2>
                    <p class="text-navy/50">Try adjusting your filters or <a href="/guides" class="text-purple hover:text-gold transition-colors underline">view all guides</a>.</p>
                </div>
            @else
                @foreach($guides as $park => $parkGuides)
                    <div class="{{ !$loop->first ? 'mt-12' : '' }}">
                        <div class="flex items-center gap-3 mb-6">
                            <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy">
                                {{ \App\Models\Guide::PARKS[$park] ?? $park }}
                            </h2>
                            <span class="bg-navy/10 text-navy/50 text-xs font-semibold px-2.5 py-1 rounded-full">{{ $parkGuides->count() }} {{ Str::plural('guide', $parkGuides->count()) }}</span>
                        </div>

                        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($parkGuides as $guide)
                                <a href="/guides/{{ $guide->slug }}" class="group bg-white rounded-2xl p-6 shadow-sm border border-navy/5 hover:shadow-lg hover:border-gold/20 hover:-translate-y-1 transition-all duration-300">
                                    @if($guide->icon)
                                        <div class="text-3xl mb-3">{{ $guide->icon }}</div>
                                    @endif
                                    <h3 class="font-heading text-lg font-bold text-navy group-hover:text-purple transition-colors">{{ $guide->title }}</h3>
                                    @if($guide->excerpt)
                                        <p class="text-navy/50 text-sm mt-2 leading-relaxed line-clamp-3">{{ $guide->excerpt }}</p>
                                    @endif
                                    <div class="flex flex-wrap gap-2 mt-4">
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-purple/10 text-purple">{{ $guide->park_label }}</span>
                                        <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-gold/15 text-gold-dark">{{ $guide->category_label }}</span>
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
