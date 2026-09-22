<x-filament.page-header title="Guides" subtitle="Manage your accessibility guides" class="mb-6">
    <x-slot:icon>
        <x-filament::icon
            :icon="\Filament\Support\Icons\Heroicon::OutlinedBookOpen"
            class="text-mouse-gold-light size-8"
            aria-hidden="true"
        />
    </x-slot:icon>

    <x-slot:stats>
        <x-filament.resource-stat label="published">{{ $published }}</x-filament.resource-stat>

        @if ($drafts > 0)
            <x-filament.resource-stat :label="str('draft')->plural($drafts)" tone="gold">
                {{ $drafts }}</x-filament.resource-stat>
        @endif
    </x-slot:stats>

    <x-slot:actions>
        <x-filament::button
            tag="a"
            :href="$createUrl"
            color="warning"
            :icon="\Filament\Support\Icons\Heroicon::OutlinedPlus"
            class="min-h-12"
        >
            New Guide
        </x-filament::button>
    </x-slot:actions>
</x-filament.page-header>
