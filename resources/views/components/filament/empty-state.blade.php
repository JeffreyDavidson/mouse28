@props([
    'title',
    'description',
    'icon' => null,
])

<div
    {{ $attributes->class(['fi-ta-ctn flex flex-col items-center justify-center px-6 py-16 text-center']) }}
    role="status"
>
    @if ($icon)
        <x-filament::icon :icon="$icon" class="text-mouse-gold/50 mb-4 size-14" aria-hidden="true" />
    @endif

    <h2 class="font-mouse-heading text-mouse-navy text-xl font-bold">{{ $title }}</h2>
    <p class="font-mouse-body text-mouse-navy/75 mt-2 max-w-prose text-sm">{{ $description }}</p>

    {{ $slot }}
</div>
