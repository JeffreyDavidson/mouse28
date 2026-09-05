@props([
    'previousEpisode' => null,
    'nextEpisode' => null,
    'compact' => false,
])

@if ($previousEpisode || $nextEpisode)
    <nav
        aria-label="Episode navigation"
        data-episode-continuation="{{ $compact ? 'compact' : 'full' }}"
        {{ $attributes->class(['border-gold/35 mt-12 grid gap-x-8 border-y py-6', 'sm:grid-cols-2' => ! $compact]) }}
    >
        @if ($previousEpisode)
            <a
                href="{{ route('episodes.show', $previousEpisode) }}"
                rel="prev"
                class="group flex min-h-20 flex-col justify-center wrap-anywhere"
            >
                <span class="text-navy/60 text-sm">← Previous episode</span>
                <span class="font-heading text-navy group-hover:text-purple mt-1 text-lg [font-weight:600] transition-colors">{{ $previousEpisode->title }}</span>
            </a>
        @endif
        @if ($nextEpisode)
            <a
                href="{{ route('episodes.show', $nextEpisode) }}"
                rel="next"
                class="group flex min-h-20 flex-col justify-center wrap-anywhere {{ ! $compact ? 'sm:col-start-2 sm:text-right' : '' }}"
            >
                <span class="text-navy/60 text-sm">Next episode →</span>
                <span class="font-heading text-navy group-hover:text-purple mt-1 text-lg [font-weight:600] transition-colors">{{ $nextEpisode->title }}</span>
            </a>
        @endif
    </nav>
@endif
