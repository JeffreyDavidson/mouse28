@props([
    'title' => 'Something Went Wrong | Mouse28',
    'description' => 'Mouse28 could not complete this request.',
    'ogTitle' => 'Mouse28',
])

@php
    $seoData = new \RalphJSmit\Laravel\SEO\Support\SEOData(
        title: $title,
        description: $description,
        image: url('/images/logo.jpg'),
        url: url()->current(),
        enableTitleSuffix: false,
        site_name: 'Mouse28',
        locale: '',
        robots: 'noindex, nofollow',
        openGraphTitle: $ogTitle,
    );

    // An error page has no URL of its own to point search engines at.
    $seoTags = seo($seoData);
    $seoTags->tags = $seoTags->tags->reject(fn (object $tag): bool => $tag instanceof \RalphJSmit\Laravel\SEO\Tags\CanonicalTag);
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {!! $seoTags !!}

    @vite('resources/css/app.css')
</head>
<body class="bg-navy font-body flex min-h-dvh flex-col text-white">
    <a
        href="#main-content"
        class="bg-gold text-navy fixed top-4 left-4 z-100 -translate-y-24 rounded-lg px-4 py-3 font-semibold shadow-xl transition-transform focus:translate-y-0"
    >
        Skip to content
    </a>

    <header class="dispatch-cloth border-gold/25 border-b">
        <nav
            aria-label="Recovery navigation"
            class="mx-auto flex min-h-20 max-w-[86rem] items-center justify-between gap-6 px-4 sm:px-6"
        >
            <a
                href="{{ route('home') }}"
                aria-label="Mouse28 homepage"
                class="group inline-flex min-h-12 items-center py-2"
            >
                <x-brand-wordmark />
            </a>
            <div class="hidden items-center gap-6 sm:flex">
                <x-site-navigation placement="recovery" />
            </div>
        </nav>
    </header>

    <main id="main-content" tabindex="-1" class="flex flex-1 flex-col">{{ $slot }}</main>

    <footer class="bg-navy border-gold/25 border-t px-4 py-6 text-center text-sm text-white/60">
        <a href="{{ route('home') }}" aria-label="Mouse28 homepage" class="inline-flex min-h-12 items-center">
            <x-brand-wordmark compact />
        </a>
    </footer>
    <script nonce="{{ Vite::cspNonce() }}">
        document.addEventListener('DOMContentLoaded', function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            document.documentElement.classList.add('js-dispatch-errors');
        });
    </script>
</body>
</html>
