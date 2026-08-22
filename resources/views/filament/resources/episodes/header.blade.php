<x-filament.page-header title="Episodes" subtitle="Manage your podcast episodes" class="mb-6">
    <x-slot:icon>
        <svg class="text-mouse-gold-light size-8" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z" />
            <path d="M19 10v2a7 7 0 0 1-14 0v-2M12 19v3" />
        </svg>
    </x-slot:icon>

    <x-slot:stats>
        <x-filament.resource-stat label="published">{{ $published }}</x-filament.resource-stat>

        @if ($drafts > 0)
            <x-filament.resource-stat :label="str('draft')->plural($drafts)" tone="gold">
                {{ $drafts }}</x-filament.resource-stat>
        @endif
    </x-slot:stats>

    <x-slot:actions>
        <x-filament::button tag="a" :href="$createUrl" color="warning" icon="heroicon-m-plus" class="min-h-12">
            New Episode
        </x-filament::button>
    </x-slot:actions>
</x-filament.page-header>
