<x-filament-widgets::widget>
    @php $activity = $this->getActivity(); @endphp
    <x-filament.dashboard-panel heading="Recent Activity" description="Pick up where you left off.">
        @if (count($activity) > 0)
            <ul class="divide-mouse-navy/10 divide-y">
                @foreach ($activity as $item)
                    <li>
                        <a
                            href="{{ $item['url'] }}"
                            class="hover:bg-mouse-cream -mx-2 flex min-h-12 items-start gap-3 rounded-lg px-2 py-4 transition-colors"
                        >
                            <x-filament::icon
                                :icon="$item['icon']"
                                @class([
                                    'mt-1 size-5 shrink-0',
                                    'text-mouse-purple' => $item['color'] === 'purple',
                                    'text-mouse-gold-dark' => $item['color'] === 'gold',
                                    'text-mouse-teal' => $item['color'] === 'teal',
                                ])
                                aria-hidden="true"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="text-mouse-navy text-base font-medium break-words @lg:text-sm">
                                    {{ $item['label'] }}
                                </p>
                                <p class="text-mouse-navy/75 mt-1 text-sm">{{ $item['type'] }}</p>
                            </div>
                            <time
                                class="text-mouse-navy/75 shrink-0 pt-1 text-xs tabular-nums"
                                datetime="{{ \Carbon\Carbon::parse($item['time'])->toIso8601String() }}"
                            >
                                {{ \Carbon\Carbon::parse($item['time'])->diffForHumans(short: true) }}
                            </time>
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-mouse-navy/75 text-base">
                No activity yet. Create a post, episode, or guide to get started.
            </p>
        @endif
    </x-filament.dashboard-panel>
</x-filament-widgets::widget>
