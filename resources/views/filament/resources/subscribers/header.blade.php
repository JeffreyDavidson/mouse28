<x-filament.page-header
    title="Newsletter Subscribers"
    subtitle="Readers who signed up through the newsletter form"
    class="mb-6"
>
    <x-slot:icon>
        <x-filament::icon
            :icon="\Filament\Support\Icons\Heroicon::OutlinedEnvelopeOpen"
            class="text-mouse-gold-light size-8"
            aria-hidden="true"
        />
    </x-slot:icon>

    <x-slot:stats>
        <x-filament.resource-stat label="Active" tone="gold">{{ $active }}</x-filament.resource-stat>
        <x-filament.resource-stat label="Pending">{{ $pending }}</x-filament.resource-stat>
        <x-filament.resource-stat label="Unsubscribed">{{ $unsubscribed }}</x-filament.resource-stat>
    </x-slot:stats>
</x-filament.page-header>
