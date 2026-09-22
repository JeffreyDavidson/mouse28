<x-filament.page-header title="Contact Messages" subtitle="Messages from your site visitors" class="mb-6">
    <x-slot:icon>
        <x-filament::icon
            :icon="\Filament\Support\Icons\Heroicon::OutlinedEnvelope"
            class="text-mouse-gold-light size-8"
            aria-hidden="true"
        />
    </x-slot:icon>

    <x-slot:stats>
        <x-filament.resource-stat label="total">{{ $total }}</x-filament.resource-stat>

        @if ($unread > 0)
            <x-filament.resource-stat label="unread" tone="gold">{{ $unread }}</x-filament.resource-stat>
        @endif
    </x-slot:stats>
</x-filament.page-header>
