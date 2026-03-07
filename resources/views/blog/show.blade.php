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

        /* Hero section */
        .post-hero {
            position: relative;
            min-height: 420px;
            display: flex;
            align-items: flex-end;
            overflow: hidden;
        }
        .post-hero-bg {
            position: absolute; inset: 0;
            background: linear-gradient(135deg, #1a1040 0%, #2d1b69 50%, #1a1040 100%);
        }
        .post-hero-bg.has-image {
            background-size: cover;
            background-position: center;
        }
        .post-hero-bg::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(0deg, rgba(26,16,64,0.95) 0%, rgba(26,16,64,0.6) 40%, rgba(26,16,64,0.3) 100%);
        }
        .post-hero-bg.has-image::after {
            background: linear-gradient(0deg, rgba(26,16,64,0.97) 0%, rgba(26,16,64,0.7) 40%, rgba(26,16,64,0.4) 100%);
        }

        /* Decorative sparkles */
        .post-hero::before {
            content: '✦'; position: absolute; top: 15%; right: 12%;
            font-size: 1rem; color: rgba(240,199,94,0.25); z-index: 1;
            animation: sparkle 4s ease-in-out infinite;
        }
        .post-hero::after {
            content: '✦'; position: absolute; top: 30%; left: 8%;
            font-size: 0.7rem; color: rgba(240,199,94,0.15); z-index: 1;
            animation: sparkle 5s ease-in-out 1s infinite;
        }
        @keyframes sparkle {
            0%, 100% { opacity: 0.2; transform: scale(1) rotate(0deg); }
            50% { opacity: 1; transform: scale(1.3) rotate(15deg); }
        }

        /* Gold decorative line */
        .gold-divider {
            height: 3px; width: 60px;
            background: linear-gradient(90deg, #d4a843, #f0c75e);
            border-radius: 2px;
        }

        /* Article content styles */
        .article-content p + p { margin-top: 1.5em; }
        .article-content br { display: block; content: ""; margin-top: 1em; }
        .article-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.25rem; font-weight: 800; color: #1a1040;
            margin-top: 2.5rem; margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid rgba(212,168,67,0.2);
        }
        .article-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem; font-weight: 700; color: #1a1040;
            margin-top: 2.5rem; margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(212,168,67,0.15);
        }
        .article-content h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.35rem; font-weight: 700; color: #1a1040;
            margin-top: 2rem; margin-bottom: 0.75rem;
        }
        .article-content > p:first-of-type::first-letter {
            float: left; font-family: 'Playfair Display', serif;
            font-size: 4em; line-height: 0.75; font-weight: 700;
            color: #d4a843; margin-right: 0.1em; margin-top: 0.05em;
            text-shadow: 2px 2px 0 rgba(212,168,67,0.1);
        }
        .article-content blockquote {
            border-left: 4px solid #d4a843;
            background: linear-gradient(135deg, rgba(212,168,67,0.05), rgba(91,62,158,0.03));
            padding: 1.5rem 2rem;
            margin: 2rem 0;
            border-radius: 0 1rem 1rem 0;
            font-style: italic;
            color: #2d1b69;
        }

        /* Share buttons */
        .share-btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 2.5rem; height: 2.5rem; border-radius: 9999px;
            transition: all 0.3s ease; color: rgba(254,249,239,0.4);
            border: 1px solid rgba(254,249,239,0.15); background: rgba(254,249,239,0.05);
            backdrop-filter: blur(4px);
        }
        .share-btn:hover {
            color: #f0c75e; border-color: rgba(240,199,94,0.5);
            background: rgba(240,199,94,0.1); transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(212,168,67,0.2);
        }

        /* Sidebar card hover */
        .sidebar-card {
            transition: all 0.3s ease;
        }
        .sidebar-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26,16,64,0.08);
        }

        /* Author card */
        .author-card {
            position: relative;
            overflow: hidden;
        }
        .author-card::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 4px;
            background: linear-gradient(90deg, #d4a843, #5b3e9e, #d4a843);
        }

        /* Waveform */
        .waveform-decoration { display: flex; align-items: end; gap: 2px; height: 24px; opacity: 0.15; }
        .waveform-decoration span { width: 3px; border-radius: 2px; background: #d4a843; }

        /* Category pill in hero */
        .category-pill {
            backdrop-filter: blur(8px);
            border: 1px solid rgba(212,168,67,0.3);
        }

        /* Floating sidebar on scroll */
        @media (min-width: 1024px) {
            .sticky-sidebar { position: sticky; top: 90px; }
        }

        /* Table of Contents */
        .toc-link {
            display: block; padding: 0.4rem 0; padding-left: 1rem;
            font-size: 0.8rem; color: rgba(26,16,64,0.5);
            border-left: 2px solid rgba(26,16,64,0.08);
            transition: all 0.2s ease; text-decoration: none;
            line-height: 1.4;
        }
        .toc-link:hover, .toc-link.active {
            color: #d4a843;
            border-left-color: #d4a843;
        }
        .toc-link.active { font-weight: 600; }
        .toc-link.toc-h3 { padding-left: 2rem; font-size: 0.75rem; }

        /* Bottom share buttons */
        .share-btn-lg {
            display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;
            padding: 0.65rem 1.25rem; border-radius: 9999px;
            font-size: 0.8rem; font-weight: 600;
            transition: all 0.3s ease;
            border: 1px solid rgba(26,16,64,0.1);
            color: rgba(26,16,64,0.6); background: white;
        }
        .share-btn-lg:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(26,16,64,0.1);
        }
        .share-btn-lg.twitter:hover { color: #1da1f2; border-color: #1da1f2; }
        .share-btn-lg.facebook:hover { color: #1877f2; border-color: #1877f2; }
        .share-btn-lg.copy:hover { color: #d4a843; border-color: #d4a843; }

        /* Back to top */
        #back-to-top {
            position: fixed; bottom: 2rem; right: 2rem; z-index: 50;
            width: 3rem; height: 3rem; border-radius: 9999px;
            background: linear-gradient(135deg, #d4a843, #f0c75e);
            color: white; border: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 15px rgba(212,168,67,0.3);
            transition: all 0.3s ease;
            opacity: 0; pointer-events: none;
            transform: translateY(10px);
        }
        #back-to-top.visible {
            opacity: 1; pointer-events: auto; transform: translateY(0);
        }
        #back-to-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(212,168,67,0.4);
        }
    </style>

    <div id="reading-progress"></div>

    {{-- Hero Section --}}
    <section class="post-hero">
        <div class="post-hero-bg {{ $post->cover_image ? 'has-image' : '' }}"
             @if($post->cover_image) style="background-image: url('{{ $post->cover_image }}')" @endif>
        </div>

        <div class="relative z-10 w-full max-w-6xl mx-auto px-4 sm:px-6 pb-14 pt-20">
            {{-- Back link --}}
            <a href="/blog" class="inline-flex items-center gap-1.5 text-white/40 hover:text-gold text-sm transition-all mb-8 group">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Back to Blog
            </a>

            {{-- Meta row --}}
            <div class="flex flex-wrap items-center gap-3 mb-5">
                @if($post->category)
                    <span class="category-pill text-xs font-bold px-4 py-1.5 rounded-full bg-gold/15 text-gold uppercase tracking-wider">
                        {{ $post->category_label }}
                    </span>
                @endif
                <span class="text-white/30 text-sm">{{ $post->published_at->format('F j, Y') }}</span>
                <span class="text-white/20">·</span>
                <span class="text-white/30 text-sm" id="reading-indicator">{{ $post->reading_time }} min read</span>
            </div>

            {{-- Title --}}
            <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight max-w-4xl">
                {{ $post->title }}
            </h1>

            {{-- Gold divider --}}
            <div class="gold-divider mt-6 mb-6"></div>

            {{-- Excerpt --}}
            @if($post->excerpt)
                <p class="text-white/50 text-lg md:text-xl leading-relaxed max-w-3xl font-light">{{ $post->excerpt }}</p>
            @endif

            {{-- Author + Share row --}}
            <div class="flex items-center justify-between mt-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gold/30 to-purple/20 flex items-center justify-center text-gold font-bold font-heading border-2 border-gold/20">
                        {{ collect(explode(' ', $post->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">{{ $post->author_name }}</p>
                        <p class="text-white/30 text-xs">Mouse28</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="share-btn" title="Share on X">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="share-btn" title="Share on Facebook">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>{const t=this.querySelector('.copy-feedback');t.classList.remove('hidden');setTimeout(()=>t.classList.add('hidden'),1500)})" class="share-btn relative" title="Copy link">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                        <span class="copy-feedback hidden absolute -bottom-9 left-1/2 -translate-x-1/2 text-[10px] bg-gold text-white px-3 py-1 rounded-full whitespace-nowrap shadow-lg">Copied!</span>
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Content Section --}}
    <section class="py-14 bg-cream relative">
        {{-- Subtle decorative dots --}}
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold/20 to-transparent"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col lg:flex-row gap-12">
                {{-- Main Content --}}
                <div class="lg:w-[66%]">
                    <article id="article-body" class="bg-white rounded-3xl p-8 md:p-14 shadow-lg shadow-navy/5 border border-navy/5 relative">
                        {{-- Decorative corner accent --}}
                        <div class="absolute top-0 right-0 w-24 h-24 overflow-hidden rounded-tr-3xl">
                            <div class="absolute -top-12 -right-12 w-24 h-24 bg-gradient-to-bl from-gold/8 to-transparent rotate-45"></div>
                        </div>

                        <div class="prose prose-lg prose-navy max-w-none text-navy/80 leading-[1.85] article-content" style="font-size: 1.1rem;">
                            {!! Str::markdown($post->body ?? '', [
                                'html_input' => 'strip',
                                'allow_unsafe_links' => false,
                            ]) !!}
                        </div>

                        {{-- End flourish --}}
                        <div class="flex items-center justify-center gap-3 mt-12 pt-8 border-t border-navy/5">
                            <span class="text-gold/30">✦</span>
                            <span class="text-gold/50 text-lg">✦</span>
                            <span class="text-gold/30">✦</span>
                        </div>
                    </article>

                    {{-- Author Card --}}
                    <div class="author-card mt-10 bg-white rounded-3xl p-8 md:p-10 shadow-lg shadow-navy/5 border border-navy/5">
                        <div class="flex flex-col sm:flex-row items-start gap-6">
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-gold/25 to-purple/15 flex items-center justify-center text-gold text-xl font-bold font-heading flex-shrink-0 border border-gold/15">
                                {{ collect(explode(' ', $post->author_name))->reject(fn($w) => in_array($w, ['&', 'and']))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('&') }}
                            </div>
                            <div>
                                <span class="text-gold text-xs font-bold uppercase tracking-widest">Written by</span>
                                <h3 class="font-heading text-2xl font-bold text-navy mt-1">{{ $post->author_name }}</h3>
                                <div class="gold-divider mt-3 mb-3"></div>
                                <p class="text-navy/55 text-sm leading-relaxed">
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

                    {{-- Share This Post --}}
                    <div class="mt-10 bg-white rounded-3xl p-8 md:p-10 shadow-lg shadow-navy/5 border border-navy/5 text-center">
                        <span class="text-gold text-xs font-bold uppercase tracking-widest">Enjoyed this post?</span>
                        <h3 class="font-heading text-xl font-bold text-navy mt-2 mb-2">Share it with fellow Disney fans</h3>
                        <p class="text-navy/40 text-sm mb-6">Help others discover Mouse28</p>
                        <div class="flex flex-wrap items-center justify-center gap-3">
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title . ' — Mouse28') }}" target="_blank" rel="noopener" class="share-btn-lg twitter">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                Share on X
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener" class="share-btn-lg facebook">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Share on Facebook
                            </a>
                            <button onclick="navigator.clipboard.writeText(window.location.href).then(()=>{this.textContent='Copied! ✓';setTimeout(()=>{this.innerHTML='<svg class=\'w-4 h-4\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1\'/></svg> Copy Link';},1500)})" class="share-btn-lg copy">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Copy Link
                            </button>
                        </div>
                    </div>

                    {{-- Related Episode --}}
                    @if($post->episode)
                        <div class="mt-8 bg-white rounded-3xl p-8 shadow-lg shadow-navy/5 border border-navy/5">
                            <span class="text-gold text-xs font-bold uppercase tracking-widest">Related Episode</span>
                            <a href="/episodes/{{ $post->episode->slug }}" class="group block mt-4">
                                <div class="flex items-center gap-5">
                                    <span class="flex-shrink-0 inline-flex items-center justify-center w-14 h-14 bg-gradient-to-br from-purple/15 to-gold/10 text-purple font-heading font-bold text-lg rounded-2xl border border-purple/10">{{ $post->episode->episode_number }}</span>
                                    <div>
                                        <h3 class="font-heading text-lg font-semibold text-navy group-hover:text-purple transition-colors">{{ $post->episode->title }}</h3>
                                        <p class="text-navy/40 text-sm mt-1 group-hover:text-gold transition-colors">Listen to the full episode →</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endif

                    {{-- Read Next --}}
                    @if($recentPosts->count())
                        <div class="mt-12">
                            <div class="flex items-center gap-4 mb-6">
                                <h3 class="font-heading text-xl font-bold text-navy">Continue Reading</h3>
                                <div class="flex-1 h-px bg-gradient-to-r from-navy/10 to-transparent"></div>
                            </div>
                            <div class="grid sm:grid-cols-2 gap-6">
                                @foreach($recentPosts->take(2) as $next)
                                    <a href="/blog/{{ $next->slug }}" class="group bg-white rounded-2xl overflow-hidden shadow-md shadow-navy/5 hover:shadow-xl transition-all duration-300 border border-navy/5 hover:-translate-y-1">
                                        @if($next->cover_image)
                                            <div class="overflow-hidden">
                                                <img src="{{ $next->cover_image }}" alt="" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-500">
                                            </div>
                                        @endif
                                        <div class="p-6">
                                            @if($next->category)
                                                <span class="text-[10px] font-bold text-gold uppercase tracking-wider">{{ $next->category_label }}</span>
                                            @endif
                                            <h4 class="font-heading text-base font-semibold text-navy group-hover:text-purple transition-colors line-clamp-2 leading-snug mt-1">{{ $next->title }}</h4>
                                            <span class="text-navy/30 text-xs mt-3 block">{{ $next->reading_time }} min read</span>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <aside class="lg:w-[34%]">
                    <div class="sticky-sidebar space-y-7">
                        {{-- Table of Contents (populated by JS) --}}
                        <div id="toc-card" class="sidebar-card bg-white rounded-2xl p-7 shadow-md shadow-navy/5 border border-navy/5 hidden">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="gold-divider"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">In This Post</h3>
                            </div>
                            <nav id="toc-nav" class="space-y-0.5"></nav>
                        </div>

                        {{-- Recent Posts --}}
                        @if($recentPosts->count())
                            <div class="sidebar-card bg-white rounded-2xl p-7 shadow-md shadow-navy/5 border border-navy/5">
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="gold-divider"></div>
                                    <h3 class="font-heading text-lg font-bold text-navy">Recent Posts</h3>
                                </div>
                                <div class="space-y-4">
                                    @foreach($recentPosts as $recent)
                                        <a href="/blog/{{ $recent->slug }}" class="group flex gap-4 items-start p-2 -mx-2 rounded-xl hover:bg-cream/50 transition-colors">
                                            @if($recent->cover_image)
                                                <img src="{{ $recent->cover_image }}" alt="" class="w-16 h-16 rounded-xl object-cover flex-shrink-0 shadow-sm">
                                            @else
                                                <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-purple/8 to-gold/8 flex items-center justify-center flex-shrink-0">
                                                    <span class="text-gold/50 text-sm">✦</span>
                                                </div>
                                            @endif
                                            <div class="min-w-0 pt-0.5">
                                                <h4 class="text-sm font-semibold text-navy group-hover:text-purple transition-colors line-clamp-2 leading-snug">{{ $recent->title }}</h4>
                                                <span class="text-xs text-navy/35 mt-1.5 block">{{ $recent->published_at->format('M j, Y') }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- Categories --}}
                        <div class="sidebar-card bg-white rounded-2xl p-7 shadow-md shadow-navy/5 border border-navy/5">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="gold-divider"></div>
                                <h3 class="font-heading text-lg font-bold text-navy">Categories</h3>
                            </div>
                            <div class="space-y-1">
                                @foreach(\App\Models\Post::CATEGORIES as $slug => $label)
                                    @php $count = $categoryCounts[$slug] ?? 0; @endphp
                                    @if($count > 0)
                                        <a href="/blog?category={{ $slug }}" class="flex items-center justify-between py-2.5 px-3 rounded-xl hover:bg-cream transition-all group">
                                            <span class="text-sm text-navy/65 group-hover:text-navy transition-colors font-medium">{{ $label }}</span>
                                            <span class="text-xs text-gold/70 bg-gold/8 px-3 py-0.5 rounded-full font-bold group-hover:bg-gold/15 transition-colors">{{ $count }}</span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Podcast CTA --}}
                        <div class="sidebar-card bg-gradient-to-br from-navy via-navy-light to-navy rounded-2xl p-7 text-center relative overflow-hidden border border-white/5">
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
                            {{-- Subtle glow --}}
                            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-gold/5 rounded-full blur-3xl"></div>
                            <span class="text-4xl block mb-4 relative">🎧</span>
                            <h3 class="font-heading text-lg font-bold text-white mb-2 relative">Listen to the Podcast</h3>
                            <p class="text-white/40 text-sm mb-5 relative leading-relaxed">Catch our latest episodes about Disney, accessibility, and family life.</p>
                            <a href="/episodes" class="inline-block bg-gradient-to-r from-gold to-gold-light hover:from-gold-light hover:to-gold text-navy font-bold text-sm px-7 py-3 rounded-full transition-all relative hover:-translate-y-0.5 shadow-lg shadow-gold/20">
                                Browse Episodes
                            </a>
                        </div>

                        {{-- Newsletter --}}
                        <div class="sidebar-card rounded-2xl overflow-hidden shadow-md shadow-navy/5 border border-gold/15 relative">
                            {{-- Gold gradient header --}}
                            <div class="bg-gradient-to-r from-gold/15 via-gold/8 to-purple/8 px-7 pt-6 pb-5 text-center relative">
                                <div class="absolute top-3 right-4 text-gold/15 text-xs">✦</div>
                                <div class="absolute bottom-3 left-4 text-gold/10 text-[10px]">✦</div>
                                <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-white/80 flex items-center justify-center shadow-sm">
                                    <svg class="w-6 h-6 text-gold" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                                </div>
                                <h3 class="font-heading text-lg font-bold text-navy">Stay in the Loop</h3>
                                <p class="text-navy/45 text-xs mt-1">Disney tips, park updates, and new posts</p>
                            </div>
                            {{-- Form area --}}
                            <div class="bg-white px-7 py-6">
                                <form action="/newsletter" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="email" name="email" placeholder="your@email.com" required
                                        class="w-full px-4 py-2.5 text-sm rounded-xl border border-navy/10 focus:border-gold focus:ring-2 focus:ring-gold/20 outline-none transition-all placeholder:text-navy/25 text-navy">
                                    <button type="submit" class="w-full bg-gradient-to-r from-gold to-gold-light hover:from-gold-light hover:to-gold text-navy font-bold text-sm py-2.5 rounded-xl transition-all hover:-translate-y-0.5 shadow-md shadow-gold/15">
                                        Subscribe ✨
                                    </button>
                                </form>
                                <p class="text-navy/25 text-[10px] text-center mt-3">No spam. Unsubscribe anytime.</p>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    {{-- Back to Top Button --}}
    <button id="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" title="Back to top">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
    </button>

    <script>
        const bar = document.getElementById('reading-progress');
        const article = document.getElementById('article-body');
        const indicator = document.getElementById('reading-indicator');
        const totalMin = {{ $post->reading_time }};
        const backToTop = document.getElementById('back-to-top');

        // Reading progress + back to top visibility
        function updateProgress() {
            if (!article) return;
            const rect = article.getBoundingClientRect();
            const articleTop = window.scrollY + rect.top;
            const articleHeight = rect.height;
            const scrolled = window.scrollY - articleTop;
            const denom = articleHeight - window.innerHeight * 0.5;
            const pct = denom > 0 ? Math.max(0, Math.min(100, (scrolled / denom) * 100)) : 0;
            bar.style.width = pct + '%';
            if (indicator && totalMin > 0 && !isNaN(pct)) {
                const currentMin = Math.max(1, Math.ceil((pct / 100) * totalMin));
                indicator.textContent = currentMin + ' of ' + totalMin + ' min read';
            }

            // Back to top
            if (window.scrollY > 500) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
        updateProgress();

        // Table of Contents — auto-generate from headings
        (function() {
            const content = document.querySelector('.article-content');
            const tocCard = document.getElementById('toc-card');
            const tocNav = document.getElementById('toc-nav');
            if (!content || !tocNav) return;

            const headings = content.querySelectorAll('h1, h2, h3');
            if (headings.length < 2) return; // Don't show TOC for short posts

            tocCard.classList.remove('hidden');

            headings.forEach((h, i) => {
                const id = 'section-' + i;
                h.id = id;

                const link = document.createElement('a');
                link.href = '#' + id;
                link.textContent = h.textContent;
                link.className = 'toc-link' + (h.tagName === 'H3' ? ' toc-h3' : '');
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    document.getElementById(id).scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
                tocNav.appendChild(link);
            });

            // Highlight active TOC item on scroll
            const tocLinks = tocNav.querySelectorAll('.toc-link');
            function updateToc() {
                let current = 0;
                headings.forEach((h, i) => {
                    if (h.getBoundingClientRect().top < 150) current = i;
                });
                tocLinks.forEach((l, i) => {
                    l.classList.toggle('active', i === current);
                });
            }
            window.addEventListener('scroll', updateToc, { passive: true });
            updateToc();
        })();
    </script>
@endsection
