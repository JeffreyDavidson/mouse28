<x-filament-widgets::widget>
    <x-filament::section heading="Quick Draft" icon="heroicon-o-pencil-square" collapsible>
        @php
            $form = $this->form;
        @endphp
        <form wire:submit.prevent="saveDraft">
            {{ $form }}
        </form>
    </x-filament::section>
</x-filament-widgets::widget>
