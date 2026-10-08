@use('App\Enums\SiteNavigationPlacement')

@foreach ($links() as $link)
    @switch ($placement)
        @case (SiteNavigationPlacement::Desktop)
            @if ($link['isSearch'])
                <a
                    href="{{ $link['href'] }}"
                    @if ($link['active']) aria-current="page" @endif
                    class="dispatch-nav-search inline-flex size-12 items-center justify-center rounded-full"
                    aria-label="Search Mouse28"
                >
                    <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" /></svg>
                </a>
            @else
                <a
                    href="{{ $link['href'] }}"
                    @if ($link['active']) aria-current="page" @endif
                    class="dispatch-nav-link inline-flex min-h-12 items-center text-sm font-medium tracking-wide"
                >{{ $link['label'] }}</a>
            @endif
            @break
        @case (SiteNavigationPlacement::Noscript)
            <a
                href="{{ $link['href'] }}"
                class="inline-flex min-h-12 items-center rounded-lg px-4 py-3 text-base font-medium text-white/80"
            >{{ $link['label'] }}</a>
            @break
        @case (SiteNavigationPlacement::Mobile)
            <a
                href="{{ $link['href'] }}"
                @if ($link['active']) aria-current="page" @endif
                class="{{ $link['active'] ? 'text-gold bg-white/5' : 'text-white/80' }} flex min-h-12 items-center rounded-lg px-4 py-3 text-base font-medium transition-colors hover:bg-white/5 hover:text-gold"
            >{{ $link['label'] }}</a>
            @break
        @case (SiteNavigationPlacement::FooterExplore)
        @case (SiteNavigationPlacement::FooterConnect)
            <a
                href="{{ $link['href'] }}"
                class="hover:text-gold inline-flex min-h-12 items-center transition-colors sm:min-h-6"
            >{{ $link['label'] }}</a>
            @break
        @case (SiteNavigationPlacement::Recovery)
            <a
                href="{{ $link['href'] }}"
                class="hover:text-gold inline-flex min-h-12 items-center text-sm font-medium text-white/75 transition-colors"
            >{{ $link['label'] }}</a>
            @break
    @endswitch
@endforeach
