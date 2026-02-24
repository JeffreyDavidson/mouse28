@extends('layouts.app')

@section('title', 'Mouse28 — Disney Parks Through Different Eyes')

@section('content')
    {{-- Hero Section --}}
    <section class="relative bg-gradient-to-br from-navy via-navy-light to-navy overflow-hidden">
        {{-- Sparkle accents --}}
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[15%] left-[10%] text-gold/40 text-xs">✦</span>
            <span class="sparkle-delay absolute top-[25%] right-[15%] text-gold/30 text-sm">✧</span>
            <span class="sparkle-delay-2 absolute top-[60%] left-[20%] text-gold/20 text-xs">✦</span>
            <span class="sparkle absolute top-[40%] right-[8%] text-gold/25 text-lg">✧</span>
            <span class="sparkle-delay absolute bottom-[20%] left-[40%] text-gold/30 text-xs">✦</span>
            <span class="sparkle-delay-2 absolute top-[10%] left-[60%] text-gold/20 text-sm">✧</span>
        </div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-20 md:py-32 text-center relative z-10">
            <img src="/images/logo.jpg" alt="Mouse 28" class="w-40 h-40 md:w-52 md:h-52 rounded-full mx-auto mb-8 shadow-2xl shadow-purple/30 border-4 border-gold/30">
            <span class="inline-block text-gold text-sm font-semibold tracking-widest uppercase mb-4">A Disney Family Podcast</span>
            <h1 class="font-heading text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-tight mb-6">
                Disney Parks Through<br>
                <span class="text-gold">Different Eyes</span>
            </h1>
            <p class="text-white/70 text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
                Join Jeffrey &amp; Cassie Davidson as they navigate the magic of Disney parks while raising their daughter Viola, who has autism. Real stories, practical tips, and a whole lot of pixie dust.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#" class="flex items-center gap-2 bg-white text-navy font-semibold px-6 py-3 rounded-full hover:shadow-lg hover:shadow-white/20 transition-all hover:-translate-y-0.5">
                    🎧 Apple Podcasts
                </a>
                <a href="#" class="flex items-center gap-2 bg-[#1DB954] text-white font-semibold px-6 py-3 rounded-full hover:shadow-lg hover:shadow-[#1DB954]/30 transition-all hover:-translate-y-0.5">
                    💚 Spotify
                </a>
            </div>
        </div>
    </section>

    {{-- Latest Episodes --}}
    <section class="py-16 md:py-24 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <span class="text-gold text-sm font-semibold tracking-widest uppercase">Latest Episodes</span>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Fresh From the Parks</h2>
            </div>

            @if($latestEpisodes->count())
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($latestEpisodes as $episode)
                        <a href="/episodes/{{ $episode->slug }}" class="group bg-white rounded-2xl p-6 shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 border border-navy/5">
                            <div class="flex items-center gap-3 mb-4">
                                <span class="bg-purple/10 text-purple text-xs font-bold px-3 py-1 rounded-full">EP {{ $episode->episode_number }}</span>
                                @if($episode->duration_seconds)
                                    <span class="text-navy/40 text-xs">{{ $episode->formatted_duration }}</span>
                                @endif
                            </div>
                            <h3 class="font-heading text-xl font-semibold text-navy group-hover:text-purple transition-colors mb-3">{{ $episode->title }}</h3>
                            <p class="text-navy/60 text-sm leading-relaxed line-clamp-3">{{ Str::limit($episode->description, 140) }}</p>
                            <div class="mt-4 text-purple text-sm font-medium flex items-center gap-1">
                                Listen now
                                <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-10">
                    <a href="/episodes" class="text-purple hover:text-purple-dark font-semibold text-sm transition-colors">View all episodes →</a>
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-2xl border border-navy/5">
                    <span class="text-4xl mb-4 block">🎙️</span>
                    <p class="text-navy/50 text-lg">Our first episode is coming soon!</p>
                    <p class="text-navy/40 text-sm mt-2">Subscribe so you don't miss it.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- About the Hosts --}}
    <section class="py-16 md:py-24 bg-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                {{-- Logo/Photo --}}
                <div class="bg-gradient-to-br from-purple/10 to-gold/10 rounded-2xl aspect-[4/3] flex items-center justify-center p-8">
                    <img src="/images/logo.jpg" alt="Jeffrey & Cassie Davidson - Mouse 28" class="max-w-full max-h-full rounded-xl shadow-lg">
                </div>

                <div>
                    <span class="text-gold text-sm font-semibold tracking-widest uppercase">Meet the Hosts</span>
                    <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2 mb-6">Jeffrey &amp; Cassie Davidson</h2>
                    <div class="space-y-4 text-navy/70 leading-relaxed">
                        <p>We're a Central Florida family who visits Disney parks every single week. Yes, every week. When your happy place is 30 minutes away, you make it a lifestyle.</p>
                        <p>Our daughter Viola is 8 years old and has autism. She experiences Disney differently — and honestly, she's taught us to see the magic in ways we never would have on our own.</p>
                        <p>Mouse'28 is our way of sharing what we've learned: the DAS pass tips, the sensory-friendly spots, the meltdown strategies, and the moments of pure joy that make it all worth it.</p>
                    </div>
                    <a href="/about" class="inline-block mt-6 text-purple hover:text-purple-dark font-semibold text-sm transition-colors">Read our full story →</a>
                </div>
            </div>
        </div>
    </section>

    {{-- Blog Preview --}}
    <section class="py-16 md:py-24 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="text-center mb-12">
                <span class="text-gold text-sm font-semibold tracking-widest uppercase">From the Blog</span>
                <h2 class="font-heading text-3xl md:text-4xl font-bold text-navy mt-2">Tips, Stories &amp; Disney Life</h2>
            </div>

            @if($latestPosts->count())
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($latestPosts as $post)
                        <a href="/blog/{{ $post->slug }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition-all hover:-translate-y-1 border border-navy/5">
                            @if($post->cover_image)
                                <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-48 object-cover">
                            @else
                                <div class="w-full h-48 bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center">
                                    <span class="text-3xl">✨</span>
                                </div>
                            @endif
                            <div class="p-6">
                                @if($post->category)
                                    <span class="text-gold text-xs font-semibold uppercase tracking-wider">{{ str_replace('-', ' ', $post->category) }}</span>
                                @endif
                                <h3 class="font-heading text-lg font-semibold text-navy group-hover:text-purple transition-colors mt-1 mb-2">{{ $post->title }}</h3>
                                <p class="text-navy/60 text-sm leading-relaxed line-clamp-2">{{ Str::limit($post->excerpt, 120) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="text-center mt-10">
                    <a href="/blog" class="text-purple hover:text-purple-dark font-semibold text-sm transition-colors">Read more posts →</a>
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-2xl border border-navy/5">
                    <span class="text-4xl mb-4 block">📝</span>
                    <p class="text-navy/50 text-lg">Blog posts are on the way!</p>
                    <p class="text-navy/40 text-sm mt-2">We're writing up our best Disney tips and stories.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- Subscribe CTA --}}
    <section class="py-16 md:py-24 bg-gradient-to-br from-navy via-navy-light to-navy relative overflow-hidden">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <span class="sparkle absolute top-[20%] left-[15%] text-gold/30 text-sm">✦</span>
            <span class="sparkle-delay absolute bottom-[25%] right-[20%] text-gold/20 text-lg">✧</span>
        </div>

        <div class="max-w-3xl mx-auto px-4 sm:px-6 text-center relative z-10">
            <h2 class="font-heading text-3xl md:text-4xl font-bold text-white mb-4">Never Miss an Episode</h2>
            <p class="text-white/60 text-lg mb-8">Subscribe on your favorite platform and join our Disney family.</p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="#" class="flex items-center gap-2 bg-white text-navy font-semibold px-6 py-3 rounded-full hover:shadow-lg transition-all hover:-translate-y-0.5">🎧 Apple Podcasts</a>
                <a href="#" class="flex items-center gap-2 bg-[#1DB954] text-white font-semibold px-6 py-3 rounded-full hover:shadow-lg transition-all hover:-translate-y-0.5">💚 Spotify</a>
                <a href="#" class="flex items-center gap-2 bg-red-600 text-white font-semibold px-6 py-3 rounded-full hover:shadow-lg transition-all hover:-translate-y-0.5">▶️ YouTube</a>
            </div>

            {{-- Newsletter --}}
            <div class="mt-12 bg-white/10 backdrop-blur-sm rounded-2xl p-8">
                <h3 class="font-heading text-xl text-white font-semibold mb-2">Get Disney Tips in Your Inbox</h3>
                <p class="text-white/50 text-sm mb-6">Weekly park tips, accessibility guides, and family stories.</p>
                <form action="#" method="POST" class="flex flex-col sm:flex-row gap-3 max-w-md mx-auto">
                    @csrf
                    <input type="email" name="email" placeholder="your@email.com" required
                        class="flex-1 px-4 py-3 rounded-full bg-white/10 border border-white/20 text-white placeholder:text-white/40 focus:outline-none focus:ring-2 focus:ring-gold text-sm">
                    <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold px-6 py-3 rounded-full transition-all text-sm">Subscribe</button>
                </form>
            </div>
        </div>
    </section>
@endsection
