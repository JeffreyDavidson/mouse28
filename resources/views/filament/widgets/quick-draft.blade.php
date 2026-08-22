<x-filament-widgets::widget>
    <div class="border-mouse-gold/15 from-mouse-navy/95 to-mouse-navy-light/90 relative h-full overflow-hidden rounded-[1.25rem] border bg-linear-to-br p-6">
        <div class="mb-5 flex items-center gap-2">
            <span class="text-lg" aria-hidden="true">✏️</span>
            <h3 class="font-mouse-heading text-mouse-cream m-0 text-lg font-bold">Quick Draft</h3>
            <div class="from-mouse-gold/30 ml-2 h-px flex-1 bg-linear-to-r to-transparent"></div>
        </div>

        <form wire:submit.prevent="saveDraft">
            {{ $this->form }}

            <div class="mt-4 flex justify-end">
                <button
                    type="submit"
                    class="font-mouse-body from-mouse-gold hover:shadow-mouse-gold/20 text-mouse-navy to-mouse-gold-light inline-flex min-h-10 items-center gap-1.5 rounded-xl bg-linear-to-br px-5 py-2.5 text-sm font-semibold transition hover:-translate-y-0.5 hover:shadow-lg"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" /></svg>
                    Save Draft
                </button>
            </div>
        </form>
    </div>
</x-filament-widgets::widget>
