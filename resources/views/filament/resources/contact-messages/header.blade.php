<x-filament.page-header
    title="Contact Messages"
    subtitle="Messages from your site visitors"
    class="mb-6"
>
    <x-slot:icon>
        <svg class="size-8 text-mouse-gold-light" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="4" width="20" height="16" rx="2"/>
            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
        </svg>
    </x-slot:icon>

    <x-slot:stats>
        <x-filament.resource-stat label="total">{{ $total }}</x-filament.resource-stat>

        @if ($unread > 0)
            <x-filament.resource-stat label="unread" tone="gold">{{ $unread }}</x-filament.resource-stat>
        @endif
    </x-slot:stats>
</x-filament.page-header>
