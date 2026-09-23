@props([
    'title',
    'subtitle',
    'iconTone' => 'gold',
])

<div {{
    $attributes->class([
        'relative rounded-2xl bg-mouse-navy p-5 sm:px-8 sm:py-6',
    ])
}}>
    <div class="relative z-1 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4 sm:gap-5">
            <div @class([
                'flex size-12 shrink-0 items-center justify-center rounded-xl sm:size-14',
                'bg-mouse-purple/30' => $iconTone === 'purple',
                'bg-mouse-gold/15' => $iconTone !== 'purple',
            ])>
                {{ $icon }}
            </div>

            <div>
                <h1 class="font-mouse-heading text-mouse-gold-light text-xl font-semibold sm:text-2xl">{{ $title }}</h1>
                <p class="font-mouse-body text-mouse-cream/80 mt-1 text-sm">{{ $subtitle }}</p>
            </div>
        </div>

        @if (isset($stats) || isset($actions))
            <div class="flex flex-col gap-4 sm:flex-row sm:flex-wrap sm:items-center">
                @isset($stats)
                    <div class="flex flex-wrap gap-3">{{ $stats }}</div>
                @endisset

                @isset($actions)
                    <div class="flex flex-wrap gap-3">{{ $actions }}</div>
                @endisset
            </div>
        @endif
    </div>
</div>
