<x-filament.page-header title="Guides" subtitle="Manage your accessibility guides" class="mb-6">
    <x-slot:icon>
        <svg class="text-mouse-gold-light size-8" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
            <path d="M8 7h8M8 11h8" />
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
            New Guide
        </x-filament::button>
    </x-slot:actions>
</x-filament.page-header>
