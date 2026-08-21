@props([
    'title',
    'subtitle',
    'iconTone' => 'gold',
])

<div {{ $attributes->class([
    'relative overflow-hidden rounded-2xl border border-mouse-gold/20 bg-linear-to-br from-mouse-navy via-mouse-navy-light to-mouse-purple p-5 sm:p-8',
]) }}>
    <div class="pointer-events-none absolute top-[-30%] right-[-10%] size-62.5 bg-[radial-gradient(circle,rgba(212,168,67,0.12)_0%,transparent_70%)]" aria-hidden="true"></div>
    <span class="pointer-events-none absolute top-[15%] right-[18%] text-xs text-mouse-gold-light/25" aria-hidden="true">✦</span>
    <span class="pointer-events-none absolute right-[10%] bottom-[20%] text-base text-mouse-gold-light/20" aria-hidden="true">✦</span>

    <div class="relative z-1 flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
        <div class="flex items-center gap-4 sm:gap-6">
            <div @class([
                'flex size-14 shrink-0 items-center justify-center rounded-2xl sm:size-16',
                'bg-mouse-purple/30' => $iconTone === 'purple',
                'bg-mouse-gold/15' => $iconTone !== 'purple',
            ])>
                {{ $icon }}
            </div>

            <div>
                <h2 class="font-mouse-heading text-xl font-bold text-mouse-gold-light sm:text-2xl">{{ $title }}</h2>
                <p class="mt-1 font-mouse-body text-sm text-mouse-cream/60">{{ $subtitle }}</p>
            </div>
        </div>

        @if (isset($stats) || isset($actions))
            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center">
                @isset($stats)
                    <div class="flex flex-wrap gap-3">
                        {{ $stats }}
                    </div>
                @endisset

                @isset($actions)
                    <div class="flex flex-wrap gap-3">
                        {{ $actions }}
                    </div>
                @endisset
            </div>
        @endif
    </div>
</div>
