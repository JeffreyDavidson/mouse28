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
        .sparkle { animation: sparkle 2s ease-in-out infinite; }
        .sparkle-delay { animation: sparkle 2s ease-in-out 0.5s infinite; }
        .sparkle-delay-2 { animation: sparkle 2s ease-in-out 1s infinite; }
        @keyframes sparkle {
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.2); }
        }
    </style>
    @stack('styles')
</head>
<body class="bg-cream text-navy min-h-screen flex flex-col font-body">
    {{-- Navigation --}}
    <nav class="bg-navy/95 backdrop-blur-sm sticky top-0 z-50 border-b border-white/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                {{-- Logo --}}
                <a href="/" class="flex items-center gap-2 group">
                    <img src="/images/logo.jpg" alt="Mouse 28" class="h-10 w-10 rounded-full object-cover">
                    <span class="font-heading text-xl font-bold text-white group-hover:text-gold transition-colors">Mouse <span class="text-gold">28</span></span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="/blog" class="{{ request()->is('blog*') ? 'text-gold' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium">Blog</a>
                    <a href="/episodes" class="{{ request()->is('episodes*') ? 'text-gold' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium">Episodes</a>
                    <a href="/about" class="{{ request()->is('about') ? 'text-gold' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium">About</a>
                    <a href="/contact" class="{{ request()->is('contact') ? 'text-gold' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium">Contact</a>
                    <a href="#subscribe" class="bg-gold hover:bg-gold-light text-navy font-semibold text-sm px-5 py-2 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 hover:-translate-y-0.5">Subscribe</a>
                </div>

                {{-- Mobile menu button --}}
                <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="md:hidden text-white/80 hover:text-gold" aria-label="Toggle menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            {{-- Mobile Nav --}}
            <div id="mobile-menu" class="hidden md:hidden pb-4 border-t border-white/10 mt-2 pt-4">
                <div class="flex flex-col gap-3">
                    <a href="/blog" class="text-white/80 hover:text-gold transition-colors text-sm font-medium px-2 py-1">Blog</a>
                    <a href="/episodes" class="text-white/80 hover:text-gold transition-colors text-sm font-medium px-2 py-1">Episodes</a>
                    <a href="/about" class="text-white/80 hover:text-gold transition-colors text-sm font-medium px-2 py-1">About</a>
                    <a href="/contact" class="text-white/80 hover:text-gold transition-colors text-sm font-medium px-2 py-1">Contact</a>
                    <a href="#subscribe" class="bg-gold hover:bg-gold-light text-navy font-semibold text-sm px-5 py-2 rounded-full transition-all text-center mt-2">Subscribe</a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-navy text-white/70">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12">
            <div class="grid md:grid-cols-3 gap-8">
                {{-- Brand --}}
                <div>
                    <a href="/" class="flex items-center gap-2 mb-4">
                        <img src="/images/logo.jpg" alt="Mouse 28" class="h-8 w-8 rounded-full object-cover">
                        <span class="font-heading text-lg font-bold text-white">Mouse <span class="text-gold">28</span></span>
                    </a>
                    <p class="text-sm leading-relaxed">Disney parks through the eyes of a family raising a daughter with autism. New episodes weekly.</p>
                </div>

                {{-- Links --}}
                <div>
                    <h4 class="font-heading text-white font-semibold mb-4">Explore</h4>
                    <div class="flex flex-col gap-2 text-sm">
                        <a href="/blog" class="hover:text-gold transition-colors">Blog</a>
                        <a href="/episodes" class="hover:text-gold transition-colors">Episodes</a>
                        <a href="/about" class="hover:text-gold transition-colors">About Us</a>
                    </div>
                </div>

                {{-- Subscribe --}}
                <div id="subscribe">
                    <h4 class="font-heading text-white font-semibold mb-4">Listen On</h4>
                    <div class="flex flex-col gap-2 text-sm">
                        <a href="#" class="hover:text-gold transition-colors">🎧 Apple Podcasts</a>
                        <a href="#" class="hover:text-gold transition-colors">💚 Spotify</a>
                        <a href="#" class="hover:text-gold transition-colors">▶️ YouTube</a>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs">&copy; {{ date('Y') }} Mouse'28 Podcast. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="#" class="text-white/50 hover:text-gold transition-colors" aria-label="Instagram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <a href="#" class="text-white/50 hover:text-gold transition-colors" aria-label="TikTok">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 006.34 6.34 6.34 6.34 0 006.34-6.34V8.73a8.19 8.19 0 004.76 1.52v-3.4a4.85 4.85 0 01-1-.16z"/></svg>
                    </a>
                    <a href="#" class="text-white/50 hover:text-gold transition-colors" aria-label="Facebook">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
