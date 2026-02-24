@extends('layouts.app')

@section('title', $guide->title . ' — Mouse28 Accessibility Guide')
@section('meta_description', Str::limit($guide->excerpt, 160))

@section('content')
    {{-- Print styles --}}
    <style>
        @media print {
            nav, footer, .no-print { display: none !important; }
            body { background: white !important; }
            .print-only { display: block !important; }
            article { box-shadow: none !important; border: none !important; padding: 0 !important; }
            a { color: inherit !important; text-decoration: none !important; }
            .prose { font-size: 11pt; }
        }
    </style>

    {{-- Header --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20 no-print">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            {{-- Breadcrumbs --}}
            <nav class="flex items-center gap-2 text-sm mb-6" aria-label="Breadcrumb">
                <a href="/guides" class="text-white/40 hover:text-gold transition-colors">Guides</a>
                <svg class="w-3 h-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="/guides?park={{ $guide->park }}" class="text-white/40 hover:text-gold transition-colors">{{ $guide->park_label }}</a>
                <svg class="w-3 h-3 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-white/60">{{ Str::limit($guide->title, 40) }}</span>
            </nav>

            <div class="max-w-3xl">
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
                    {{-- Print & utility bar --}}
                    <div class="flex items-center justify-end gap-3 mb-4 no-print">
                        <button onclick="window.print()" class="inline-flex items-center gap-2 text-sm text-navy/40 hover:text-purple transition-colors bg-white px-4 py-2 rounded-xl border border-navy/10 hover:border-purple/20">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Print
                        </button>
                    </div>

                    {{-- Table of Contents (auto-generated) --}}
                    <div id="toc-container" class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5 mb-6 hidden">
                        <h3 class="font-heading text-lg font-bold text-navy mb-3 flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            In This Guide
                        </h3>
                        <nav id="toc-nav" class="space-y-1"></nav>
                    </div>

                    <article class="bg-white rounded-2xl p-8 md:p-12 shadow-sm border border-navy/5">
                        <div id="guide-body" class="prose prose-lg prose-navy max-w-none text-navy/80 leading-relaxed prose-headings:font-heading prose-headings:text-navy prose-a:text-purple prose-a:no-underline hover:prose-a:underline prose-li:marker:text-purple prose-img:rounded-xl prose-blockquote:border-purple prose-blockquote:bg-purple/5 prose-blockquote:rounded-r-xl prose-blockquote:py-1">
                            {!! $guide->body !!}
                        </div>
                    </article>

                    {{-- Was this helpful? --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5 mt-6 no-print">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <p class="font-heading text-lg font-bold text-navy">Was this guide helpful?</p>
                            <div class="flex items-center gap-3">
                                <button onclick="this.classList.add('ring-2','ring-green-300');this.nextElementSibling.classList.remove('ring-2','ring-red-300')" class="inline-flex items-center gap-2 bg-green-50 hover:bg-green-100 text-green-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-all border border-green-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                                    Yes, helpful!
                                </button>
                                <button onclick="this.classList.add('ring-2','ring-red-300');this.previousElementSibling.classList.remove('ring-2','ring-green-300')" class="inline-flex items-center gap-2 bg-red-50 hover:bg-red-100 text-red-700 px-5 py-2.5 rounded-xl text-sm font-medium transition-all border border-red-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"/></svg>
                                    Needs work
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[32%] space-y-6 no-print">
                    {{-- Park Info Card --}}
                    <div class="bg-gradient-to-br from-purple/5 to-gold/5 rounded-2xl p-6 border border-purple/10">
                        <div class="flex items-center gap-3 mb-3">
                            @if($guide->icon)<span class="text-3xl">{{ $guide->icon }}</span>@endif
                            <div>
                                <p class="font-heading font-bold text-navy">{{ $guide->park_label }}</p>
                                <p class="text-navy/40 text-xs">{{ $guide->category_label }}</p>
                            </div>
                        </div>
                        <a href="/guides?park={{ $guide->park }}" class="inline-flex items-center gap-1 text-sm text-purple font-medium hover:text-purple-dark transition-colors mt-2">
                            View all {{ $guide->park_label }} guides
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>

                    {{-- Related Guides --}}
                    @if($relatedGuides->isNotEmpty())
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <h3 class="font-heading text-lg font-bold text-navy mb-4">Related Guides</h3>
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

    {{-- TOC generation script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.getElementById('guide-body');
            const tocContainer = document.getElementById('toc-container');
            const tocNav = document.getElementById('toc-nav');
            if (!body || !tocNav) return;

            const headings = body.querySelectorAll('h2');
            if (headings.length < 2) return;

            tocContainer.classList.remove('hidden');
            headings.forEach(function(h, i) {
                const id = 'section-' + i;
                h.id = id;
                const link = document.createElement('a');
                link.href = '#' + id;
                link.className = 'block text-sm text-navy/60 hover:text-purple transition-colors py-1.5 px-3 rounded-lg hover:bg-purple/5';
                link.textContent = h.textContent;
                tocNav.appendChild(link);
            });
        });
    </script>
@endsection
