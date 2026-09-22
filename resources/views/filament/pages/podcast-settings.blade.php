<x-filament-panels::page>
    <x-filament.page-header
        title="Podcast Settings"
        subtitle="Configure your podcast info, distribution links, and social media"
        class="mb-6"
    >
        <x-slot:icon>
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::OutlinedMicrophone"
                class="text-mouse-gold-light size-8"
                aria-hidden="true"
            />
        </x-slot:icon>
    </x-filament.page-header>

    {{ $this->form }}
</x-filament-panels::page>
