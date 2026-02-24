@extends('layouts.app')

@section('title', 'Mouse28 — Disney Parks Through Different Eyes')

@section('content')
    {{-- Hero Section --}}
    <section class="relative bg-gradient-to-br from-navy via-navy-light to-navy overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[15%] left-[10%] text-gold/40 text-xs">✦</span>
            <span class="sparkle-delay absolute top-[25%] right-[15%] text-gold/30 text-sm">✧</span>
            <span class="sparkle-delay-2 absolute top-[60%] left-[20%] text-gold/20 text-xs">✦</span>
            <span class="sparkle absolute top-[40%] right-[8%] text-gold/25 text-lg">✧</span>
            <span class="sparkle-delay absolute bottom-[20%] left-[40%] text-gold/30 text-xs">✦</span>
            <span class="sparkle-delay-2 absolute top-[10%] left-[60%] text-gold/20 text-sm">✧</span>
            <span class="sparkle absolute top-[70%] right-[25%] text-gold/15 text-xs">✦</span>
            <span class="sparkle-delay-2 absolute top-[5%] left-[35%] text-gold/25 text-sm">✧</span>
        </div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-24 md:py-36 text-center relative z-10">
            <span class="inline-block text-gold/80 text-sm font-semibold tracking-[0.2em] uppercase mb-6 font-body">Weekly Disney Park Visitors · Accessibility Advocates · Storytellers</span>
            <h1 class="font-heading text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.1] mb-6">
                Disney Parks Through<br>
                <span class="text-gold">Different Eyes</span>
            </h1>
            <p class="text-white/65 text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed font-body">
                We're Jeffrey &amp; Cassie — a family who visits Disney every week. Our daughter Viola has autism and sees the parks in ways that changed how we experience magic. We write about tips, accessibility, and the moments in between.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/blog" class="bg-gold hover:bg-gold-light text-navy font-semibold px-8 py-3.5 rounded-full shadow-lg shadow-gold/20 hover:shadow-gold/40 transition-all hover:-translate-y-0.5 text-base font-body">
                    Read the Blog
                </a>
                @if($latestEpisodes->count())
                    <a href="/episodes/{{ $latestEpisodes->first()->slug }}" class="bg-white/10 backdrop-blur-sm text-white/90 hover:text-white hover:bg-white/20 font-semibold px-8 py-3.5 rounded-full border border-white/20 transition-all hover:-translate-y-0.5 text-base font-body">
                        🎧 Latest Episode
                    </a>
                @endif
            </div>
            <p class="mt-8 text-white/35 text-sm font-body">Also a podcast — <a href="/episodes" class="text-white/50 hover:text-gold/70 underline underline-offset-2 transition-colors">listen to the show →</a></p>
        </div>
    </section>

    {{-- Featured Post --}}
    @if($featuredPost)
        <section class="bg-navy">
            <a href="/blog/{{ $featuredPost->slug }}" class="group block relative overflow-hidden">
                <div class="max-w-7xl mx-auto">
                    <div class="relative min-h-[400px] md:min-h-[500px] flex items-end">
                        @if($featuredPost->cover_image)
                            <img src="{{ $featuredPost->cover_image }}" alt="{{ $featuredPost->title }}" class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-t from-navy via-navy/60 to-transparent"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-navy-light via-purple/30 to-navy"></div>
                        @endif
                        <div class="relative z-10 p-8 md:p-14 max-w-3xl">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="bg-gold/90 text-navy text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider font-body">Featured</span>
                                @if($featuredPost->category)
                                    <span class="bg-white/15 backdrop-blur-sm text-white/80 text-xs font-semibold px-3 py-1 rounded-full font-body">{{ str_replace('-', ' ', $featuredPost->category) }}</span>
                                @endif
                                <span class="text-white/50 text-sm font-body">{{ $featuredPost->published_at->format('M j, Y') }}</span>
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

    {{-- Latest Posts Grid --}}
    <section class="py-16 md:py-24 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-end justify-between mb-12">
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
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7">
                    @foreach($latestPosts as $post)
                        <a href="/blog/{{ $post->slug }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-navy/5">
                            <div class="relative overflow-hidden">
                                @if($post->cover_image)
                                    <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-52 object-cover transition-transform duration-500 group-hover:scale-105">
                                @else
                                    <div class="w-full h-52 bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center">
                                        <span class="text-3xl">✨</span>
                                    </div>
                                @endif
                                @if($post->category)
                                    <span class="absolute top-3 left-3 bg-navy/80 backdrop-blur-sm text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider font-body">{{ str_replace('-', ' ', $post->category) }}</span>
                                @endif
                            </div>
                            <div class="p-6">
                                <span class="text-navy/40 text-xs font-body">{{ $post->published_at->format('M j, Y') }}</span>
                                <h3 class="font-heading text-lg font-semibold text-navy group-hover:text-purple transition-colors duration-200 mt-1.5 mb-2 leading-snug">{{ $post->title }}</h3>
                                @if($post->excerpt)
                                    <p class="text-navy/55 text-sm leading-relaxed line-clamp-2 font-body">{{ Str::limit($post->excerpt, 130) }}</p>
                                @endif
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
                    <p class="text-navy/50 text-lg font-body">Blog posts are on the way!</p>
                    <p class="text-navy/35 text-sm mt-2 font-body">We're writing up our best Disney tips and stories.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Podcast Section (Secondary) --}}
    <section class="py-14 md:py-20 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            <div class="flex items-end justify-between mb-10">
                <div>
                    <span class="text-purple/60 text-sm font-semibold tracking-[0.15em] uppercase font-body">🎙️ Also Listen</span>
                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-1">From the Podcast</h2>
                </div>
                <a href="/episodes" class="hidden sm:inline-flex items-center gap-1 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                    All episodes
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            @if($latestEpisodes->count())
                <div class="divide-y divide-navy/8">
                    @foreach($latestEpisodes as $episode)
                        <a href="/episodes/{{ $episode->slug }}" class="group flex items-center gap-5 py-5 hover:bg-cream/50 -mx-4 px-4 rounded-xl transition-colors duration-200">
                            <div class="flex-shrink-0 w-12 h-12 rounded-full bg-purple/10 flex items-center justify-center group-hover:bg-purple/20 transition-colors">
                                <span class="text-purple font-bold text-sm font-body">{{ $episode->episode_number }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-heading text-base font-semibold text-navy group-hover:text-purple transition-colors truncate">{{ $episode->title }}</h3>
                                <p class="text-navy/45 text-sm font-body mt-0.5">
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
                    <p class="text-navy/50 font-body">Our first episode is coming soon!</p>
                </div>
            @endif
        </div>
    </section>

    {{-- About the Hosts (Compact) --}}
    <section class="py-14 md:py-20 bg-cream">
        <div class="max-w-5xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row gap-10 items-center">
                <div class="flex-shrink-0">
                    <img src="/images/logo.jpg" alt="Jeffrey & Cassie Davidson" class="w-36 h-36 md:w-44 md:h-44 rounded-2xl shadow-lg object-cover">
                </div>
                <div>
                    <span class="text-gold text-sm font-semibold tracking-[0.15em] uppercase font-body">Meet the Family</span>
                    <h2 class="font-heading text-2xl md:text-3xl font-bold text-navy mt-1 mb-4">Jeffrey &amp; Cassie Davidson</h2>
                    <p class="text-navy/65 leading-relaxed font-body max-w-xl">
                        We're a Central Florida family who visits Disney every week. Our daughter Viola has autism and experiences the parks differently — she's taught us to see magic in ways we never imagined. Mouse'28 is where we share what we've learned: DAS tips, sensory-friendly spots, and the joy that makes it all worth it.
                    </p>
                    <a href="/about" class="inline-flex items-center gap-1 mt-4 text-purple hover:text-navy font-semibold text-sm transition-colors font-body">
                        Our full story
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Newsletter CTA --}}
    <section class="py-16 md:py-24 bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[20%] left-[15%] text-gold/30 text-sm">✦</span>
            <span class="sparkle-delay absolute bottom-[25%] right-[20%] text-gold/20 text-lg">✧</span>
            <span class="sparkle-delay-2 absolute top-[50%] left-[70%] text-gold/15 text-xs">✦</span>
        </div>

        <div class="max-w-2xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-white mb-4">Get Disney Tips in Your Inbox</h2>
            <p class="text-white/55 text-lg mb-8 font-body">Weekly park tips, accessibility guides, and family stories. No spam, just pixie dust.</p>
            <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                @csrf
                <input type="email" name="email" placeholder="your@email.com" required
                    class="flex-1 px-5 py-3.5 rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/35 focus:outline-none focus:ring-2 focus:ring-gold/60 focus:border-transparent text-sm font-body transition-all">
                <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold px-7 py-3.5 rounded-full transition-all text-sm font-body hover:shadow-lg hover:shadow-gold/20 hover:-translate-y-0.5">
                    Subscribe
                </button>
            </form>
            <div class="flex items-center justify-center gap-6 mt-10 pt-8 border-t border-white/10">
                <a href="#" class="text-white/40 hover:text-white/70 text-sm font-body transition-colors">🎧 Apple Podcasts</a>
                <a href="#" class="text-white/40 hover:text-white/70 text-sm font-body transition-colors">💚 Spotify</a>
                <a href="#" class="text-white/40 hover:text-white/70 text-sm font-body transition-colors">▶️ YouTube</a>
            </div>
        </div>
    </section>
@endsection
