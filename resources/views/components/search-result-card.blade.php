@props(['href', 'eyebrow', 'title', 'description' => null])

<a
    href="{{ $href }}"
    {{ $attributes->class(['dispatch-interactive-card group border-navy/5 block rounded-2xl border bg-white p-6 shadow-sm']) }}
>
    <span class="text-gold-ink text-xs font-bold tracking-widest uppercase">{{ $eyebrow }}</span>
    <h3 class="font-heading text-navy group-hover:text-purple mt-2 text-2xl font-bold transition-colors">
        {{ $title }}
    </h3>
    @if ($description)
        <p class="text-navy/65 mt-3 text-sm/relaxed">{{ Str::limit($description, 150) }}</p>
    @endif
</a>
