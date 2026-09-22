<x-filament-widgets::widget>
    <x-filament.dashboard-panel heading="Quick Draft" description="Capture an idea now. Come back to finish the story.">
        <form wire:submit="saveDraft">
            {{ $this->form }}
            <div class="mt-6 flex justify-end">
                <x-filament::button type="submit" wire:target="saveDraft" class="min-h-12">
                    Save Draft
                </x-filament::button>
            </div>
        </form>
    </x-filament.dashboard-panel>
</x-filament-widgets::widget>
