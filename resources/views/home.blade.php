@extends('layouts.app')

@section('title', 'Mouse28 — Disney Parks Through Different Eyes')

@section('content')
    <style>
        @keyframes heroGradient {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }
        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        @keyframes gentlePulse {
            0%, 100% { box-shadow: 0 4px 15px rgba(212,168,67,0.2); }
            50% { box-shadow: 0 4px 25px rgba(212,168,67,0.45); }
        }
        @keyframes successPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.03); }
            100% { transform: scale(1); }
        }
        .hero-animated-bg {
            background: linear-gradient(270deg, #1a1040, #2d1b69, #1a1040, #3a2370);
            background-size: 400% 400%;
            animation: heroGradient 12s ease infinite;
        }
        .cta-primary {
            animation: gentlePulse 3s ease-in-out infinite;
        }
        .cta-primary:hover {
            animation: none;
        }
        .card-shimmer::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(105deg, transparent 40%, rgba(212,168,67,0.08) 50%, transparent 60%);
            transform: translateX(-100%);
            transition: none;
        }
        .group:hover .card-shimmer::after {
            animation: shimmer 0.8s ease forwards;
        }
        /* Card hover enhancements */
        .post-card {
            border-color: rgba(26,16,64,0.05);
            transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .post-card:hover {
            border-color: rgba(212,168,67,0.3);
            background: linear-gradient(to bottom, #fff, #fffdf8);
        }
        .post-card .card-img {
            transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .post-card:hover .card-img {
            transform: scale(1.07);
        }
        .post-card .card-overlay {
            opacity: 0;
            transition: opacity 0.35s ease;
        }
        .post-card:hover .card-overlay {
            opacity: 1;
        }
        /* Featured post overlay */
        .featured-link .featured-overlay {
            background: linear-gradient(to t, rgba(26,16,64,0.95), rgba(26,16,64,0.7), rgba(26,16,64,0.2));
            transition: background 0.5s ease;
        }
        .featured-link:hover .featured-overlay {
            background: linear-gradient(to top, rgba(26,16,64,0.95), rgba(45,27,105,0.75), rgba(91,62,158,0.25));
        }
        /* Newsletter input glow */
        .newsletter-input:focus {
            box-shadow: 0 0 0 3px rgba(212,168,67,0.25), 0 0 20px rgba(212,168,67,0.1);
        }
        /* Scroll animations with stagger support */
        [data-animate] {
            opacity: 1;
            transform: translateY(0);
        }
        .js-animate [data-animate] {
            opacity: 0;
            transform: translateY(24px);
            transition: opacity 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        .js-animate [data-animate].is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        /* Wave dividers — smoother rendering */
        .wave-divider svg {
            display: block;
            filter: none;
        }
    </style>

    {{-- Hero Section --}}
    <section class="relative hero-animated-bg overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[15%] left-[10%] text-gold/40 text-xs">✦</span>
            <span class="sparkle-delay absolute top-[25%] right-[15%] text-gold/30 text-sm">✧</span>
            <span class="sparkle-delay-2 absolute top-[60%] left-[20%] text-gold/20 text-xs">✦</span>
            <span class="sparkle absolute top-[40%] right-[8%] text-gold/25 text-lg">✧</span>
            <span class="sparkle-delay absolute bottom-[20%] left-[40%] text-gold/30 text-xs">✦</span>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-24 md:py-36 text-center relative z-10">
            <span class="inline-block text-gold/80 text-sm font-semibold tracking-[0.15em] uppercase mb-6 font-body">Autism Family · Disney Every Week</span>
            <h1 class="font-heading text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.1] mb-6">
                Disney Parks Through<br>
                <span class="text-gold">Different Eyes</span>
            </h1>
            <p class="text-white/65 text-lg md:text-xl max-w-2xl mx-auto mb-8 leading-[1.7] font-body">
                Accessibility tips, sensory guides, and real stories from a family who visits Disney every week with our autistic daughter.
            </p>
            @if($guidesCount > 0 || $storiesCount > 0)
                <div class="flex items-center justify-center gap-6 mb-10 text-white/40 text-sm font-body">
                    @if($guidesCount > 0)
                        <span>📖 {{ $guidesCount }} {{ Str::plural('guide', $guidesCount) }} published</span>
                    @endif
                    @if($storiesCount > 0)
                        <span>💜 {{ $storiesCount }} {{ Str::plural('family', $storiesCount) }} sharing stories</span>
                    @endif
                </div>
            @endif
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/blog" class="cta-primary bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-4 min-h-[48px] rounded-full shadow-lg shadow-gold/20 hover:shadow-gold/50 hover:scale-105 transition-all duration-300 hover:-translate-y-1 text-base font-body inline-flex items-center">
                    Read the Blog
                </a>
                @if($latestEpisodes->count())
                    <a href="/episodes/{{ $latestEpisodes->first()->slug }}" class="bg-white/10 backdrop-blur-sm text-white/90 hover:text-white hover:bg-white/20 font-semibold px-8 py-4 min-h-[48px] rounded-full border border-white/20 hover:border-gold/40 transition-all duration-300 hover:-translate-y-1 hover:scale-105 text-base font-body inline-flex items-center">
                        🎧 Latest Episode
                    </a>
                @endif
            </div>
            <p class="mt-8 text-white/40 text-sm font-body">Also a podcast — <a href="/episodes" class="text-white/65 hover:text-gold underline underline-offset-2 transition-colors">listen to the show →</a></p>
        </div>

        {{-- Wave divider --}}
        <div class="wave-divider absolute bottom-0 left-0 right-0" aria-hidden="true">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block" preserveAspectRatio="none">
                <path d="M0 60V30Q360 0 720 25Q1080 50 1440 20V60H0Z" fill="#1a1040"/>
            </svg>
        </div>
    </section>

    {{-- Featured Post --}}
    @if($featuredPost)
        <section class="bg-navy" data-animate>
            <a href="/blog/{{ $featuredPost->slug }}" class="featured-link group block relative overflow-hidden">
                <div class="max-w-7xl mx-auto">
                    <div class="relative min-h-[400px] md:min-h-[500px] flex items-end">
                        @if($featuredPost->cover_image)
                            <img src="{{ $featuredPost->cover_image }}" alt="{{ $featuredPost->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="featured-overlay absolute inset-0 bg-gradient-to-t from-navy via-navy/70 to-navy/20"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-navy-light via-purple/30 to-navy"></div>
                        @endif
                        <div class="relative z-10 p-8 md:p-14 max-w-3xl border-l-4 border-gold ml-4 md:ml-10">
                            <div class="flex items-center gap-3 mb-4 flex-wrap">
                                <span class="bg-gold/90 text-navy text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider font-body">Featured</span>
                                @if($featuredPost->category)
                                    <span class="bg-white/15 backdrop-blur-sm text-white/80 text-xs font-semibold px-3 py-1 rounded-full font-body">{{ $featuredPost->category_label }}</span>
                                @endif
                                <span class="text-white/40 text-sm font-body">{{ $featuredPost->published_at->format('M j, Y') }}</span>
                                <span class="bg-white/10 backdrop-blur-sm text-white/65 text-xs font-semibold px-3 py-1 rounded-full font-body">{{ $featuredPost->reading_time }} min read</span>
                            </div>
                            <h2 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white leading-tight mb-4 group-hover:text-gold transition-colors duration-300">
                                {{ $featuredPost->title }}
                            </h2>
                            @if($featuredPost->excerpt)
                                <p class="text-white/65 text-lg leading-relaxed line-clamp-2 font-body">{{ $featuredPost->excerpt }}</p>
                            @endif
                            <span class="inline-flex items-center gap-2 mt-6 text-gold font-semibold text-sm font-body group-hover:gap-3 transition-all">
                                Read article
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </section>
    @endif

    {{-- Wave: Navy → Cream --}}
    <div class="bg-cream" aria-hidden="true">
        <svg viewBox="0 0 1440 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block -mt-px">
            <path d="M0 0V28C360 0 720 48 1080 24C1260 12 1380 4 1440 0V0H0Z" fill="#1a1040"/>
        </svg>
    </div>

    {{-- Latest Posts Grid --}}
    <section class="py-16 md:py-24 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-end justify-between mb-12" data-animate>
                <div>
                    <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">Latest Stories</span>
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Tips, Guides &amp; Disney Life</h2>
                </div>
                <a href="/blog" class="hidden sm:inline-flex items-center gap-1 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                    View all
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if($latestPosts->count())
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($latestPosts as $post)
                        <a href="/blog/{{ $post->slug }}" class="post-card group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl hover:-translate-y-2 border border-navy/5 relative" data-animate data-stagger="{{ $loop->index }}"
                            <div class="relative overflow-hidden card-shimmer">
                                @if($post->cover_image)
                                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="card-img w-full h-52 object-cover">
                                    <div class="card-overlay absolute inset-0 bg-gradient-to-t from-purple/20 to-transparent"></div>
                                @else
                                    <div class="w-full h-52 bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center">
                                        <span class="text-3xl">✨</span>
                                    </div>
                                @endif
                                @if($post->category)
                                    <span class="absolute top-3 left-3 bg-navy/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider font-body">{{ $post->category_label }}</span>
                                @endif
                            </div>
                            <div class="p-6">
                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-navy/40 text-xs font-body">{{ $post->published_at->format('M j, Y') }}</span>
                                    <span class="text-navy/40 text-xs font-body">{{ $post->reading_time }} min read</span>
                                </div>
                                <h3 class="font-heading text-xl font-bold text-navy group-hover:text-purple transition-colors duration-200 mb-2 leading-snug">{{ $post->title }}</h3>
                                @if($post->excerpt)
                                    <p class="text-navy/65 text-sm leading-[1.7] line-clamp-2 font-body mb-4">{{ Str::limit($post->excerpt, 130) }}</p>
                                @endif
                                <div class="flex items-center gap-2 pt-3 border-t border-navy/5">
                                    <div class="w-7 h-7 rounded-full bg-purple/10 flex items-center justify-center text-purple text-[10px] font-bold flex-shrink-0">
                                        {{ collect(explode(' ', $post->author_name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('') }}
                                    </div>
                                    <span class="text-navy/40 text-xs font-medium font-body">{{ $post->author_name }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-10 sm:hidden">
                    <a href="/blog" class="text-purple hover:text-navy font-semibold text-sm transition-colors font-body">View all posts →</a>
                </div>
            @else
                <div class="text-center py-16 bg-white rounded-2xl border border-navy/5">
                    <span class="text-4xl mb-4 block">📝</span>
                    <p class="text-navy/65 text-lg font-body">Blog posts are on the way!</p>
                    <p class="text-navy/40 text-sm mt-2 font-body">We're writing up our best Disney tips and stories.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Guides Teaser --}}
    @if($popularGuides->count())
        <section class="py-14 md:py-20 bg-cream">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div class="flex items-end justify-between mb-10" data-animate>
                    <div>
                        <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">📖 Essential Reading</span>
                        <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-1">Park Guides</h2>
                    </div>
                    <a href="/guides" class="hidden sm:inline-flex items-center gap-1 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                        All guides
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach($popularGuides as $guide)
                        <a href="/guides/{{ $guide->slug }}" class="group bg-white rounded-xl p-5 border border-navy/5 hover:border-gold/30 hover:shadow-lg transition-all duration-300 hover:-translate-y-1" data-animate>
                            <div class="flex items-start gap-3 mb-3">
                                @if($guide->icon)
                                    <span class="text-2xl flex-shrink-0">{{ $guide->icon }}</span>
                                @else
                                    <span class="text-2xl flex-shrink-0">📋</span>
                                @endif
                                <div class="min-w-0">
                                    @if($guide->park)
                                        <span class="text-purple/60 text-xs font-semibold uppercase tracking-wider font-body">{{ $guide->park }}</span>
                                    @endif
                                    <h3 class="font-heading text-base font-semibold text-navy group-hover:text-purple transition-colors leading-snug">{{ $guide->title }}</h3>
                                </div>
                            </div>
                            @if($guide->excerpt)
                                <p class="text-navy/50 text-sm leading-relaxed line-clamp-2 font-body">{{ Str::limit($guide->excerpt, 100) }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-8 sm:hidden">
                    <a href="/guides" class="text-purple hover:text-navy font-semibold text-sm transition-colors font-body">All guides →</a>
                </div>
            </div>
        </section>
    @endif

    {{-- Wave: Cream → White --}}
    <div class="bg-white" aria-hidden="true">
        <svg viewBox="0 0 1440 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block -mt-px">
            <path d="M0 0C480 40 960 0 1440 30V40H0V0Z" fill="#fef9ef"/>
        </svg>
    </div>

    {{-- Podcast Section --}}
    <section class="py-16 md:py-24 bg-white relative">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-end justify-between mb-12" data-animate>
                <div>
                    <span class="text-purple/65 text-sm font-semibold tracking-[0.15em] uppercase font-body">🎙️ Also Listen</span>
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">From the Podcast</h2>
                </div>
                <a href="/episodes" class="hidden sm:inline-flex items-center gap-1 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                    All episodes
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if($latestEpisodes->count())
                <div class="divide-y divide-navy/8">
                    @foreach($latestEpisodes as $episode)
                        <a href="/episodes/{{ $episode->slug }}" class="group flex items-center gap-5 py-5 hover:bg-cream/50 -mx-4 px-4 rounded-xl transition-colors duration-200" data-animate>
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-purple/10 flex items-center justify-center group-hover:bg-purple/20 transition-colors relative">
                                <span class="text-purple font-bold text-sm font-body group-hover:opacity-0 transition-opacity">{{ $episode->episode_number }}</span>
                                <svg class="w-5 h-5 text-purple absolute opacity-0 group-hover:opacity-100 transition-opacity" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-heading text-base font-semibold text-navy group-hover:text-purple transition-colors truncate">{{ $episode->title }}</h3>
                                <p class="text-navy/40 text-sm font-body mt-0.5">
                                    {{ $episode->published_at->format('M j, Y') }}
                                    @if($episode->duration_seconds)
                                        <span class="mx-1.5">·</span>{{ $episode->formatted_duration }}
                                    @endif
                                </p>
                            </div>
                            <svg class="w-5 h-5 text-navy/25 group-hover:text-purple group-hover:translate-x-1 transition-all flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-6 sm:hidden">
                    <a href="/episodes" class="text-purple hover:text-navy font-semibold text-sm transition-colors font-body">All episodes →</a>
                </div>
            @else
                <div class="text-center py-10 bg-cream/50 rounded-2xl">
                    <span class="text-3xl mb-3 block">🎙️</span>
                    <p class="text-navy/65 font-body">Our first episode is coming soon!</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Wave: White → Cream --}}
    <div class="bg-cream" aria-hidden="true">
        <svg viewBox="0 0 1440 40" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block -mt-px">
            <path d="M0 0C360 35 720 5 1080 30C1260 42 1380 15 1440 20V40H0V0Z" fill="white"/>
        </svg>
    </div>

    {{-- About the Hosts --}}
    <section class="py-16 md:py-24 bg-cream">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row gap-12 items-center" data-animate>
                <div class="flex-shrink-0">
                    <img src="/images/logo.jpg" alt="Jeffrey & Cassie Davidson" class="w-36 h-36 md:w-44 md:h-44 rounded-2xl shadow-lg object-cover">
                </div>
                <div>
                    <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">Meet the Family</span>
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2 mb-5">Jeffrey &amp; Cassie Davidson</h2>
                    <div class="relative pl-8 mb-4">
                        <span class="absolute left-0 top-0 font-heading text-5xl text-gold/25 leading-none select-none" aria-hidden="true">"</span>
                        <p class="text-navy/75 text-lg italic leading-relaxed font-body">
                            She's taught us to see magic in ways we never imagined.
                        </p>
                        <span class="absolute -bottom-2 right-0 font-heading text-5xl text-gold/25 leading-none select-none" aria-hidden="true">"</span>
                    </div>
                    <p class="text-navy/65 leading-[1.7] font-body max-w-xl">
                        We're a Central Florida family who visits Disney every week. Our daughter Viola has autism and experiences the parks differently. Mouse'28 is where we share what we've learned: DAS tips, sensory-friendly spots, and the joy that makes it all worth it.
                    </p>
                    <a href="/about" class="inline-flex items-center gap-1 mt-4 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                        Our full story
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Wave: Cream → Navy --}}
    <div class="bg-gradient-to-br from-navy via-navy-light to-navy" aria-hidden="true">
        <svg viewBox="0 0 1440 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto block -mt-px">
            <path d="M0 0C480 48 960 0 1440 36V48H0V0Z" fill="#fef9ef"/>
        </svg>
    </div>

    {{-- Newsletter CTA --}}
    <section class="py-16 md:py-24 bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[20%] left-[15%] text-gold/30 text-sm">✦</span>
            <span class="sparkle-delay absolute bottom-[25%] right-[20%] text-gold/20 text-lg">✧</span>
            <span class="sparkle-delay-2 absolute top-[50%] left-[70%] text-gold/15 text-xs">✦</span>
        </div>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center relative z-10" data-animate>
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-white mb-4">Get Disney Tips in Your Inbox</h2>
            <p class="text-white/65 text-lg mb-8 leading-[1.7] font-body">Weekly park tips, accessibility guides, and family stories. No spam, just pixie dust.</p>
            <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                @csrf
                <input type="email" name="email" placeholder="your@email.com" required
                    class="newsletter-input flex-1 px-5 py-3.5 min-h-[48px] rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-gold/60 focus:border-gold/40 text-sm font-body transition-all duration-300">
                <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold px-7 py-3.5 min-h-[48px] rounded-full transition-all duration-300 text-sm font-body hover:shadow-lg hover:shadow-gold/30 hover:-translate-y-0.5 hover:scale-105 active:scale-95">
                    Subscribe
                </button>
            </form>
            <div class="flex items-center justify-center gap-6 mt-10 pt-8 border-t border-white/10">
                <a href="#" class="text-white/40 hover:text-white/65 text-sm font-body transition-colors">🎧 Apple Podcasts</a>
                <a href="#" class="text-white/40 hover:text-white/65 text-sm font-body transition-colors">💚 Spotify</a>
                <a href="#" class="text-white/40 hover:text-white/65 text-sm font-body transition-colors">▶️ YouTube</a>
            </div>
        </div>
    </section>

    {{-- Scroll animation observer --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.documentElement.classList.add('js-animate');

            // Staggered scroll animations
            const obs = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) {
                        const stagger = e.target.dataset.stagger;
                        const delay = stagger ? parseInt(stagger) * 120 : 0;
                        setTimeout(() => e.target.classList.add('is-visible'), delay);
                        obs.unobserve(e.target);
                    }
                });
            }, { threshold: 0.08, rootMargin: '0px 0px -60px 0px' });
            document.querySelectorAll('[data-animate]').forEach(el => obs.observe(el));

            // Subtle hero parallax on scroll
            const hero = document.querySelector('.hero-animated-bg');
            if (hero) {
                let ticking = false;
                window.addEventListener('scroll', function() {
                    if (!ticking) {
                        requestAnimationFrame(function() {
                            const scroll = window.scrollY;
                            if (scroll < 800) {
                                hero.style.transform = 'translateY(' + (scroll * 0.15) + 'px)';
                                hero.style.opacity = Math.max(0.3, 1 - scroll / 900);
                            }
                            ticking = false;
                        });
                        ticking = true;
                    }
                }, { passive: true });
            }
        });
    </script>
@endsection
