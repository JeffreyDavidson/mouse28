<x-filament-widgets::widget>
    @php $items = $this->getTimeline(); @endphp

    <div class="border-mouse-gold/15 from-mouse-navy/95 to-mouse-navy-light/90 relative h-full overflow-hidden rounded-[1.25rem] border bg-linear-to-br p-6">
        <div class="mb-5 flex items-center gap-2">
            <span class="text-lg" aria-hidden="true">📅</span>
            <h3 class="font-mouse-heading text-mouse-cream m-0 text-lg font-bold">Upcoming Content</h3>
            <div class="from-mouse-gold/30 ml-2 h-px flex-1 bg-linear-to-r to-transparent"></div>
        </div>

        @if (count($items) === 0)
            <div class="px-4 py-8 text-center">
                <div class="mb-3 text-4xl" aria-hidden="true">✨</div>
                <p class="font-mouse-heading text-mouse-cream mb-2 text-base">Your week is wide open!</p>
                <p class="font-mouse-body text-mouse-gold-light/60 text-sm">
                    Schedule some magical content for the next 7 days.
                </p>
            </div>
        @else
            <div class="relative pl-6">
                <div class="from-mouse-gold/30 to-mouse-purple/30 absolute inset-y-1 left-[5px] w-0.5 bg-linear-to-b"></div>

                @foreach ($items as $item)
                    @php
                        $statusClasses = match ($item['status']) {
                            'Published' => [
                                'dot' => 'bg-mouse-gold shadow-[0_0_6px_rgb(212_168_67/27%)]',
                                'badge' => 'bg-mouse-gold/10 text-mouse-gold',
                            ],
                            'Scheduled' => [
                                'dot' => 'bg-mouse-gold-light shadow-[0_0_6px_rgb(240_199_94/27%)]',
                                'badge' => 'bg-mouse-gold-light/10 text-mouse-gold-light',
                            ],
                            default => [
                                'dot' => 'bg-mouse-purple shadow-[0_0_6px_rgb(91_62_158/27%)]',
                                'badge' => 'bg-mouse-purple/10 text-mouse-purple-light',
                            ],
                        };
                    @endphp
                    <a
                        href="{{ $item['url'] }}"
                        class="hover:bg-mouse-gold/5 relative block rounded-xl pb-4 no-underline transition-colors last:pb-0"
                    >
                        <span class="{{ $statusClasses['dot'] }} absolute top-1 -left-6 size-3 rounded-full border-2 border-mouse-navy/80"></span>
                        <div class="rounded-xl px-3 py-2">
                            <p class="font-mouse-body text-mouse-cream text-sm/5 font-medium">{{ $item['title'] }}</p>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="font-mouse-body text-mouse-gold-light/70 text-xs">{{ \Carbon\Carbon::parse($item['date'])->format('M j, g:ia') }}</span>
                                <span class="{{ $statusClasses['badge'] }} rounded-sm px-1.5 py-0.5 font-mouse-body text-[0.65rem] font-semibold tracking-wide uppercase">{{ $item['status'] }}</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
