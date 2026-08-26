@props([
    'title' => 'Mouse28 — Disney Parks Through Different Eyes',
    'description' => 'Disney parks through the eyes of a family raising a daughter with autism. Tips, accessibility guides, and stories from Jeffrey & Cassie Davidson.',
    'robots' => 'index,follow',
    'ogTitle' => null,
    'ogDescription' => null,
    'ogType' => 'website',
    'ogImage' => null,
    'canonical' => null,
    'showFooterNewsletter' => true,
])

@php
    $canonicalUrl = $canonical ?: url()->current();
    $socialTitle = $ogTitle ?: $title;
    $socialDescription = $ogDescription ?: $description;
    $socialImage = $ogImage ?: url('/images/logo.jpg');
    $socialImage = Str::startsWith($socialImage, ['http://', 'https://'])
        ? $socialImage
        : url('/'.ltrim($socialImage, '/'));

    \Laravel\Head\Facades\Head::title($title)
        ->description($description)
        ->robots($robots)
        ->canonical($canonicalUrl, forceHttps: Str::startsWith($canonicalUrl, 'https://'))
        ->og(
            type: $ogType,
            title: $socialTitle,
            description: $socialDescription,
            url: $canonicalUrl,
            siteName: 'Mouse28',
        )
        ->ogImage($socialImage)
        ->twitter(
            card: \Laravel\Head\Enums\TwitterCard::SummaryWithLargeImage,
            title: $socialTitle,
            description: $socialDescription,
            image: $socialImage,
        )
        ->feed(route('rss.blog'), 'Mouse28 Blog')
        ->feed(route('rss.podcast'), 'Mouse28 Podcast');
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#1a1040" />
    <link rel="preload" href="/fonts/mouse28/poppins-400.woff2" as="font" type="font/woff2" crossorigin />
    <link rel="preload" href="/fonts/mouse28/playfair-latin.woff2" as="font" type="font/woff2" crossorigin />
    @head

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    @stack('head')
    @if (config('services.fathom.site_id'))
        <script
            src="https://cdn.usefathom.com/script.js"
            data-site="{{ config('services.fathom.site_id') }}"
            defer
        ></script>
    @endif
</head>
<body class="bg-cream font-body text-navy flex min-h-dvh flex-col">
    <a
        href="#main-content"
        class="bg-gold text-navy fixed top-4 left-4 z-100 -translate-y-24 rounded-lg px-4 py-3 font-semibold shadow-xl transition-transform focus:translate-y-0"
    >
        Skip to content
    </a>

    {{-- Navigation --}}
    <header class="sticky top-0 z-50">
        <nav
            class="bg-navy/95 border-b border-white/10 backdrop-blur-sm"
            aria-label="Primary navigation"
            x-data="{ open: false }"
            @keydown.escape.window="open = false"
        >
            <div class="mx-auto max-w-6xl px-4 sm:px-6">
                <div class="flex h-20 items-center justify-between">
                    {{-- Logo --}}
                    <a
                        href="{{ route('home') }}"
                        aria-label="Mouse28 homepage"
                        class="group inline-flex min-h-12 items-center tracking-tight"
                    >
                        <span class="font-heading group-hover:text-gold text-2xl font-bold text-white transition-colors">Mouse<span class="text-gold">28</span></span>
                    </a>

                    {{-- Desktop Nav --}}
                    <div class="hidden items-center gap-10 md:flex">
                        <a
                            href="{{ route('home') }}"
                            @if (request()->routeIs('home')) aria-current="page" @endif
                            class="{{ request()->routeIs('home') ? 'text-gold nav-link-active' : 'text-white/80' }} inline-flex min-h-12 items-center text-sm font-medium tracking-wide transition-colors hover:text-gold"
                        >Home</a>
                        <a
                            href="{{ route('blog.index') }}"
                            @if (request()->routeIs('blog.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('blog.*') ? 'text-gold nav-link-active' : 'text-white/80' }} inline-flex min-h-12 items-center text-sm font-medium tracking-wide transition-colors hover:text-gold"
                        >Blog</a>
                        <a
                            href="{{ route('episodes.index') }}"
                            @if (request()->routeIs('episodes.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('episodes.*') ? 'text-gold nav-link-active' : 'text-white/80' }} inline-flex min-h-12 items-center text-sm font-medium tracking-wide transition-colors hover:text-gold"
                        >Podcast</a>
                        <a
                            href="{{ route('about') }}"
                            @if (request()->routeIs('about')) aria-current="page" @endif
                            class="{{ request()->routeIs('about') ? 'text-gold nav-link-active' : 'text-white/80' }} inline-flex min-h-12 items-center text-sm font-medium tracking-wide transition-colors hover:text-gold"
                        >About</a>
                        <a
                            href="{{ route('contact.show') }}"
                            @if (request()->routeIs('contact.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('contact.*') ? 'text-gold nav-link-active' : 'text-white/80' }} inline-flex min-h-12 items-center text-sm font-medium tracking-wide transition-colors hover:text-gold"
                        >Contact</a>
                        <a
                            href="{{ route('search') }}"
                            @if (request()->routeIs('search')) aria-current="page" @endif
                            class="{{ request()->routeIs('search') ? 'text-gold' : 'text-white/80' }} inline-flex size-12 items-center justify-center rounded-full transition-colors hover:bg-white/5 hover:text-gold"
                            aria-label="Search Mouse28"
                        >
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" /></svg>
                        </a>
                    </div>

                    {{-- Mobile menu button (animated hamburger → X) --}}
                    <button
                        type="button"
                        @click="open = ! open"
                        :aria-expanded="open.toString()"
                        aria-controls="mobile-navigation"
                        class="hover:text-gold relative -mr-3 size-12 text-white/80 transition-colors md:hidden"
                        :aria-label="open ? 'Close navigation menu' : 'Open navigation menu'"
                    >
                        <span
                            class="hamburger-line absolute left-3 h-0.5 w-6 origin-center rounded bg-current"
                            :class="open ? 'top-[23px] rotate-45' : 'top-[15px] rotate-0'"
                        ></span>
                        <span
                            class="hamburger-line absolute top-[23px] left-3 h-0.5 w-6 rounded bg-current"
                            :class="open ? 'opacity-0' : 'opacity-100'"
                        ></span>
                        <span
                            class="hamburger-line absolute left-3 h-0.5 w-6 origin-center rounded bg-current"
                            :class="open ? 'top-[23px] -rotate-45' : 'top-[31px] rotate-0'"
                        ></span>
                    </button>
                </div>

                {{-- Mobile Nav (slide-down) --}}
                <div
                    id="mobile-navigation"
                    x-show="open"
                    :aria-hidden="open ? 'false' : 'true'"
                    @click.outside="open = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-2"
                    class="mt-2 border-t border-white/10 pt-4 pb-5 md:hidden"
                    x-cloak
                >
                    <div class="flex flex-col gap-1">
                        <a
                            href="{{ route('home') }}"
                            @if (request()->routeIs('home')) aria-current="page" @endif
                            class="{{ request()->routeIs('home') ? 'text-gold bg-white/5' : 'text-white/80' }} flex min-h-12 items-center rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 hover:text-gold"
                        >Home</a>
                        <a
                            href="{{ route('blog.index') }}"
                            @if (request()->routeIs('blog.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('blog.*') ? 'text-gold bg-white/5' : 'text-white/80' }} flex min-h-12 items-center rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 hover:text-gold"
                        >Blog</a>
                        <a
                            href="{{ route('episodes.index') }}"
                            @if (request()->routeIs('episodes.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('episodes.*') ? 'text-gold bg-white/5' : 'text-white/80' }} flex min-h-12 items-center rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 hover:text-gold"
                        >Podcast</a>
                        <a
                            href="{{ route('about') }}"
                            @if (request()->routeIs('about')) aria-current="page" @endif
                            class="{{ request()->routeIs('about') ? 'text-gold bg-white/5' : 'text-white/80' }} flex min-h-12 items-center rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 hover:text-gold"
                        >About</a>
                        <a
                            href="{{ route('contact.show') }}"
                            @if (request()->routeIs('contact.*')) aria-current="page" @endif
                            class="{{ request()->routeIs('contact.*') ? 'text-gold bg-white/5' : 'text-white/80' }} flex min-h-12 items-center rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 hover:text-gold"
                        >Contact</a>
                        <a
                            href="{{ route('search') }}"
                            @if (request()->routeIs('search')) aria-current="page" @endif
                            class="{{ request()->routeIs('search') ? 'text-gold bg-white/5' : 'text-white/80' }} flex min-h-12 items-center rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 hover:text-gold"
                        >Search</a>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    {{-- Main Content --}}
    <main id="main-content" tabindex="-1" class="isolate flex-1">{{ $slot }}</main>

    {{-- Footer --}}
    <footer class="bg-navy relative text-white/70">
        {{-- Decorative gold gradient top border --}}
        <div class="from-gold-dark via-gold-light to-gold-dark absolute inset-x-0 top-0 h-1 bg-linear-to-r"></div>

        <div class="mx-auto max-w-6xl px-4 pt-16 pb-8 sm:px-6">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-start lg:justify-between">
                @if ($showFooterNewsletter)
                    {{-- Newsletter (left side) --}}
                    <div class="shrink-0 lg:max-w-md">
                        <h2 class="font-heading mb-2 text-base font-semibold tracking-wider text-white uppercase sm:text-sm">
                            Stay in the Loop
                        </h2>
                        <p class="mb-4 text-base text-white/50 sm:text-sm">
                            New posts, episodes, and park tips straight to your inbox.
                        </p>
                        @if (session('newsletter_success'))
                            <div
                                role="status"
                                class="border-gold/30 bg-gold/10 text-gold mb-3 rounded-xl border px-5 py-2.5 text-center text-base font-medium sm:text-sm"
                            >
                                You're in! We'll keep you posted.
                            </div>
                        @elseif (session('newsletter_error'))
                            <p role="alert" class="mb-3 text-base text-red-300 sm:text-sm">
                                {{ session('newsletter_error') }}
                            </p>
                        @endif
                        <form action="{{ route('newsletter.store') }}" method="POST" class="flex flex-col gap-2">
                            @csrf
                            <x-newsletter-protection honeypot-id="footer-newsletter-website" />
                            <div class="flex flex-col gap-2 sm:flex-row">
                                <label for="footer-newsletter-email" class="sr-only">Email address</label>
                                <input
                                    id="footer-newsletter-email"
                                    type="email"
                                    name="email"
                                    value="{{ $errors->newsletter->isNotEmpty() || session('newsletter_error') ? old('email') : '' }}"
                                    placeholder="your@email.com"
                                    autocomplete="email"
                                    required
                                    @error('email', 'newsletter') aria-invalid="true" aria-describedby="footer-newsletter-email-error" @enderror
                                    class="focus:border-gold/50 focus:ring-gold/30 min-h-12 min-w-0 flex-1 rounded-full border border-white/10 bg-white/10 px-4 py-2.5 text-base text-white placeholder-white/60 transition-colors focus:ring-1 focus:outline-none sm:text-sm"
                                />
                                <button
                                    type="submit"
                                    class="bg-gold text-navy hover:bg-gold-light hover:shadow-gold/25 min-h-12 rounded-full px-6 py-2.5 text-base font-semibold whitespace-nowrap transition-[background-color,box-shadow] hover:shadow-lg sm:text-sm"
                                >
                                    Subscribe
                                </button>
                            </div>
                            @error('email', 'newsletter')
                                <p id="footer-newsletter-email-error" role="alert" class="text-sm text-red-300">
                                    {{ $message }}
                                </p>
                            @enderror
                            <p class="text-xs text-white/60">We use your email to send Mouse28 updates.</p>
                        </form>
                    </div>
                @endif

                {{-- Links (right side) --}}
                <div class="flex flex-wrap gap-x-16 gap-y-8">
                    {{-- Explore --}}
                    <div>
                        <h2 class="font-heading mb-4 text-base font-semibold tracking-wider text-white uppercase sm:text-sm">
                            Explore
                        </h2>
                        <div class="flex flex-col gap-1 text-base sm:text-sm">
                            <a
                                href="{{ route('blog.index') }}"
                                class="hover:text-gold inline-flex min-h-12 items-center transition-colors sm:min-h-6"
                            >Blog</a>
                            <a
                                href="{{ route('guides.index') }}"
                                class="hover:text-gold inline-flex min-h-12 items-center transition-colors sm:min-h-6"
                            >Guides</a>
                            <a
                                href="{{ route('episodes.index') }}"
                                class="hover:text-gold inline-flex min-h-12 items-center transition-colors sm:min-h-6"
                            >Podcast</a>
                            <a
                                href="{{ route('about') }}"
                                class="hover:text-gold inline-flex min-h-12 items-center transition-colors sm:min-h-6"
                            >About Us</a>
                        </div>
                    </div>

                    {{-- Connect --}}
                    <div>
                        <h2 class="font-heading mb-4 text-base font-semibold tracking-wider text-white uppercase sm:text-sm">
                            Connect
                        </h2>
                        <div class="flex flex-col gap-1 text-base sm:text-sm">
                            <a
                                href="{{ route('contact.show') }}"
                                class="hover:text-gold inline-flex min-h-12 items-center transition-colors sm:min-h-6"
                            >Contact Us</a>
                            @foreach ($podcast->distributionLinks() as $link)
                                <a
                                    href="{{ $link['url'] }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="hover:text-gold inline-flex min-h-12 items-center transition-colors sm:min-h-6"
                                >{{ $link['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="mt-8 flex flex-col items-center justify-between gap-4 border-t border-white/10 pt-6 sm:flex-row">
                <p class="text-base text-white/60 sm:text-sm">&copy; {{ date('Y') }} Mouse28. All rights reserved.</p>
                <p class="text-base text-white/60 sm:text-sm">Made with ✨ from Infinity Digital</p>
            </div>
        </div>
    </footer>
</body>
</html>
