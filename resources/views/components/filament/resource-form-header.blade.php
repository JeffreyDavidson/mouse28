@props([
    'title',
    'subtitle',
    'iconTone' => 'gold',
])

<x-filament.page-header :$title :$subtitle :icon-tone="$iconTone" {{ $attributes }}>
    <x-slot:icon>{{ $icon }}</x-slot:icon>
</x-filament.page-header>
