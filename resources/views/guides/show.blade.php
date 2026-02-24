@extends('layouts.app')

@section('title', $guide->title . ' — Mouse28 Accessibility Guide')
@section('meta_description', Str::limit($guide->excerpt, 160))

@section('content')
    {{-- Header --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <a href="/guides{{ $guide->park !== 'general' ? '?park='.$guide->park : '' }}" class="text-white/50 hover:text-gold text-sm transition-colors mb-6 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                All Guides
            </a>
            <div class="mt-4 max-w-3xl">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    <span class="text-sm font-semibold px-4 py-1 rounded-full bg-purple/30 text-white">{{ $guide->park_label }}</span>
                    <span class="text-sm font-semibold px-4 py-1 rounded-full bg-gold/30 text-gold-light">{{ $guide->category_label }}</span>
                </div>
                <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white">
                    @if($guide->icon) {{ $guide->icon }} @endif{{ $guide->title }}
                </h1>
                @if($guide->excerpt)
                    <p class="text-white/60 text-lg mt-4 leading-relaxed">{{ $guide->excerpt }}</p>
                @endif
            </div>
        </div>
    </section>

    {{-- Content --}}
    <section class="py-12 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col lg:flex-row gap-10">
                {{-- Main Content --}}
                <div class="lg:w-[68%]">
                    <article class="bg-white rounded-2xl p-8 md:p-12 shadow-sm border border-navy/5">
                        <div class="prose prose-lg prose-navy max-w-none text-navy/80 leading-relaxed">
                            {!! $guide->body !!}
                        </div>
                    </article>
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[32%] space-y-6">
                    {{-- Related Guides --}}
                    @if($relatedGuides->isNotEmpty())
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <h3 class="font-heading text-lg font-bold text-navy mb-4">More {{ $guide->park_label }} Guides</h3>
                            <div class="space-y-3">
                                @foreach($relatedGuides as $related)
                                    <a href="/guides/{{ $related->slug }}" class="group flex items-start gap-3 p-2 -mx-2 rounded-lg hover:bg-cream transition-colors">
                                        @if($related->icon)
                                            <span class="text-xl mt-0.5">{{ $related->icon }}</span>
                                        @endif
                                        <div>
                                            <span class="text-sm font-medium text-navy group-hover:text-purple transition-colors">{{ $related->title }}</span>
                                            <span class="block text-xs text-navy/40 mt-0.5">{{ $related->category_label }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Browse by Category --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                        <h3 class="font-heading text-lg font-bold text-navy mb-4">Browse by Category</h3>
                        <div class="space-y-2">
                            @foreach($categories as $slug => $label)
                                <a href="/guides?category={{ $slug }}" class="flex items-center justify-between p-2 -mx-2 rounded-lg hover:bg-cream transition-colors group">
                                    <span class="text-sm text-navy/70 group-hover:text-purple transition-colors">{{ $label }}</span>
                                    <svg class="w-4 h-4 text-navy/20 group-hover:text-purple transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- CTA --}}
                    <div class="bg-gradient-to-br from-purple to-navy rounded-2xl p-6 text-white">
                        <h3 class="font-heading text-lg font-bold mb-2">Share Your Tips!</h3>
                        <p class="text-white/70 text-sm leading-relaxed mb-4">Have accessibility tips from your Disney visits? We'd love to hear from you.</p>
                        <a href="/contact" class="inline-block bg-gold hover:bg-gold-light text-navy font-semibold text-sm px-5 py-2.5 rounded-full transition-all hover:shadow-lg hover:-translate-y-0.5">
                            Get in Touch
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
@endsection
