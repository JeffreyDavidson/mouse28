@props(['href', 'label', 'description'])

<a
    href="{{ $href }}"
    target="_blank"
    rel="noopener noreferrer"
    {{ $attributes->class(['text-gold hover:text-cream inline-flex min-h-12 flex-col justify-center gap-1.5']) }}
>
    <span class="font-semibold underline underline-offset-8">{{ $label }}</span>
    <span class="text-cream/55 text-xs">{{ $description }}</span>
</a>
