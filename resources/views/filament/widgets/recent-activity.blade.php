<x-filament-widgets::widget>
    @php $activity = $this->getActivity(); @endphp

    <div class="bg-mouse-navy-light/30 border-mouse-gold/15 overflow-hidden rounded-[1.25rem] border">
        <div class="border-mouse-gold/10 border-b px-6 py-5">
            <h3 class="font-mouse-heading text-mouse-gold-light text-lg font-bold">Recent Activity</h3>
        </div>

        @if (count($activity) > 0)
            <div class="py-2">
                @foreach ($activity as $item)
                    @php
                        $accentClasses = match($item['color']) {
                            '#d4a843' => [
                                'surface' => 'bg-mouse-gold/10',
                                'icon' => 'text-mouse-gold',
                            ],
                            default => [
                                'surface' => 'bg-mouse-purple/10',
                                'icon' => 'text-mouse-purple-light',
                            ],
                        };
                    @endphp
                    <a href="{{ $item['url'] }}" class="hover:bg-mouse-gold/6 flex items-center gap-4 px-6 py-3.5 no-underline transition-colors">
                        <div class="{{ $accentClasses['surface'] }} flex size-9 shrink-0 items-center justify-center rounded-[0.625rem]">
                            <x-filament::icon icon="heroicon-o-{{ $item['icon'] }}" class="{{ $accentClasses['icon'] }} size-4.5" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-mouse-body text-mouse-cream truncate text-sm font-medium">{{ $item['label'] }}</p>
                            <p class="font-mouse-body text-mouse-cream/50 text-xs">{{ $item['type'] }}</p>
                        </div>
                        <time class="font-mouse-body text-mouse-cream/50 shrink-0 text-xs" datetime="{{ \Carbon\Carbon::parse($item['time'])->toIso8601String() }}">
                            {{ \Carbon\Carbon::parse($item['time'])->diffForHumans(short: true) }}
                        </time>
                    </a>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <x-filament::icon icon="heroicon-o-sparkles" class="text-mouse-gold/30 mx-auto mb-3 size-10" />
                <p class="font-mouse-body text-mouse-cream/50 text-sm">No activity yet. Start creating something magical!</p>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
