@props([
    'previousEpisode' => null,
    'nextEpisode' => null,
    'compact' => false,
])

@if ($previousEpisode || $nextEpisode)
    <nav
        aria-label="Episode navigation"
        data-episode-continuation="{{ $compact ? 'compact' : 'full' }}"
        {{ $attributes->class(['grid gap-4', 'sm:grid-cols-2' => ! $compact, 'mt-12' => ! $compact]) }}
    >
        @if ($previousEpisode)
            <a
                href="{{ route('episodes.show', $previousEpisode) }}"
                rel="prev"
                class="dispatch-interactive-card group border-navy/5 hover:border-purple/20 flex min-h-24 flex-col justify-center rounded-2xl border bg-white px-6 py-5 wrap-anywhere shadow-sm"
            >
                <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">← Previous episode</span>
                <span class="font-heading text-navy group-hover:text-purple mt-2 text-lg font-bold transition-colors">{{ $previousEpisode->title }}</span>
            </a>
        @endif
        @if ($nextEpisode)
            <a
                href="{{ route('episodes.show', $nextEpisode) }}"
                rel="next"
                class="dispatch-interactive-card group border-navy/5 hover:border-purple/20 flex min-h-24 flex-col justify-center rounded-2xl border bg-white px-6 py-5 text-right wrap-anywhere shadow-sm {{ ! $compact ? 'sm:col-start-2' : '' }}"
            >
                <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">Next episode →</span>
                <span class="font-heading text-navy group-hover:text-purple mt-2 text-lg font-bold transition-colors">{{ $nextEpisode->title }}</span>
            </a>
        @endif
    </nav>
@endif
