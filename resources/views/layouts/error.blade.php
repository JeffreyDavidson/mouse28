<!DOCTYPE html>
<html lang="en" class="antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Something Went Wrong — Mouse28')</title>
    <meta name="description" content="@yield('meta_description', 'Mouse28 could not complete this request.')">
    <meta name="robots" content="@yield('robots', 'noindex,nofollow')">

    <meta property="og:title" content="@yield('og_title', 'Mouse28')">
    <meta property="og:description" content="@yield('og_description', 'Disney parks through different eyes.')">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ url('/images/logo.jpg') }}">
    <meta property="og:site_name" content="Mouse28">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-dvh flex-col bg-navy font-body text-white">
    <a href="#main-content" class="fixed top-4 left-4 z-100 -translate-y-24 rounded-lg bg-gold px-4 py-3 font-semibold text-navy shadow-xl transition-transform focus:translate-y-0">
        Skip to content
    </a>

    <header class="border-b border-white/10 bg-navy/95">
        <nav aria-label="Recovery navigation" class="mx-auto flex min-h-20 max-w-6xl items-center justify-between gap-6 px-4 sm:px-6">
            <a href="{{ route('home') }}" aria-label="Mouse28 homepage" class="inline-flex min-h-12 items-center font-heading text-2xl font-bold text-white transition-colors hover:text-gold">Mouse<span class="text-gold">28</span></a>
            <div class="hidden items-center gap-6 sm:flex">
                <a href="{{ route('blog.index') }}" class="inline-flex min-h-12 items-center text-sm font-medium text-white/75 transition-colors hover:text-gold">Blog</a>
                <a href="{{ route('guides.index') }}" class="inline-flex min-h-12 items-center text-sm font-medium text-white/75 transition-colors hover:text-gold">Guides</a>
                <a href="{{ route('episodes.index') }}" class="inline-flex min-h-12 items-center text-sm font-medium text-white/75 transition-colors hover:text-gold">Podcast</a>
            </div>
        </nav>
    </header>

    <main id="main-content" tabindex="-1" class="flex flex-1 flex-col">
        @yield('content')
    </main>

    <footer class="border-t border-white/10 bg-navy px-4 py-6 text-center text-sm text-white/40">
        &copy; {{ date('Y') }} Mouse28
    </footer>
</body>
</html>
