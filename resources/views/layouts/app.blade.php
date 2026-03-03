<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mouse28 — Disney Parks Through Different Eyes')</title>
    <meta name="description" content="@yield('meta_description', 'Disney parks through the eyes of a family raising a daughter with autism. Tips, accessibility guides, and stories from Jeffrey & Cassie Davidson.')">

    {{-- Open Graph --}}
    <meta property="og:title" content="@yield('og_title', 'Mouse28 — Disney Parks Through Different Eyes')">
    <meta property="og:description" content="@yield('og_description', 'Disney parks through the eyes of a family raising a daughter with autism. Tips, accessibility guides, and stories.')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', url('/images/logo.jpg'))">
    <meta property="og:site_name" content="Mouse28">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('og_title', 'Mouse28 — Disney Parks Through Different Eyes')">
    <meta name="twitter:description" content="@yield('og_description', 'Disney parks through the eyes of a family raising a daughter with autism.')">
    <meta name="twitter:image" content="@yield('og_image', url('/images/logo.jpg'))">

    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" type="application/rss+xml" title="Mouse28 Blog" href="{{ url('/rss/blog') }}">
    <link rel="alternate" type="application/rss+xml" title="Mouse28 Podcast" href="{{ url('/rss/podcast') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: '#1a1040',
                        'navy-light': '#2d1b69',
                        purple: '#5b3e9e',
                        'purple-light': '#7b5eb5',
                        'purple-dark': '#3a2370',
                        gold: '#d4a843',
                        'gold-light': '#f0c75e',
                        'gold-dark': '#b8922e',
                        cream: '#fef9ef',
                        'cream-dark': '#f5efe0',
                    },
                    fontFamily: {
                        heading: ['"Playfair Display"', 'serif'],
                        body: ['Poppins', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Playfair Display', serif; }

        /* Sparkle animations */
        .sparkle { animation: sparkle 3s ease-in-out infinite; }
        .sparkle-delay { animation: sparkle 3s ease-in-out 0.8s infinite; }
        .sparkle-delay-2 { animation: sparkle 3s ease-in-out 1.6s infinite; }
        @keyframes sparkle {
            0%, 100% { opacity: 0.2; transform: scale(0.8) rotate(0deg); }
            50% { opacity: 0.9; transform: scale(1.15) rotate(15deg); }
        }

        /* Smooth page transitions */
        main { animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Better prose styling for articles */
        .prose h2 { font-family: 'Playfair Display', serif; color: #1a1040; margin-top: 2em; }
        .prose h3 { font-family: 'Playfair Display', serif; color: #2d1b69; }
        .prose a { color: #5b3e9e; text-decoration: underline; text-underline-offset: 3px; }
        .prose blockquote { border-left-color: #d4a843; background: #fef9ef; padding: 1rem 1.5rem; border-radius: 0 0.75rem 0.75rem 0; }
        .prose img { border-radius: 1rem; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #fef9ef; }
        ::-webkit-scrollbar-thumb { background: #d4a843; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #b8922e; }

        /* Line clamp utilities */
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-3 { display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }

        /* Nav active underline */
        .nav-link-active { position: relative; }
        .nav-link-active::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #d4a843, #f0c75e);
            border-radius: 1px;
        }

        /* Hamburger animation */
        .hamburger-line {
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-cream text-navy min-h-screen flex flex-col font-body">
    {{-- Navigation --}}
    <nav class="bg-navy/95 backdrop-blur-sm sticky top-0 z-50 border-b border-white/10" x-data="{ open: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-20">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-3 group">
                    <img src="/images/logo.jpg" alt="Mouse 28" class="h-11 w-11 rounded-full object-cover ring-2 ring-gold/30 group-hover:ring-gold/60 transition-all">
                    <span class="font-heading text-xl font-bold text-white group-hover:text-gold transition-colors">Mouse <span class="text-gold">28</span></span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-10">
                    <a href="/blog" class="{{ request()->is('blog*') ? 'text-gold nav-link-active' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium tracking-wide">Blog</a>
                    <a href="/episodes" class="{{ request()->is('episodes*') ? 'text-gold nav-link-active' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium tracking-wide">Podcast</a>
                    <a href="/about" class="{{ request()->is('about') ? 'text-gold nav-link-active' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium tracking-wide">About</a>
                </div>

                {{-- Mobile menu button (animated hamburger → X) --}}
                <button @click="open = !open" class="md:hidden text-white/80 hover:text-gold transition-colors relative w-6 h-6" aria-label="Toggle menu">
                    <span class="hamburger-line absolute left-0 w-6 h-0.5 bg-current rounded" :class="open ? 'top-[11px] rotate-45' : 'top-[4px] rotate-0'" style="transform-origin: center;"></span>
                    <span class="hamburger-line absolute left-0 top-[11px] w-6 h-0.5 bg-current rounded" :class="open ? 'opacity-0' : 'opacity-100'"></span>
                    <span class="hamburger-line absolute left-0 w-6 h-0.5 bg-current rounded" :class="open ? 'top-[11px] -rotate-45' : 'top-[18px] rotate-0'" style="transform-origin: center;"></span>
                </button>
            </div>

            {{-- Mobile Nav (slide-down) --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="md:hidden pb-5 border-t border-white/10 mt-2 pt-4"
                 x-cloak>
                <div class="flex flex-col gap-1">
                    <a href="/blog" class="{{ request()->is('blog*') ? 'text-gold bg-white/5' : 'text-white/80' }} hover:text-gold hover:bg-white/5 transition-all text-sm font-medium px-4 py-3 min-h-[44px] flex items-center rounded-lg">Blog</a>
                    <a href="/episodes" class="{{ request()->is('episodes*') ? 'text-gold bg-white/5' : 'text-white/80' }} hover:text-gold hover:bg-white/5 transition-all text-sm font-medium px-4 py-3 min-h-[44px] flex items-center rounded-lg">Podcast</a>
                    <a href="/about" class="{{ request()->is('about') ? 'text-gold bg-white/5' : 'text-white/80' }} hover:text-gold hover:bg-white/5 transition-all text-sm font-medium px-4 py-3 min-h-[44px] flex items-center rounded-lg">About</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-navy text-white/70 relative">
        {{-- Decorative gold gradient top border --}}
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-gold-dark via-gold-light to-gold-dark"></div>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 pt-16 pb-8">
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">
                {{-- Brand / Tagline --}}
                <div class="sm:col-span-2 lg:col-span-1">
                    <a href="/" class="flex items-center gap-2 mb-3">
                        <img src="/images/logo.jpg" alt="Mouse 28" class="h-10 w-10 rounded-full object-cover">
                        <span class="font-heading text-lg font-bold text-white">Mouse <span class="text-gold">28</span></span>
                    </a>
                    <p class="text-sm leading-relaxed text-white/50 mt-2">A family sharing the magic (and the real talk) of Disney parks with a daughter on the autism spectrum.</p>
                </div>

                {{-- Explore --}}
                <div>
                    <h4 class="font-heading text-white font-semibold mb-4 text-sm tracking-wider uppercase">Explore</h4>
                    <div class="flex flex-col gap-2.5 text-sm">
                        <a href="/blog" class="hover:text-gold transition-colors">Blog</a>
                        <a href="/episodes" class="hover:text-gold transition-colors">Podcast</a>
                        <a href="/about" class="hover:text-gold transition-colors">About Us</a>
                    </div>
                </div>

                {{-- Resources --}}
                <div>
                    <h4 class="font-heading text-white font-semibold mb-4 text-sm tracking-wider uppercase">Resources</h4>
                    <div class="flex flex-col gap-2.5 text-sm">
                        <a href="/stories" class="hover:text-gold transition-colors">Family Stories</a>
                        <a href="/contact" class="hover:text-gold transition-colors">Contact Us</a>
                        <a href="/rss/blog" class="hover:text-gold transition-colors">RSS Feed</a>
                    </div>
                </div>

                {{-- Connect --}}
                <div>
                    <h4 class="font-heading text-white font-semibold mb-4 text-sm tracking-wider uppercase">Connect</h4>
                    <div class="flex flex-col gap-2.5 text-sm mb-6">
                        <a href="#" class="hover:text-gold transition-colors">Apple Podcasts</a>
                        <a href="#" class="hover:text-gold transition-colors">Spotify</a>
                        <a href="#" class="hover:text-gold transition-colors">YouTube</a>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="#" class="text-white/40 hover:text-gold transition-colors" aria-label="Instagram">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                        </a>
                        <a href="#" class="text-white/40 hover:text-gold transition-colors" aria-label="TikTok">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.34-6.34V8.73a8.19 8.19 0 004.76 1.52v-3.4a4.85 4.85 0 01-1-.16z"/></svg>
                        </a>
                        <a href="#" class="text-white/40 hover:text-gold transition-colors" aria-label="Facebook">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Newsletter Signup --}}
            <div class="mt-12 pt-8 border-t border-white/10" x-data="{ submitted: false, error: false }">
                <div class="max-w-md">
                    <h4 class="font-heading text-white font-semibold mb-2">Stay in the Loop ✨</h4>
                    <p class="text-sm text-white/50 mb-4">New posts, episodes, and park tips straight to your inbox.</p>
                    <form x-show="!submitted" @submit.prevent="
                        error = false;
                        fetch('/newsletter', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ email: $refs.footerEmail.value })
                        }).then(r => { if (r.ok || r.redirected) { submitted = true } else { error = true } }).catch(() => error = true)
                    " class="flex gap-2">
                        <input x-ref="footerEmail" type="email" name="email" placeholder="your@email.com" required class="flex-1 bg-white/10 border border-white/10 rounded-full px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-colors">
                        <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold text-sm px-6 py-2.5 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 whitespace-nowrap">Subscribe</button>
                    </form>
                    <div x-show="submitted" x-transition class="bg-gold/10 border border-gold/30 rounded-full px-5 py-2.5 text-sm text-gold font-medium">
                        You're in! We'll keep you posted.
                    </div>
                    <div x-show="error" x-transition class="text-red-400 text-sm mt-2">Something went wrong. Please try again.</div>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-white/10 mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs text-white/40">&copy; {{ date('Y') }} Mouse28. All rights reserved.</p>
                <p class="text-xs text-white/40">Made with ✨ in Central Florida</p>
            </div>
        </div>
    </footer>
</body>
</html>
