@props([
    'title' => 'Something Went Wrong | Mouse28',
    'description' => 'Mouse28 could not complete this request.',
    'ogTitle' => 'Mouse28',
    'ogDescription' => 'Disney parks through different eyes.',
])

@php
    \Laravel\Head\Facades\Head::title($title)
        ->description($description)
        ->hiddenFromRobots()
        ->og(
            type: \Laravel\Head\Enums\OgType::Website,
            title: $ogTitle,
            description: $ogDescription,
            siteName: 'Mouse28',
        )
        ->ogImage(url('/images/logo.jpg'));
@endphp

<!DOCTYPE html>
<html lang="en" class="scroll-smooth antialiased">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @head

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                <a
                    href="{{ route('blog.index') }}"
                    class="hover:text-gold inline-flex min-h-12 items-center text-sm font-medium text-white/75 transition-colors"
                >Blog</a>
                @if (config('mouse28.guides_enabled'))
                    <a
                        href="{{ route('guides.index') }}"
                        class="hover:text-gold inline-flex min-h-12 items-center text-sm font-medium text-white/75 transition-colors"
                    >Guides</a>
                @endif
                <a
                    href="{{ route('episodes.index') }}"
                    class="hover:text-gold inline-flex min-h-12 items-center text-sm font-medium text-white/75 transition-colors"
                >Podcast</a>
            </div>
        </nav>
    </header>

    <main id="main-content" tabindex="-1" class="flex flex-1 flex-col">{{ $slot }}</main>

    <footer class="bg-navy border-gold/25 border-t px-4 py-6 text-center text-sm text-white/60">
        <a href="{{ route('home') }}" aria-label="Mouse28 homepage" class="inline-flex min-h-12 items-center">
            <x-brand-wordmark compact />
        </a>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            document.documentElement.classList.add('js-dispatch-errors');
        });
    </script>
</body>
</html>
