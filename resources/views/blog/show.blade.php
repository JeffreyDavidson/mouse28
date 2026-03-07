@extends('layouts.app')

@section('title', $post->title . ' — Mouse28')
@section('meta_description', Str::limit($post->excerpt, 160))
@section('og_title', $post->title)
@section('og_description', Str::limit($post->excerpt, 200))
@section('og_type', 'article')
@if($post->cover_image) @section('og_image', $post->cover_image) @endif

@section('content')
    <style>
        #reading-progress {
            position: fixed; top: 64px; left: 0; height: 3px; z-index: 40;
            background: linear-gradient(90deg, #d4a843, #f0c75e);
            width: 0%; transition: width 0.1s linear;
            box-shadow: 0 0 8px rgba(212,168,67,0.4);
        }
        .article-content p + p {
            margin-top: 1.5em;
        }
        .article-content br {
            display: block;
            content: "";
            margin-top: 1em;
        }
        .article-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.25rem;
            font-weight: 800;
            color: #1a1040;
            margin-top: 2.5rem;
            margin-bottom: 1.25rem;
        }
        .article-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a1040;
            margin-top: 2.5rem;
            margin-bottom: 1rem;
        }
        .article-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #1a1040;
            margin-top: 2rem;
            margin-bottom: 0.75rem;
        }
        .article-content > p:first-of-type::first-letter {
            float: left; font-family: 'Playfair Display', serif;
            font-size: 3.5em; line-height: 0.8; font-weight: 700;
            color: #d4a843; margin-right: 0.08em; margin-top: 0.05em;
        }
        .share-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 2.25rem; height: 2.25rem; border-radius: 9999px;
            transition: all 0.2s ease; color: rgba(26,16,64,0.4);
            border: 1px solid rgba(26,16,64,0.1); background: white;
        }
        .share-btn:hover { color: #5b3e9e; border-color: #5b3e9e; transform: translateY(-1px); }
        .waveform-decoration { display: flex; align-items: end; gap: 2px; height: 24px; opacity: 0.15; }
        .waveform-decoration span { width: 3px; border-radius: 2px; background: #d4a843; }
    </style>

    <div id="reading-progress"></div>

    {{-- Header --}}
    <section class="bg-gradient-to-br from-navy to-navy-light py-16 md:py-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <a href="/blog" class="text-white/50 hover:text-gold text-sm transition-colors mb-6 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                All Posts
            </a>
            <div class="mt-4 max-w-3xl">
                <div class="flex flex-wrap items-center gap-3 mb-4">
                    @if($post->category)
                        <span class="text-sm font-semibold px-4 py-1 rounded-full {{ $post->category_color }}">{{ $post->category_label }}</span>
                    @endif
                    <span class="text-white/40 text-sm">{{ $post->published_at->format('F j, Y') }}</span>
                    <span class="text-white/30">·</span>
                    <span class="text-white/40 text-sm" id="reading-indicator">{{ $post->reading_time }} min read</span>
                </div>
                <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-bold text-white">{{ $post->title }}</h1>
                <div class="flex items-center gap-4 mt-5">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gold/20 flex items-center justify-center text-gold text-xs font-bold">
                            {{ collect(explode(' ', $post->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                        </div>
                        <span class="text-white/60 text-sm font-medium">{{ $post->author_name }}</span>
                    </div>
                    <div class="flex items-center gap-1.5 ml-auto">
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="share-btn !bg-white/10 !border-white/20 !text-white/50 hover:!text-gold hover:!border-gold/50" title="Share on X">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                        </a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="share-btn !bg-white/10 !border-white/20 !text-white/50 hover:!text-gold hover:!border-gold/50" title="Share on Facebook">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>{const t=this.querySelector('.copy-feedback');t.classList.remove('hidden');setTimeout(()=>t.classList.add('hidden'),1500)})" class="share-btn !bg-white/10 !border-white/20 !text-white/50 hover:!text-gold hover:!border-gold/50 relative" title="Copy link">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                            <span class="copy-feedback hidden absolute -bottom-8 left-1/2 -translate-x-1/2 text-[10px] bg-gold text-white px-2 py-0.5 rounded-full whitespace-nowrap shadow-lg">Copied!</span>
                        </button>
                    </div>
                </div>
                @if($post->excerpt)
                    <p class="text-white/60 text-lg mt-4 leading-relaxed">{{ $post->excerpt }}</p>
                @endif
            </div>
        </div>
    </section>

    <section class="py-12 bg-cream">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col lg:flex-row gap-10">
                {{-- Main Content --}}
                <div class="lg:w-[68%]">
                    @if($post->cover_image)
                        <img src="{{ $post->cover_image }}" alt="{{ $post->title }}" class="w-full h-64 md:h-96 object-cover rounded-2xl mb-10 shadow-sm">
                    @endif

                    <article id="article-body" class="bg-white rounded-2xl p-8 md:p-12 shadow-sm border border-navy/5">
                        <div class="prose prose-lg prose-navy max-w-none text-navy/80 leading-relaxed article-content">
                            {!! Str::markdown($post->body ?? '', [
                                'html_input' => 'strip',
                                'allow_unsafe_links' => false,
                            ]) !!}
                        </div>
                    </article>

                    {{-- Author Card --}}
                    <div class="mt-8 bg-white rounded-2xl p-8 shadow-sm border border-navy/5">
                        <div class="flex items-start gap-5">
                            <div class="w-16 h-16 rounded-full bg-gradient-to-br from-gold/30 to-purple/20 flex items-center justify-center text-gold text-lg font-bold font-heading flex-shrink-0">
                                {{ collect(explode(' ', $post->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                            </div>
                            <div>
                                <span class="text-navy/40 text-xs font-semibold uppercase tracking-wider">Written by</span>
                                <h3 class="font-heading text-xl font-bold text-navy mt-0.5">{{ $post->author_name }}</h3>
                                <p class="text-navy/60 text-sm leading-relaxed mt-1">
                                    @if(Str::contains($post->author_name, '&') || (Str::contains($post->author_name, 'Jeffrey') && Str::contains($post->author_name, 'Cassie')))
                                        The couple behind Mouse28. Over a decade as Disney passholders, navigating park life with their daughter Viola and sharing every tip, review, and memory along the way.
                                    @elseif(Str::contains($post->author_name, 'Cassie'))
                                        Co-host of Mouse28. Disney magic-maker, accessibility champion, and the planner behind every park day.
                                    @elseif(Str::contains($post->author_name, 'Jeffrey'))
                                        Co-host of Mouse28. Theme park nerd, tech enthusiast, and the voice keeping it real about Disney life.
                                    @else
                                        Disney park explorer, accessibility advocate, and parent.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Related Episode --}}
                    @if($post->episode)
                        <div class="mt-8 bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <span class="text-gold text-xs font-semibold uppercase tracking-wider">Related Episode</span>
                            <a href="/episodes/{{ $post->episode->slug }}" class="group block mt-3">
                                <div class="flex items-center gap-4">
                                    <span class="flex-shrink-0 inline-flex items-center justify-center w-12 h-12 bg-purple/10 text-purple font-heading font-bold rounded-xl">{{ $post->episode->episode_number }}</span>
                                    <div>
                                        <h3 class="font-heading text-lg font-semibold text-navy group-hover:text-purple transition-colors">{{ $post->episode->title }}</h3>
                                        <p class="text-navy/50 text-sm mt-1">Listen to the full episode →</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif

                    {{-- Read Next --}}
                    @if($recentPosts->count())
                        <div class="mt-10">
                            <h3 class="font-heading text-xl font-bold text-navy mb-5">Read Next</h3>
                            <div class="grid sm:grid-cols-2 gap-5">
                                @foreach($recentPosts->take(2) as $next)
                                    <a href="/blog/{{ $next->slug }}" class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 border border-navy/5">
                                        @if($next->cover_image)
                                            <img src="{{ $next->cover_image }}" alt="" class="w-full h-36 object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-36 bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center">
                                                <span class="text-2xl">✨</span>
                                            </div>
                                        @endif
                                        <div class="p-5">
                                            <h4 class="font-heading text-base font-semibold text-navy group-hover:text-purple transition-colors line-clamp-2 leading-snug">{{ $next->title }}</h4>
                                            <span class="text-navy/40 text-xs mt-2 block">{{ $next->reading_time }} min read</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[32%] space-y-6">
                    {{-- Recent Posts --}}
                    @if($recentPosts->count())
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                            <h3 class="font-heading text-lg font-bold text-navy mb-5">Recent Posts</h3>
                            <div class="space-y-4">
                                @foreach($recentPosts as $recent)
                                    <a href="/blog/{{ $recent->slug }}" class="group flex gap-3 items-start">
                                        @if($recent->cover_image)
                                            <img src="{{ $recent->cover_image }}" alt="" class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                                        @else
                                            <div class="w-14 h-14 rounded-lg bg-gradient-to-br from-purple/10 to-gold/10 flex items-center justify-center flex-shrink-0">
                                                <span class="text-sm">✨</span>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <h4 class="text-sm font-semibold text-navy group-hover:text-purple transition-colors line-clamp-2 leading-snug">{{ $recent->title }}</h4>
                                            <span class="text-xs text-navy/40 mt-1 block">{{ $recent->published_at->format('M j, Y') }}</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Categories --}}
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-navy/5">
                        <h3 class="font-heading text-lg font-bold text-navy mb-5">Categories</h3>
                        <div class="space-y-1">
                            @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                                @php $count = $categoryCounts[$slug] ?? 0; @endphp
                                @if($count > 0)
                                    <a href="/blog?category={{ $slug }}" class="flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-cream transition-colors group">
                                        <span class="text-sm text-navy/70 group-hover:text-navy transition-colors font-medium">{{ $label }}</span>
                                        <span class="text-xs text-navy/30 bg-navy/5 px-2.5 py-0.5 rounded-full font-semibold">{{ $count }}</span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    </div>

                    {{-- Podcast CTA --}}
                    <div class="bg-gradient-to-br from-navy to-navy-light rounded-2xl p-6 text-center relative overflow-hidden">
                        {{-- Waveform decoration --}}
                        <div class="absolute bottom-3 left-4 waveform-decoration" aria-hidden="true">
                            <span style="height: 40%"></span><span style="height: 70%"></span><span style="height: 50%"></span>
                            <span style="height: 90%"></span><span style="height: 30%"></span><span style="height: 60%"></span>
                            <span style="height: 80%"></span><span style="height: 45%"></span><span style="height: 65%"></span>
                        </div>
                        <div class="absolute bottom-3 right-4 waveform-decoration" aria-hidden="true">
                            <span style="height: 55%"></span><span style="height: 85%"></span><span style="height: 40%"></span>
                            <span style="height: 70%"></span><span style="height: 50%"></span><span style="height: 90%"></span>
                            <span style="height: 35%"></span><span style="height: 60%"></span><span style="height: 75%"></span>
                        </div>
                        <span class="text-3xl block mb-3 relative">🎧</span>
                        <h3 class="font-heading text-lg font-bold text-white mb-2 relative">Listen to the Podcast</h3>
                        <p class="text-white/50 text-sm mb-4 relative">Catch our latest episodes about Disney, accessibility, and family life.</p>
                        <a href="/episodes" class="inline-block bg-gold hover:bg-gold-light text-white font-semibold text-sm px-6 py-2.5 rounded-full transition-all relative hover:-translate-y-0.5">
                            Browse Episodes
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <script>
        // Reading progress bar
        const bar = document.getElementById('reading-progress');
        const article = document.getElementById('article-body');
        const indicator = document.getElementById('reading-indicator');
        const totalMin = {{ $post->reading_time }};

        function updateProgress() {
            if (!article) return;
            const rect = article.getBoundingClientRect();
            const articleTop = window.scrollY + rect.top;
            const articleHeight = rect.height;
            const scrolled = window.scrollY - articleTop;
            const denom = articleHeight - window.innerHeight * 0.5;
            const pct = denom > 0 ? Math.max(0, Math.min(100, (scrolled / denom) * 100)) : 0;
            bar.style.width = pct + '%';

            // Update reading position indicator
            if (indicator && totalMin > 0 && !isNaN(pct)) {
                const currentMin = Math.max(1, Math.ceil((pct / 100) * totalMin));
                indicator.textContent = currentMin + ' of ' + totalMin + ' min read';
            }
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();
    </script>
@endsection
