<x-filament-widgets::widget>
    <div class="relative h-full overflow-hidden rounded-[1.25rem] border border-mouse-gold/15 bg-linear-to-br from-mouse-navy/95 to-mouse-navy-light/90 p-6">
        <div class="pointer-events-none absolute -top-5 -right-5 size-25 bg-[radial-gradient(circle,rgb(240_199_94/8%),transparent_70%)]"></div>

        <div class="mb-5 flex items-center gap-2">
            <span class="text-lg" aria-hidden="true">✨</span>
            <h3 class="m-0 font-mouse-heading text-lg font-bold text-mouse-cream">Writing Prompt</h3>
            <div class="ml-2 h-px flex-1 bg-linear-to-r from-mouse-gold/30 to-transparent"></div>
        </div>

        <blockquote class="rounded-xl border-l-3 border-mouse-gold/40 bg-mouse-gold/6 p-4">
            <p class="font-mouse-heading text-[1.05rem]/6 text-mouse-cream/90 italic">
                “{{ $this->getPrompt() }}”
            </p>
        </blockquote>

        <p class="mt-3 text-right font-mouse-body text-xs text-mouse-gold-light/50">Refreshes on each visit</p>
    </div>
</x-filament-widgets::widget>
