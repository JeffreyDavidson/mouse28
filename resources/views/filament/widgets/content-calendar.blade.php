<x-filament-widgets::widget>
    @php $items = $this->getTimeline(); @endphp
    <x-filament.dashboard-panel heading="Upcoming Content" description="Your publishing schedule for the next 7 days.">
        @if (count($items) === 0)
            <div>
                <p class="text-mouse-navy text-base font-medium">Your week is wide open!</p>
                <p class="text-mouse-navy/75 mt-2 text-base @lg:text-sm">
                    Choose a publish date in the editor when your next story is ready.
                </p>
            </div>
        @else
            <ul class="divide-mouse-navy/10 divide-y">
                @foreach ($items as $item)
                    <li>
                        <a
                            href="{{ $item['url'] }}"
                            class="hover:bg-mouse-cream -mx-2 block min-h-12 rounded-lg px-2 py-4 transition-colors"
                        >
                            <p class="text-mouse-navy text-base font-medium break-words @lg:text-sm">
                                {{ $item['title'] }}
                            </p>
                            <div class="mt-2 flex flex-wrap items-center gap-3">
                                <time
                                    class="text-mouse-navy/75 text-sm"
                                    datetime="{{ $item['date']->toIso8601String() }}"
                                >
                                    {{ \App\Support\DisplayTimezone::convert($item['date'])->format('M j, g:ia') }}
                                </time>
                                <x-filament::badge :color="$item['status']->getColor()">
                                    {{ $item['status']->getLabel() }}
                                </x-filament::badge>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament.dashboard-panel>
</x-filament-widgets::widget>
