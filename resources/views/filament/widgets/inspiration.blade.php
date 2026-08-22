<x-filament-widgets::widget>
    <div class="border-mouse-gold/15 from-mouse-navy/95 to-mouse-navy-light/90 relative h-full overflow-hidden rounded-[1.25rem] border bg-linear-to-br p-6">
        <div class="pointer-events-none absolute -top-5 -right-5 size-25 bg-[radial-gradient(circle,rgb(240_199_94/8%),transparent_70%)]"></div>

        <div class="mb-5 flex items-center gap-2">
            <span class="text-lg" aria-hidden="true">✨</span>
            <h3 class="font-mouse-heading text-mouse-cream m-0 text-lg font-bold">Writing Prompt</h3>
            <div class="from-mouse-gold/30 ml-2 h-px flex-1 bg-linear-to-r to-transparent"></div>
        </div>

        <blockquote class="bg-mouse-gold/6 border-mouse-gold/40 rounded-xl border-l-3 p-4">
            <p class="font-mouse-heading text-mouse-cream/90 text-[1.05rem]/6 italic">“{{ $this->getPrompt() }}”</p>
        </blockquote>

        <p class="font-mouse-body text-mouse-gold-light/50 mt-3 text-right text-xs">Refreshes on each visit</p>
    </div>
</x-filament-widgets::widget>
