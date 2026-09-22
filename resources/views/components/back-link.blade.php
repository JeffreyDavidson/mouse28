@props(['href', 'label'])

<a
    href="{{ $href }}"
    {{ $attributes->class(['text-cream/65 hover:text-gold inline-flex min-h-12 items-center gap-2 text-sm font-semibold transition-colors']) }}
>
    <svg aria-hidden="true" class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
    </svg>
    {{ $label }}
</a>
