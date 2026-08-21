@props([
    'title',
    'subtitle',
    'iconTone' => 'gold',
])

<div {{ $attributes->class([
    'relative overflow-hidden rounded-2xl border border-mouse-gold/20 bg-linear-to-br from-mouse-navy via-mouse-navy-light to-mouse-purple p-5 sm:px-10 sm:py-7',
]) }}>
    <div class="pointer-events-none absolute top-[-30%] right-[-10%] size-50 bg-[radial-gradient(circle,rgba(212,168,67,0.1)_0%,transparent_70%)]" aria-hidden="true"></div>
    <span class="pointer-events-none absolute top-[20%] right-[15%] text-xs text-mouse-gold-light/20" aria-hidden="true">✦</span>

    <div class="relative z-1 flex items-center gap-4 sm:gap-5">
        <div @class([
            'flex size-13 shrink-0 items-center justify-center rounded-[0.875rem]',
            'bg-mouse-purple/30' => $iconTone === 'purple',
            'bg-mouse-gold/15' => $iconTone !== 'purple',
        ])>
            {{ $icon }}
        </div>

        <div>
            <h2 class="font-mouse-heading text-xl font-bold text-mouse-gold-light sm:text-[1.35rem]">{{ $title }}</h2>
            <p class="mt-1 font-mouse-body text-xs text-mouse-cream/60 sm:text-sm">{{ $subtitle }}</p>
        </div>
    </div>
</div>
