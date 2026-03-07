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

    {{-- Page header --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-14">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <span class="text-gold text-xs font-semibold tracking-widest uppercase">Stories & Tips</span>
            <h1 class="font-heading text-3xl md:text-4xl font-bold text-white mt-2">Blog</h1>
            <p class="text-white/40 mt-3">Disney tips, park guides, and stories from our family to yours.</p>
        </div>
    </section>

    <section class="py-16 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">

            {{-- Featured post + grid layout --}}

            {{-- Featured: B-style two-column with excerpt --}}
            @php $feat = $demoPosts[0]; $fColor = $categoryColors[$feat->category] ?? '#5b3e9e'; @endphp
            <style>
                @keyframes borderRotate {
                    0% { --border-angle: 0deg; }
                    100% { --border-angle: 360deg; }
                }
                @property --border-angle {
                    syntax: '<angle>';
                    initial-value: 0deg;
                    inherits: false;
                }
                .featured-wrapper {
                    position: relative;
                    z-index: 1;
                }
                .featured-wrapper::before {
                    content: '';
                    position: absolute;
                    inset: -2px;
                    border-radius: 1.625rem;
                    background: conic-gradient(from var(--border-angle), #d4a843, #7b5eb5, #4a90a4, #d4a843);
                    animation: borderRotate 6s linear infinite;
                    z-index: 0;
                    opacity: 0.5;
                }
                .featured-card-border {
                    position: relative;
                    z-index: 1;
                    background: linear-gradient(135deg, #1a1040, #2d1b69);
                    border-radius: 1.5rem;
                    color: white;
                    overflow: hidden;
                }
            </style>
            <div class="mb-8 featured-wrapper rounded-3xl">
                {{-- Corner ribbon --}}
                <div class="ribbon ribbon-top-left"><span>Featured</span></div>

                <a href="#" class="featured-card-border group block transition-all duration-300">
                    {{-- Corner ribbon styles --}}
                    <style>
                        /* Ribbon: exact nxworld CodePen — only colors changed */
                        .ribbon {
                            width: 150px;
                            height: 150px;
                            overflow: hidden;
                            position: absolute;
                            z-index: 30;
                            pointer-events: none;
                        }
                        .ribbon::before,
                        .ribbon::after {
                            position: absolute;
                            z-index: -1;
                            content: '';
                            display: block;
                            border: 5px solid #7a5e1e;
                        }
                        .ribbon span {
                            position: absolute;
                            display: block;
                            width: 225px;
                            padding: 15px 0;
                            background-color: #d4a843;
                            box-shadow: 0 5px 10px rgba(0,0,0,.1);
                            color: #1a1040;
                            font: 700 18px/1 'Poppins', sans-serif;
                            text-shadow: 0 1px 1px rgba(0,0,0,.2);
                            text-transform: uppercase;
                            text-align: center;
                        }
                        .ribbon-top-left {
                            top: -10px;
                            left: -10px;
                        }
                        .ribbon-top-left::before,
                        .ribbon-top-left::after {
                            border-top-color: transparent;
                            border-left-color: transparent;
                        }
                        .ribbon-top-left::before {
                            top: 0;
                            right: 0;
                        }
                        .ribbon-top-left::after {
                            bottom: 0;
                            left: 0;
                        }
                        .ribbon-top-left span {
                            right: -25px;
                            top: 30px;
                            transform: rotate(-45deg);
                        }
                    </style>
                    <div class="grid md:grid-cols-5 min-h-[280px] relative">
                        {{-- Excerpt side --}}
                        <div class="md:col-span-2 p-8 md:p-10 pl-12 md:pl-16 pt-24 pb-8 flex flex-col justify-center relative">
                            <p class="text-white/70 text-sm md:text-base leading-relaxed relative z-10">
                                {{ $feat->excerpt }}
                            </p>
                        </div>
                        {{-- Content side --}}
                        <div class="md:col-span-3 p-8 md:p-10 flex flex-col justify-center">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full" style="background: {{ $fColor }}30; color: {{ $fColor }};">{{ $feat->category_label }}</span>
                                <span class="text-white/30 text-xs">{{ $feat->reading_time }} min read</span>
                            </div>
                            <h2 class="font-heading text-2xl md:text-3xl font-bold text-white group-hover:text-gold transition-colors leading-snug">{{ $feat->title }}</h2>
                            <div class="flex items-center justify-between mt-6 pt-6 border-t border-white/10">
                                <div class="flex items-center gap-4">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-gold/25 to-purple/15 flex items-center justify-center text-gold text-[10px] font-bold font-heading border border-gold/20">
                                        {{ collect(explode(' ', $feat->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                                    </div>
                                    <div>
                                        <p class="text-white text-sm font-semibold">{{ $feat->author_name }}</p>
                                        <p class="text-white/40 text-xs">{{ $feat->published_at->format('F j, Y') }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex items-center gap-1.5 text-gold text-sm font-semibold group-hover:gap-2.5 transition-all">
                                    Read Article
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
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
