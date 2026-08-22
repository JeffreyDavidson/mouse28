@props([
    'title',
    'subtitle',
    'iconTone' => 'gold',
])

<div {{
    $attributes->class([
        'relative overflow-hidden rounded-2xl border border-mouse-gold/20 bg-linear-to-br from-mouse-navy via-mouse-navy-light to-mouse-purple p-5 sm:px-10 sm:py-7',
    ])
}}>
    <div
        class="pointer-events-none absolute top-[-30%] right-[-10%] size-50 bg-[radial-gradient(circle,rgba(212,168,67,0.1)_0%,transparent_70%)]"
        aria-hidden="true"
    ></div>
    <span class="text-mouse-gold-light/20 pointer-events-none absolute top-[20%] right-[15%] text-xs" aria-hidden="true"
        >✦</span>

    <div class="relative z-1 flex items-center gap-4 sm:gap-5">
        <div @class([
            'flex size-13 shrink-0 items-center justify-center rounded-[0.875rem]',
            'bg-mouse-purple/30' => $iconTone === 'purple',
            'bg-mouse-gold/15' => $iconTone !== 'purple',
        ])>
            {{ $icon }}
        </div>

        <div>
            <h2 class="font-mouse-heading text-mouse-gold-light text-xl font-bold sm:text-[1.35rem]">{{ $title }}</h2>
            <p class="font-mouse-body text-mouse-cream/60 mt-1 text-xs sm:text-sm">{{ $subtitle }}</p>
        </div>
    </div>
</div>
