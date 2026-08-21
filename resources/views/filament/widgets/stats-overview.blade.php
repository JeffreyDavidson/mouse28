<x-filament-widgets::widget>
    @php $stats = $this->getStats(); @endphp

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            @php
                $accentClasses = match($stat['color']) {
                    '#d4a843' => [
                        'surface' => 'bg-mouse-gold/15',
                        'icon' => 'text-mouse-gold',
                    ],
                    '#e8a838' => [
                        'surface' => 'bg-amber-400/15',
                        'icon' => 'text-amber-400',
                    ],
                    '#7b5eb5' => [
                        'surface' => 'bg-mouse-purple-light/15',
                        'icon' => 'text-mouse-purple-light',
                    ],
                    default => [
                        'surface' => 'bg-mouse-purple/15',
                        'icon' => 'text-mouse-purple-light',
                    ],
                };
            @endphp
            <div class="border-mouse-gold/15 from-mouse-navy/95 group hover:border-mouse-gold/40 to-mouse-navy-light/90 relative overflow-hidden rounded-[1.25rem] border bg-linear-to-br p-6 transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_8px_25px_rgb(212_168_67/15%)]">
                <div class="pointer-events-none absolute top-0 right-0 size-20 bg-[radial-gradient(circle_at_top_right,rgb(212_168_67/8%),transparent_70%)]"></div>
                <div class="flex items-start gap-4">
                    <div class="{{ $accentClasses['surface'] }} flex size-12 shrink-0 items-center justify-center rounded-xl">
                        <x-filament::icon :icon="$stat['icon']" class="{{ $accentClasses['icon'] }} size-6" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-mouse-body text-mouse-cream/60 text-xs font-medium tracking-wider uppercase">{{ $stat['label'] }}</p>
                        <p class="font-mouse-heading text-mouse-cream my-1 text-3xl/9 font-bold">{{ $stat['value'] }}</p>
                        <p class="font-mouse-body text-mouse-gold-light/70 text-xs">{{ $stat['description'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-widgets::widget>
