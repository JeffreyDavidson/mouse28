<x-filament.page-header title="Episodes" subtitle="Manage your podcast episodes" class="mb-6">
    <x-slot:icon>
        <x-filament::icon
            :icon="\Filament\Support\Icons\Heroicon::OutlinedMicrophone"
            class="text-mouse-gold-light size-8"
            aria-hidden="true"
        />
    </x-slot:icon>

    <x-slot:stats>
        <x-filament.resource-stat label="Published">{{ $published }}</x-filament.resource-stat>
        <x-filament.resource-stat label="Drafts" tone="gold">{{ $drafts }}</x-filament.resource-stat>
    </x-slot:stats>

    <x-slot:actions>
        <x-filament::button
            tag="a"
            :href="$createUrl"
            color="warning"
            :icon="\Filament\Support\Icons\Heroicon::OutlinedPlus"
            class="min-h-12"
        >
            New Episode
        </x-filament::button>
    </x-slot:actions>
</x-filament.page-header>
