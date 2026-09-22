@props([
    'title',
    'subtitle',
    'iconTone' => 'gold',
])

<div {{
    $attributes->class([
        'relative rounded-2xl bg-mouse-navy p-5 sm:px-10 sm:py-7',
    ])
}}>
    <div class="relative z-1 flex items-center gap-4 sm:gap-5">
        <div @class([
            'flex size-13 shrink-0 items-center justify-center rounded-xl',
            'bg-mouse-purple/30' => $iconTone === 'purple',
            'bg-mouse-gold/15' => $iconTone !== 'purple',
        ])>
            {{ $icon }}
        </div>

        <div>
            <h2 class="font-mouse-heading text-mouse-gold-light text-xl font-semibold sm:text-2xl">{{ $title }}</h2>
            <p class="font-mouse-body text-mouse-cream/80 mt-1 text-xs sm:text-sm">{{ $subtitle }}</p>
        </div>
    </div>
</div>
