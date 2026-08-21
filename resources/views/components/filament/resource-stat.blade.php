@props([
    'label',
    'tone' => 'purple',
])

<div {{ $attributes->class([
    'min-w-24 rounded-xl border px-5 py-2 text-center',
    'border-mouse-purple/40 bg-mouse-purple/30' => $tone === 'purple',
    'border-mouse-gold/25 bg-mouse-gold/12' => $tone === 'gold',
]) }}>
    <span @class([
        'block font-mouse-heading text-2xl font-bold',
        'text-mouse-cream' => $tone === 'purple',
        'text-mouse-gold-light' => $tone === 'gold',
    ])>{{ $slot }}</span>
    <span @class([
        'block font-mouse-body text-xs',
        'text-mouse-cream/60' => $tone === 'purple',
        'text-mouse-gold-light/70' => $tone === 'gold',
    ])>{{ $label }}</span>
</div>
