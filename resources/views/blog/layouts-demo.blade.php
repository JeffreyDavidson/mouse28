@extends('layouts.app')

@section('title', 'Blog Layout Options — Mouse28')

@section('content')
    @php
        // Fake posts for demo
        $demoPosts = [
            (object)[
                'title' => 'Welcome to Mouse28',
                'excerpt' => 'Meet Jeff, Cassie, and Viola, the family behind Mouse28. From a proposal at Cinderella\'s Royal Table to over a decade as Florida passholders, here\'s how our Disney obsession started and why we\'re sharing it with you.',
                'category' => 'general',
                'category_label' => 'General',
                'author_name' => 'Jeffrey & Cassie',
                'reading_time' => 6,
                'published_at' => now()->subDays(1),
                'slug' => '#',
            ],
            (object)[
                'title' => 'Our Top 10 Sensory-Friendly Spots at Magic Kingdom',
                'excerpt' => 'Finding quiet corners and low-stimulation areas in the busiest theme park on Earth. These are the spots that saved our park days.',
                'category' => 'park-accessibility',
                'category_label' => 'Park Accessibility',
                'author_name' => 'Jeffrey & Cassie',
                'reading_time' => 8,
                'published_at' => now()->subDays(3),
                'slug' => '#',
            ],
            (object)[
                'title' => 'The Best Quick Service Meals Under $15 at EPCOT',
                'excerpt' => 'You don\'t need a sit-down reservation to eat well at EPCOT. Here are our go-to quick service picks that won\'t break the bank.',
                'category' => 'food-reviews',
                'category_label' => 'Food Reviews',
                'author_name' => 'Cassie Davidson',
                'reading_time' => 5,
                'published_at' => now()->subDays(5),
                'slug' => '#',
            ],
            (object)[
                'title' => 'DAS Pass Changes: What Families Need to Know in 2026',
                'excerpt' => 'Disney updated the Disability Access Service again. We break down what changed, who qualifies, and how to navigate the new process.',
                'category' => 'disney-news',
                'category_label' => 'Disney News',
                'author_name' => 'Jeffrey Davidson',
                'reading_time' => 10,
                'published_at' => now()->subDays(7),
                'slug' => '#',
            ],
            (object)[
                'title' => 'Why We Still Go Every Week After 10 Years',
                'excerpt' => 'People ask us all the time if we get bored. The honest answer might surprise you.',
                'category' => 'family-life',
                'category_label' => 'Family Life',
                'author_name' => 'Jeffrey & Cassie',
                'reading_time' => 4,
                'published_at' => now()->subDays(10),
                'slug' => '#',
            ],
        ];

        $categoryColors = [
            'disney-tips' => '#d4a843',
            'park-accessibility' => '#7b5eb5',
            'episode-recap' => '#22c55e',
            'family-life' => '#3b82f6',
            'autism-awareness' => '#ec4899',
            'disney-news' => '#f97316',
            'food-reviews' => '#f59e0b',
            'resort-reviews' => '#14b8a6',
            'disney-plus' => '#6366f1',
            'merchandise' => '#f43f5e',
            'general' => '#4a90a4',
        ];

        $categoryGradients = [
            'disney-tips' => ['from' => '#d4a843', 'to' => '#f0c75e', 'icon' => '🏰'],
            'park-accessibility' => ['from' => '#7b5eb5', 'to' => '#a78bfa', 'icon' => '💜'],
            'episode-recap' => ['from' => '#059669', 'to' => '#34d399', 'icon' => '🎙️'],
            'family-life' => ['from' => '#2563eb', 'to' => '#60a5fa', 'icon' => '👨‍👩‍👧'],
            'autism-awareness' => ['from' => '#db2777', 'to' => '#f472b6', 'icon' => '🧩'],
            'disney-news' => ['from' => '#ea580c', 'to' => '#fb923c', 'icon' => '📰'],
            'food-reviews' => ['from' => '#d97706', 'to' => '#fbbf24', 'icon' => '🍽️'],
            'resort-reviews' => ['from' => '#0d9488', 'to' => '#5eead4', 'icon' => '🏨'],
            'disney-plus' => ['from' => '#4f46e5', 'to' => '#818cf8', 'icon' => '📺'],
            'merchandise' => ['from' => '#e11d48', 'to' => '#fb7185', 'icon' => '🛍️'],
            'general' => ['from' => '#4a90a4', 'to' => '#7bc4d4', 'icon' => '✨'],
        ];
        $defaultGrad = ['from' => '#5b3e9e', 'to' => '#7c5cbf', 'icon' => '✨'];
    @endphp

    <style>
        .option-label {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: linear-gradient(135deg, #1a1040, #2d1b69);
            color: #d4a843; font-family: 'Poppins', sans-serif;
            font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em;
            text-transform: uppercase; padding: 0.5rem 1.25rem;
            border-radius: 9999px; border: 1px solid rgba(212,168,67,0.3);
        }
        .option-desc {
            color: rgba(26,16,64,0.5); font-size: 0.9rem; max-width: 600px;
            margin: 0.75rem auto 2rem; line-height: 1.7; text-align: center;
        }
        .demo-divider {
            height: 1px; margin: 4rem 0;
            background: linear-gradient(90deg, transparent, rgba(212,168,67,0.3), transparent);
        }
    </style>

    {{-- Page header --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-14">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <span class="text-gold text-xs font-semibold tracking-widest uppercase">Layout Options</span>
            <h1 class="font-heading text-3xl md:text-4xl font-bold text-white mt-2">Blog Post List Designs</h1>
            <p class="text-white/40 mt-3">Pick the one that feels right. All shown without cover images.</p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- OPTION A: Full-width editorial cards --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div class="text-center mb-2">
                <span class="option-label">A</span>
            </div>
            <h2 class="font-heading text-2xl font-bold text-navy text-center">Full-Width Editorial</h2>
            <p class="option-desc">Text-forward, magazine feel. Each post is a full-width card with a bold category accent stripe. Clean and confident without needing images.</p>

            <div class="space-y-5 max-w-4xl mx-auto">
                @foreach($demoPosts as $i => $post)
                    @php $color = $categoryColors[$post->category] ?? '#5b3e9e'; @endphp
                    <a href="#" class="group block bg-white rounded-2xl overflow-hidden shadow-sm border border-navy/5 transition-all duration-300 hover:shadow-xl hover:-translate-y-1" style="border-left: 4px solid {{ $color }};">
                        <div class="p-6 md:p-8 flex flex-col md:flex-row md:items-center gap-4 md:gap-8">
                            {{-- Left: category + reading time --}}
                            <div class="flex md:flex-col items-center md:items-start gap-3 md:gap-1 md:w-32 flex-shrink-0">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background: {{ $color }}15; color: {{ $color }};">{{ $post->category_label }}</span>
                                <span class="text-navy/25 text-xs">{{ $post->reading_time }} min read</span>
                            </div>
                            {{-- Center: title + excerpt --}}
                            <div class="flex-1 min-w-0">
                                <h3 class="font-heading text-xl font-bold text-navy group-hover:text-purple transition-colors leading-snug">{{ $post->title }}</h3>
                                <p class="text-navy/45 text-sm leading-relaxed mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                            </div>
                            {{-- Right: author + date --}}
                            <div class="flex md:flex-col items-center md:items-end gap-3 md:gap-1 flex-shrink-0 text-right">
                                <span class="text-navy/60 text-sm font-medium">{{ $post->author_name }}</span>
                                <span class="text-navy/25 text-xs">{{ $post->published_at->format('M j, Y') }}</span>
                            </div>
                            {{-- Arrow --}}
                            <svg class="w-5 h-5 text-navy/15 group-hover:text-gold group-hover:translate-x-1 transition-all flex-shrink-0 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="demo-divider"></div>

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- OPTION B: Magazine pull-quote featured + grid --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div class="text-center mb-2">
                <span class="option-label">B</span>
            </div>
            <h2 class="font-heading text-2xl font-bold text-navy text-center">Pull-Quote Featured + Grid</h2>
            <p class="option-desc">The latest post gets a dramatic pull-quote treatment. The rest sit in a clean text-only grid. Each card uses the first line of the excerpt as a visual hook.</p>

            {{-- Featured with pull quote --}}
            @php $feat = $demoPosts[0]; $fColor = $categoryColors[$feat->category] ?? '#5b3e9e'; @endphp
            <a href="#" class="group block bg-white rounded-3xl overflow-hidden shadow-lg shadow-navy/5 border border-navy/5 mb-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1">
                <div class="grid md:grid-cols-5">
                    {{-- Pull quote side --}}
                    <div class="md:col-span-2 p-8 md:p-10 flex items-center relative" style="background: linear-gradient(135deg, {{ $fColor }}08, {{ $fColor }}03);">
                        <div class="absolute top-6 left-8 text-6xl font-heading font-bold leading-none" style="color: {{ $fColor }}15;">&ldquo;</div>
                        <p class="font-heading text-lg md:text-xl italic leading-relaxed relative z-10 line-clamp-4" style="color: {{ $fColor }}cc;">
                            {{ Str::limit($feat->excerpt, 140) }}
                        </p>
                    </div>
                    {{-- Content side --}}
                    <div class="md:col-span-3 p-8 md:p-10 flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background: {{ $fColor }}15; color: {{ $fColor }};">{{ $feat->category_label }}</span>
                            <span class="text-navy/25 text-xs">{{ $feat->reading_time }} min read</span>
                        </div>
                        <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy group-hover:text-purple transition-colors leading-snug">{{ $feat->title }}</h2>
                        <div class="flex items-center gap-4 mt-6 pt-5 border-t border-navy/5">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gold/25 to-purple/15 flex items-center justify-center text-gold text-[10px] font-bold font-heading border border-gold/15">
                                {{ collect(explode(' ', $feat->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                            </div>
                            <div>
                                <p class="text-navy text-sm font-semibold">{{ $feat->author_name }}</p>
                                <p class="text-navy/30 text-xs">{{ $feat->published_at->format('F j, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            {{-- Grid --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach(array_slice($demoPosts, 1) as $post)
                    @php $pColor = $categoryColors[$post->category] ?? '#5b3e9e'; @endphp
                    <a href="#" class="group bg-white rounded-2xl p-6 shadow-sm border border-navy/5 transition-all duration-300 hover:shadow-lg hover:-translate-y-1.5 relative overflow-hidden">
                        {{-- Top accent --}}
                        <div class="absolute top-0 left-0 right-0 h-1 transition-transform origin-left duration-300 group-hover:scale-x-100 scale-x-0" style="background: {{ $pColor }};"></div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full inline-block mb-3" style="background: {{ $pColor }}15; color: {{ $pColor }};">{{ $post->category_label }}</span>
                        <h3 class="font-heading text-lg font-bold text-navy group-hover:text-purple transition-colors leading-snug line-clamp-2">{{ $post->title }}</h3>
                        <p class="text-navy/45 text-sm leading-relaxed mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="flex items-center justify-between mt-5 pt-4 border-t border-navy/5">
                            <span class="text-navy/40 text-xs">{{ $post->author_name }}</span>
                            <span class="text-navy/25 text-xs">{{ $post->reading_time }} min</span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="demo-divider"></div>

            {{-- ═══════════════════════════════════════════════ --}}
            {{-- OPTION C: A+B Hybrid (Full-width featured + grid) --}}
            {{-- ═══════════════════════════════════════════════ --}}
            <div class="text-center mb-2">
                <span class="option-label">C</span>
            </div>
            <h2 class="font-heading text-2xl font-bold text-navy text-center">Hybrid: Full-Width Featured + Grid</h2>
            <p class="option-desc">The latest post gets A's full-width editorial treatment with the category accent stripe. Everything else goes into B's text-only grid cards below.</p>

            {{-- Featured: B-style two-column with excerpt --}}
            @php $feat = $demoPosts[0]; $fColor = $categoryColors[$feat->category] ?? '#5b3e9e'; @endphp
            <a href="#" class="group block bg-white rounded-3xl overflow-hidden shadow-lg shadow-navy/5 border border-navy/5 mb-8 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 relative">
                {{-- Top accent bar on hover --}}
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-3xl transition-transform origin-left duration-300 group-hover:scale-x-100 scale-x-0" style="background: {{ $fColor }};"></div>
                <div class="grid md:grid-cols-5 min-h-[280px] relative">
                    {{-- Excerpt side --}}
                    <div class="md:col-span-2 p-8 md:p-10 flex flex-col justify-center relative" style="background: linear-gradient(135deg, {{ $fColor }}12, {{ $fColor }}05);">
                        <span class="text-[10px] font-bold uppercase tracking-widest mb-4 block" style="color: {{ $fColor }};">Featured Post</span>
                        <p class="text-navy/70 text-sm md:text-base leading-relaxed relative z-10">
                            {{ $feat->excerpt }}
                        </p>
                    </div>
                    {{-- Vertical divider --}}
                    <div class="hidden md:block absolute left-[40%] top-6 bottom-6 w-px" style="background: linear-gradient(180deg, transparent, {{ $fColor }}30, transparent);"></div>
                    {{-- Content side --}}
                    <div class="md:col-span-3 p-8 md:p-10 flex flex-col justify-center">
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background: {{ $fColor }}15; color: {{ $fColor }};">{{ $feat->category_label }}</span>
                            <span class="text-navy/25 text-xs">{{ $feat->reading_time }} min read</span>
                        </div>
                        <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy group-hover:text-purple transition-colors leading-snug">{{ $feat->title }}</h2>
                        <div class="flex items-center gap-4 mt-auto pt-6 border-t border-navy/5">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gold/25 to-purple/15 flex items-center justify-center text-gold text-[10px] font-bold font-heading border border-gold/15">
                                {{ collect(explode(' ', $feat->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                            </div>
                            <div>
                                <p class="text-navy text-sm font-semibold">{{ $feat->author_name }}</p>
                                <p class="text-navy/30 text-xs">{{ $feat->published_at->format('F j, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            {{-- Grid: B-style cards --}}
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach(array_slice($demoPosts, 1) as $post)
                    @php $pColor = $categoryColors[$post->category] ?? '#5b3e9e'; @endphp
                    <a href="#" class="group bg-white rounded-2xl p-6 shadow-sm border border-navy/5 transition-all duration-300 hover:shadow-lg hover:-translate-y-1.5 relative overflow-hidden">
                        <div class="absolute top-0 left-0 right-0 h-1 transition-transform origin-left duration-300 group-hover:scale-x-100 scale-x-0" style="background: {{ $pColor }};"></div>
                        <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full inline-block mb-3" style="background: {{ $pColor }}15; color: {{ $pColor }};">{{ $post->category_label }}</span>
                        <h3 class="font-heading text-lg font-bold text-navy group-hover:text-purple transition-colors leading-snug line-clamp-2">{{ $post->title }}</h3>
                        <p class="text-navy/45 text-sm leading-relaxed mt-2 line-clamp-2">{{ $post->excerpt }}</p>
                        <div class="flex items-center justify-between mt-5 pt-4 border-t border-navy/5">
                            <span class="text-navy/40 text-xs">{{ $post->author_name }}</span>
                            <span class="text-navy/25 text-xs">{{ $post->reading_time }} min</span>
                        </div>
                    </a>
                @endforeach
            </div>

        </div>
    </section>
@endsection
