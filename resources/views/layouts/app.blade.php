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
@if(config('services.fathom.site_id'))
<script src="https://cdn.usefathom.com/script.js" data-site="{{ config('services.fathom.site_id') }}" defer></script>
@endif
</head>
<body class="bg-cream text-navy min-h-screen flex flex-col font-body">
    {{-- Navigation --}}
    <nav class="bg-navy/95 backdrop-blur-sm sticky top-0 z-50 border-b border-white/10" x-data="{ open: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-20">
                {{-- Logo --}}
                <a href="/" class="group" style="letter-spacing: -0.02em;">
                    <span class="font-heading text-2xl font-bold text-white group-hover:text-gold transition-colors">Mouse<span class="text-gold">28</span></span>
                </a>

                {{-- Desktop Nav --}}
                <div class="hidden md:flex items-center gap-10">
                    <a href="/" class="{{ request()->is('/') ? 'text-gold nav-link-active' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium tracking-wide">Home</a>
                    <a href="/blog" class="{{ request()->is('blog*') ? 'text-gold nav-link-active' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium tracking-wide">Blog</a>
                    <a href="/episodes" class="{{ request()->is('episodes*') ? 'text-gold nav-link-active' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium tracking-wide">Podcast</a>
                    <a href="/about" class="{{ request()->is('about') ? 'text-gold nav-link-active' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium tracking-wide">About</a>
                    <a href="/contact" class="{{ request()->is('contact') ? 'text-gold nav-link-active' : 'text-white/80' }} hover:text-gold transition-colors text-sm font-medium tracking-wide">Contact</a>
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
                    <a href="/" class="{{ request()->is('/') ? 'text-gold bg-white/5' : 'text-white/80' }} hover:text-gold hover:bg-white/5 transition-all text-sm font-medium px-4 py-3 min-h-[44px] flex items-center rounded-lg">Home</a>
                    <a href="/blog" class="{{ request()->is('blog*') ? 'text-gold bg-white/5' : 'text-white/80' }} hover:text-gold hover:bg-white/5 transition-all text-sm font-medium px-4 py-3 min-h-[44px] flex items-center rounded-lg">Blog</a>
                    <a href="/episodes" class="{{ request()->is('episodes*') ? 'text-gold bg-white/5' : 'text-white/80' }} hover:text-gold hover:bg-white/5 transition-all text-sm font-medium px-4 py-3 min-h-[44px] flex items-center rounded-lg">Podcast</a>
                    <a href="/about" class="{{ request()->is('about') ? 'text-gold bg-white/5' : 'text-white/80' }} hover:text-gold hover:bg-white/5 transition-all text-sm font-medium px-4 py-3 min-h-[44px] flex items-center rounded-lg">About</a>
                    <a href="/contact" class="{{ request()->is('contact') ? 'text-gold bg-white/5' : 'text-white/80' }} hover:text-gold hover:bg-white/5 transition-all text-sm font-medium px-4 py-3 min-h-[44px] flex items-center rounded-lg">Contact</a>
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
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-10">
                {{-- Newsletter (left side) --}}
                <div class="lg:max-w-md flex-shrink-0" x-data="{ submitted: false, error: false }">
                    <h4 class="font-heading text-white font-semibold mb-2 text-sm tracking-wider uppercase">Stay in the Loop</h4>
                    <p class="text-sm text-white/50 mb-4">New posts, episodes, and park tips straight to your inbox.</p>
                    <form x-show="!submitted" @submit.prevent="
                        error = false;
                        fetch('/newsletter', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ email: $refs.footerEmail.value })
                        }).then(r => { if (r.ok || r.redirected) { submitted = true } else { error = true } }).catch(() => error = true)
                    " class="flex gap-2">
                        <input x-ref="footerEmail" type="email" name="email" placeholder="your@email.com" required class="flex-1 min-w-0 bg-white/10 border border-white/10 rounded-full px-4 py-2.5 text-sm text-white placeholder-white/30 focus:outline-none focus:border-gold/50 focus:ring-1 focus:ring-gold/30 transition-colors">
                        <button type="submit" class="bg-gold hover:bg-gold-light text-navy font-semibold text-sm px-6 py-2.5 rounded-full transition-all hover:shadow-lg hover:shadow-gold/25 whitespace-nowrap">Subscribe</button>
                    </form>
                    <div x-show="submitted" x-transition class="bg-gold/10 border border-gold/30 rounded-full px-5 py-2.5 text-sm text-gold font-medium text-center">
                        You're in! We'll keep you posted.
                    </div>
                    <div x-show="error" x-transition class="text-red-400 text-sm mt-2">Something went wrong. Please try again.</div>
                </div>

                {{-- Links (right side) --}}
                <div class="flex gap-16">
                    {{-- Explore --}}
                    <div>
                        <h4 class="font-heading text-white font-semibold mb-4 text-sm tracking-wider uppercase">Explore</h4>
                        <div class="flex flex-col gap-2.5 text-sm">
                            <a href="/blog" class="hover:text-gold transition-colors">Blog</a>
                            <a href="/episodes" class="hover:text-gold transition-colors">Podcast</a>
                            <a href="/about" class="hover:text-gold transition-colors">About Us</a>
                        </div>
                    </div>

                    {{-- Connect --}}
                    <div>
                        <h4 class="font-heading text-white font-semibold mb-4 text-sm tracking-wider uppercase">Connect</h4>
                        <div class="flex flex-col gap-2.5 text-sm">
                            <a href="/contact" class="hover:text-gold transition-colors">Contact Us</a>
                            <a href="#" class="hover:text-gold transition-colors">Apple Podcasts</a>
                            <a href="#" class="hover:text-gold transition-colors">Spotify</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="border-t border-white/10 mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                <p class="text-xs text-white/40">&copy; {{ date('Y') }} Mouse28. All rights reserved.</p>
                <p class="text-xs text-white/40">Made with ✨ from Infinity Digital</p>
            </div>
        </div>
    </footer>
</body>
</html>
