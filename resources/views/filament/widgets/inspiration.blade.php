<x-filament-widgets::widget>
    <x-filament.dashboard-panel heading="Writing Prompt" description="A little inspiration for your next story.">
        <blockquote>
            <p class="font-mouse-heading text-mouse-navy text-lg text-pretty">“{{ $this->getPrompt() }}”</p>
        </blockquote>
        <p class="text-mouse-navy/75 mt-4 text-sm">Refreshes on each visit</p>
    </x-filament.dashboard-panel>
</x-filament-widgets::widget>
